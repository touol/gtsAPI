<?php

/**
 * Вставка из Excel в любую таблицу gtsAPI.
 *
 * Раньше вставка жила только в расчёте (gtsshop/rows_paste): таблица слала
 * матрицу, а что с ней делать, решал сам расчёт. Здесь то же самое, но общее:
 * новые строки создаются обычным create, строки под выделением правятся
 * обычным update. Так на вставку распространяются те же права, триггеры и
 * значения по умолчанию из фильтров, что и на ручной ввод.
 *
 * Таблица со своей логикой вставки (расчёт) объявляет её в конфиге:
 *   paste: { action: 'gtsshop/rows_paste' }
 * и общий путь для неё закрыт. paste: false — вставка выключена совсем.
 *
 * БЕЗОПАСНОСТЬ. Всё, что ниже отказывает, отказывает на сервере: фронт может
 * прислать что угодно. Писать можно только в поля основной таблицы из
 * properties.fields, открытые на запись для этого пользователя; править —
 * только строки, которые он видит через read с теми же фильтрами.
 */
trait TablePasteTrait
{
    /** Больше за раз не берём: каждая строка — полноценный create/update с триггерами. */
    protected $pasteMaxRows = 500;

    /** Типы колонок, в которые вставка умеет писать. Всё прочее — отказ. */
    protected $pasteTypes = [
        'text', 'textarea', 'string', 'email',
        'number', 'decimal', 'int', 'integer', 'float',
        'boolean', 'checkbox',
        'date', 'datetime',
        'select', 'autocomplete',
    ];

    public function paste($rule, $request, $method)
    {
        // Запись только POST-ом: GET-ссылка не должна менять данные.
        if ($method !== 'POST') return $this->error('Вставка выполняется только POST-запросом');
        // Только обычные таблицы. JSON-таблицы (type 2) и деревья (type 3)
        // наследуют route_post, но строки у них — не объекты класса, и проверка
        // «строка своя» ниже для них не годится.
        if ((int)$rule['type'] !== 1) return $this->error('Вставка из Excel для этого вида таблиц не поддерживается');

        $cfg = isset($rule['properties']['paste']) ? $rule['properties']['paste'] : null;
        if ($cfg === false) return $this->error('Вставка из Excel для этой таблицы выключена');
        if (is_array($cfg) and !empty($cfg['action'])) {
            // Своя логика вставки — общий путь её бы обошёл (нумерация, пересчёт).
            return $this->error('У этой таблицы своя вставка: ' . $cfg['action']);
        }

        $rows = [];
        if (!empty($request['rows']) and is_array($request['rows'])) {
            $rows = array_values($request['rows']);
        } else if (!empty($request['text']) and is_string($request['text'])) {
            $rows = $this->pasteParseTsv($request['text']);
        }
        if (!empty($request['skip_first'])) array_shift($rows);
        if (empty($rows)) return $this->error('Нечего вставлять');
        if (count($rows) > $this->pasteMaxRows) {
            return $this->error('За раз можно вставить не больше ' . $this->pasteMaxRows . ' строк');
        }

        $fields = (isset($request['fields']) and is_array($request['fields'])) ? array_values($request['fields']) : [];
        if (empty($fields)) return $this->error('Не выбраны колонки для вставки');

        $filters = (isset($request['filters']) and is_array($request['filters'])) ? $request['filters'] : [];
        $decimalComma = !isset($request['decimal_comma']) || !empty($request['decimal_comma']);

        $canCreate = $this->pasteActionAllowed($rule, 'create');
        $canUpdate = $this->pasteActionAllowed($rule, 'update');
        if (!$canCreate and !$canUpdate) return $this->error('Нет прав на изменение этой таблицы');

        // Колонки проверяем один раз на всю вставку. Отказ по колонке — это
        // отказ всем её ячейкам, причина одна.
        $fixed = $this->addDefaultFields($rule, ['filters' => $filters]);
        if (!is_array($fixed)) $fixed = [];
        $selects = $this->getSelects($rule['properties']['fields']);
        $columns = [];
        $warnings = [];
        foreach ($fields as $i => $field) {
            if ($field === null or $field === '') continue;
            $field = (string)$field;
            $reason = $this->pasteColumnReason($rule, $field, $fixed);
            if ($reason !== '') {
                $warnings[] = $this->pasteLabel($rule, $field) . ': ' . $reason . ' — колонка пропущена';
                continue;
            }
            $columns[$i] = $field;
        }
        if (empty($columns)) {
            return $this->error("Вставлять некуда.\n" . implode("\n", $warnings));
        }

        // Строки под выделением. Правим только те, что пользователь видит в
        // этой таблице с этими фильтрами, — иначе по чужому id можно было бы
        // переписать любую запись класса.
        $targetIds = [];
        if (!empty($request['row_ids']) and is_array($request['row_ids'])) {
            $targetIds = array_values(array_filter(array_map('intval', $request['row_ids'])));
        }
        $visible = [];
        if ($targetIds) {
            if (!$canUpdate) return $this->error('Нет права изменять строки этой таблицы');
            $resp = $this->read($rule, [
                'ids' => implode(',', $targetIds),
                'filters' => $filters,
                'limit' => 0,
            ], null, [], 'paste');
            if (!$resp['success']) return $resp;
            if (!empty($resp['data']['rows']) and is_array($resp['data']['rows'])) {
                foreach ($resp['data']['rows'] as $r) {
                    if (isset($r['id'])) $visible[(int)$r['id']] = true;
                }
            }
        }

        $nonEmpty = 0;
        foreach ($rows as $cells) {
            if ($this->pasteRowHasValue($cells)) $nonEmpty++;
        }
        if ($nonEmpty > count($targetIds) and !$canCreate) {
            return $this->error('Нет права добавлять строки. Выделите столько строк, сколько вставляете.');
        }

        $ids = [];
        $created = [];   // с данными — чтобы «Повторить» в журнале таблицы завело строки заново
        $updated = [];
        $rejected = [];
        $acCache = [];
        $lineNo = !empty($request['skip_first']) ? 2 : 1;
        $rowNo = 0;

        // create/update не перечитывают каждую строку: таблица перечитается
        // целиком после вставки (у gsMaterial чтение = сетевой запрос к складу,
        // 5 строк шли 24 с). Сбрасываем флаг в finally.
        $this->skipReadAfterSave = true;
        try {
        foreach ($rows as $cells) {
            if (!$this->pasteRowHasValue($cells)) { $lineNo++; continue; }

            $targetId = isset($targetIds[$rowNo]) ? $targetIds[$rowNo] : 0;
            $rowNo++;
            if ($targetId and empty($visible[$targetId])) {
                $warnings[] = 'строка ' . $lineNo . ': строка таблицы не найдена или недоступна, пропущена';
                $lineNo++;
                continue;
            }

            $values = [];
            foreach ($columns as $i => $field) {
                if (!array_key_exists($i, $cells)) continue;
                $raw = trim((string)$cells[$i]);
                if ($raw === '') continue;
                $cell = $this->pasteCell($rule, $field, $raw, $decimalComma, $selects, $acCache);
                if (empty($cell['ok'])) {
                    $key = $field . '|' . $cell['reason'];
                    if (!isset($rejected[$key])) {
                        $rejected[$key] = ['label' => $this->pasteLabel($rule, $field), 'reason' => $cell['reason'], 'count' => 0, 'sample' => $raw];
                    }
                    $rejected[$key]['count']++;
                    continue;
                }
                $values[$field] = $cell['value'];
            }
            if (empty($values)) { $lineNo++; continue; }

            if ($targetId) {
                $obj = $this->modx->getObject($rule['class'], $targetId);
                if (!$obj) { $warnings[] = 'строка ' . $lineNo . ': строка таблицы не найдена, пропущена'; $lineNo++; continue; }
                $old = [];
                foreach (array_keys($values) as $f) $old[$f] = $obj->get($f);
                $resp = $this->update($rule, array_merge($values, [
                    'api_action' => 'update',
                    'id' => $targetId,
                    'filters' => $filters,
                ]), null);
                if ($resp['success']) {
                    $updated[] = ['id' => $targetId, 'old' => $old, 'new' => $values];
                } else {
                    $warnings[] = 'строка ' . $lineNo . ': ' . $resp['message'];
                }
            } else {
                $resp = $this->create($rule, array_merge($values, [
                    'api_action' => 'create',
                    'filters' => $filters,
                ]), null);
                if ($resp['success'] and !empty($resp['data']['object']['id'])) {
                    $newId = (int)$resp['data']['object']['id'];
                    $ids[] = $newId;
                    $created[] = ['id' => $newId, 'data' => $values];
                } else if ($resp['success']) {
                    $warnings[] = 'строка ' . $lineNo . ': создана, но id не получен';
                } else {
                    $warnings[] = 'строка ' . $lineNo . ': ' . $resp['message'];
                }
            }
            $lineNo++;
        }
        } finally {
            $this->skipReadAfterSave = false;
        }

        foreach ($rejected as $r) {
            $warnings[] = $r['label'] . ': «' . $r['sample'] . '» — ' . $r['reason'] . '. Пропущено: ' . $r['count'];
        }

        $message = 'Вставлено строк: ' . count($ids);
        if ($updated) $message .= ', обновлено: ' . count($updated);

        return $this->success($message, [
            'ids'      => $ids,
            'created'  => $created,
            'updated'  => $updated,
            'warnings' => $warnings,
        ]);
    }

    /**
     * Откат/повтор вставки одним запросом (журнал отмены таблицы).
     *
     * Раньше журнал откатывал пачку построчно: запрос на каждую строку, и каждый
     * update перечитывал свою строку. Здесь всё одним проходом.
     *
     *   update: [{id, values: {поле: значение}}] — вернуть/выставить значения
     *   create: [{поле: значение}]               — завести строки (повтор)
     *   delete: [id, …]                          — снести созданные (откат)
     *
     * Проверки как у paste: POST, обычная таблица, права create/update/delete,
     * строки видимы с фильтрами, поля — только те, в которые может писать вставка.
     * Значения не разбираются (это уже значения базы), запись — через create/update.
     */
    public function paste_bulk($rule, $request, $method)
    {
        if ($method !== 'POST') return $this->error('Только POST-запросом');
        if ((int)$rule['type'] !== 1) return $this->error('Для этого вида таблиц не поддерживается');
        // Таблица со своей вставкой (paste.action, расчёт) тоже откатывается
        // здесь: откат — это обычные update/create/delete с триггерами таблицы.
        $cfg = isset($rule['properties']['paste']) ? $rule['properties']['paste'] : null;
        if ($cfg === false) return $this->error('Вставка для этой таблицы выключена');

        $filters = (isset($request['filters']) and is_array($request['filters'])) ? $request['filters'] : [];
        $upd =(isset($request['update']) and is_array($request['update'])) ? array_values($request['update']) : [];
        $crt = (isset($request['create']) and is_array($request['create'])) ? array_values($request['create']) : [];
        $del = (isset($request['delete']) and is_array($request['delete'])) ? array_values(array_filter(array_map('intval', $request['delete']))) : [];
        if (count($upd) + count($crt) + count($del) > $this->pasteMaxRows * 2) return $this->error('Слишком много строк');

        if ($upd and !$this->pasteActionAllowed($rule, 'update')) return $this->error('Нет права изменять строки');
        if ($crt and !$this->pasteActionAllowed($rule, 'create')) return $this->error('Нет права добавлять строки');
        if ($del and !$this->pasteActionAllowed($rule, 'delete')) return $this->error('Нет права удалять строки');

        // Поля — по тем же правилам, что у вставки.
        $fixed = $this->addDefaultFields($rule, ['filters' => $filters]);
        if (!is_array($fixed)) $fixed = [];
        // Поля, которые отбросили (не для записи), — в сводку: иначе откат
        // вышел бы неполным молча. id и заданные фильтром — не в счёт, их и так
        // присылает повтор (строка целиком).
        $skipped = [];
        $clean = function ($values) use ($rule, $fixed, &$skipped) {
            $out = [];
            if (!is_array($values)) return $out;
            foreach ($values as $f => $v) {
                if (!is_string($f) or is_array($v) or is_object($v)) continue;
                if ($this->pasteColumnReason($rule, $f, $fixed) !== '') {
                    if ($f !== 'id' and !array_key_exists($f, $fixed) and isset($rule['properties']['fields'][$f])) $skipped[$f] = true;
                    continue;
                }
                $out[$f] = $v;
            }
            return $out;
        };

        // Видимость строк, которые правим и удаляем.
        $ids = $del;
        foreach ($upd as $u) if (!empty($u['id'])) $ids[] = (int)$u['id'];
        $visible = [];
        if ($ids) {
            $resp = $this->read($rule, ['ids' => implode(',', array_unique($ids)), 'filters' => $filters, 'limit' => 0], null, [], 'paste');
            if (!$resp['success']) return $resp;
            foreach (($resp['data']['rows'] ?? []) as $r) if (isset($r['id'])) $visible[(int)$r['id']] = true;
        }

        $warnings = [];
        $createdIds = [];
        $this->skipReadAfterSave = true;
        try {
            foreach ($upd as $u) {
                $id = isset($u['id']) ? (int)$u['id'] : 0;
                if (!$id or empty($visible[$id])) { $warnings[] = "строка $id недоступна, пропущена"; continue; }
                $values = $clean(isset($u['values']) ? $u['values'] : []);
                if (!$values) continue;
                $resp = $this->update($rule, array_merge($values, ['api_action' => 'update', 'id' => $id, 'filters' => $filters]), null);
                if (!$resp['success']) $warnings[] = "строка $id: " . $resp['message'];
            }
            foreach ($crt as $values) {
                $values = $clean($values);
                if (!$values) continue;
                $resp = $this->create($rule, array_merge($values, ['api_action' => 'create', 'filters' => $filters]), null);
                if ($resp['success'] and !empty($resp['data']['object']['id'])) $createdIds[] = (int)$resp['data']['object']['id'];
                else if (!$resp['success']) $warnings[] = $resp['message'];
            }
        } finally {
            $this->skipReadAfterSave = false;
        }
        $delIds = array_values(array_filter($del, function ($id) use ($visible) { return !empty($visible[$id]); }));
        if (count($delIds) < count($del)) $warnings[] = 'часть строк для удаления недоступна, пропущена';
        if ($delIds) {
            $resp = $this->delete($rule, ['ids' => $delIds], null);
            if (!$resp['success']) $warnings[] = $resp['message'];
        }

        if ($skipped) $warnings[] = 'не восстановлены поля (не для записи): ' . implode(', ', array_keys($skipped));

        return $this->success('', ['created_ids' => $createdIds, 'warnings' => $warnings]);
    }

    /**
     * Разрешено ли пользователю действие таблицы (create/update) — так же,
     * как его проверяет route_post для обычного запроса.
     */
    protected function pasteActionAllowed($rule, $action)
    {
        $found = false;
        foreach (['actions', 'hide_actions'] as $key) {
            if (!isset($rule['properties'][$key][$action])) continue;
            $found = true;
            $resp = $this->checkPermissions($rule['properties'][$key][$action]);
            if (!$resp['success']) return false;
        }
        return $found;
    }

    /**
     * Почему в колонку нельзя вставлять. Пустая строка — можно.
     */
    protected function pasteColumnReason($rule, $field, $fixed)
    {
        if (empty($rule['properties']['fields'][$field]) or !is_array($rule['properties']['fields'][$field])) {
            return 'нет такой колонки';
        }
        $desc = $rule['properties']['fields'][$field];
        if ($field === 'id' or strpos($field, '.') !== false) return 'колонка не редактируется';
        if (!empty($desc['class']) and $desc['class'] !== $rule['class']) return 'колонка из другой таблицы';
        if ($this->pasteRestricted($desc, 'disabled')) return 'колонка недоступна';
        if ($this->pasteRestricted($desc, 'readonly')) return 'колонка только для чтения';
        $type = !empty($desc['type']) ? (string)$desc['type'] : 'text';
        if (!in_array($type, $this->pasteTypes, true)) return 'колонка не редактируется';
        if ($type === 'autocomplete' and !empty($desc['table_by'])) return 'справочник зависит от другой колонки, вставка не поддерживается';
        // Поле задано фильтром/родителем (напр. id документа в подтаблице):
        // вставка в него увела бы строку к чужому родителю.
        if (array_key_exists($field, $fixed)) return 'значение задано родителем';
        return '';
    }

    /**
     * readonly/disabled: true — закрыто; массив условий — закрыто, пока
     * пользователь не подходит под условие (как в options()).
     */
    protected function pasteRestricted($desc, $key)
    {
        if (!isset($desc[$key])) return false;
        $v = $desc[$key];
        if (!is_array($v)) return !empty($v);
        if (!empty($v['authenticated']) and $this->modx->user->id > 0) return false;
        if (!empty($v['groups'])) {
            if ($this->modx->user->isMember(array_map('trim', explode(',', $v['groups'])))) return false;
        }
        if (!empty($v['permitions'])) {
            foreach (array_map('trim', explode(',', $v['permitions'])) as $pm) {
                if ($this->modx->hasPermission($pm)) return false;
            }
        }
        return true;
    }

    protected function pasteLabel($rule, $field)
    {
        $d = isset($rule['properties']['fields'][$field]) ? $rule['properties']['fields'][$field] : [];
        return !empty($d['label']) ? $d['label'] : $field;
    }

    protected function pasteRowHasValue($cells)
    {
        if (!is_array($cells)) return false;
        foreach ($cells as $c) {
            if (is_array($c)) continue;
            if (trim((string)$c) !== '') return true;
        }
        return false;
    }

    /**
     * Проверить и привести одно значение к типу колонки.
     * Непригодное не пишем — отказ с причиной в сводку.
     */
    protected function pasteCell($rule, $field, $value, $decimalComma, $selects, &$acCache)
    {
        $desc = $rule['properties']['fields'][$field];
        $type = !empty($desc['type']) ? (string)$desc['type'] : 'text';

        if ($type === 'select') {
            $options = [];
            if (!empty($selects[$field]['rows'])) $options = $selects[$field]['rows'];
            else if (!empty($desc['select_data'])) $options = $desc['select_data'];
            if (empty($options) or !is_array($options)) return ['ok' => false, 'reason' => 'нет списка вариантов'];
            $lower = $this->pasteLower($value);
            foreach ($options as $o) {
                if (!is_array($o)) continue;
                $id = isset($o['id']) ? $o['id'] : (isset($o[0]) ? $o[0] : null);
                $content = isset($o['content']) ? $o['content'] : (isset($o[1]) ? $o[1] : $id);
                if ($id === null) continue;
                if ($this->pasteLower(strip_tags((string)$content)) === $lower) return ['ok' => true, 'value' => $id];
            }
            return ['ok' => false, 'reason' => 'нет в списке'];
        }

        if ($type === 'autocomplete') {
            $key = $field . '|' . $this->pasteLower($value);
            if (!isset($acCache[$key])) $acCache[$key] = $this->pasteFindAutocomplete($desc, $value);
            return $acCache[$key];
        }

        if ($type === 'boolean' or $type === 'checkbox') {
            $lower = $this->pasteLower($value);
            if (in_array($lower, ['1', 'да', 'true', 'yes', 'истина', '+', 'v', 'x'], true)) return ['ok' => true, 'value' => 1];
            if (in_array($lower, ['0', 'нет', 'false', 'no', 'ложь', '-'], true)) return ['ok' => true, 'value' => 0];
            return ['ok' => false, 'reason' => 'нужно «да» или «нет»'];
        }

        if (in_array($type, ['number', 'decimal', 'int', 'integer', 'float'], true)) {
            $clean = str_replace(["\xC2\xA0", ' ', "'"], '', $value);
            if ($decimalComma) $clean = str_replace(',', '.', $clean);
            if (!preg_match('/^-?\d+(\.\d+)?$/', $clean)) return ['ok' => false, 'reason' => 'не число'];
            if ($type === 'int' or $type === 'integer') {
                if ((float)$clean != floor((float)$clean)) return ['ok' => false, 'reason' => 'нужно целое число'];
                return ['ok' => true, 'value' => (int)$clean];
            }
            return ['ok' => true, 'value' => $clean];
        }

        if ($type === 'date' or $type === 'datetime') {
            $ts = $this->pasteParseDate($value);
            if (!$ts) return ['ok' => false, 'reason' => 'не дата'];
            return ['ok' => true, 'value' => date($type === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s', $ts)];
        }

        return ['ok' => true, 'value' => $value];
    }

    protected function pasteLower($s)
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string)$s)), 'UTF-8');
    }

    /**
     * Дата из Excel: чаще всего «31.12.2026» или «31.12.26»; остальное — strtotime.
     */
    protected function pasteParseDate($value)
    {
        $value = trim($value);
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2}|\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/', $value, $m)) {
            $y = (int)$m[3];
            if ($y < 100) $y += 2000;
            if (!checkdate((int)$m[2], (int)$m[1], $y)) return false;
            return mktime(isset($m[4]) ? (int)$m[4] : 0, isset($m[5]) ? (int)$m[5] : 0, isset($m[6]) ? (int)$m[6] : 0, (int)$m[2], (int)$m[1], $y);
        }
        return strtotime($value);
    }

    /**
     * Текст → id записи справочника автокомплита.
     *
     * Ищем тем же поиском, что и выпадающий список (autocomplete.where с
     * подстановкой query), — значит, находим только то, что пользователь мог бы
     * выбрать руками. Из найденного берём точное совпадение видимого названия
     * (без регистра). Ноль или несколько совпадений — отказ: угадывать нельзя,
     * «Болт М8» и «Болт М8 оцинк.» — разные позиции.
     */
    protected function pasteFindAutocomplete($desc, $text)
    {
        if (empty($desc['table'])) return ['ok' => false, 'reason' => 'у колонки не задан справочник'];
        $acTable = $this->modx->getObject('gtsAPITable', ['table' => $desc['table'], 'active' => 1]);
        if (!$acTable) return ['ok' => false, 'reason' => 'справочник не найден'];
        $perm = $this->checkPermissions($acTable->toArray());
        if (!$perm['success']) return ['ok' => false, 'reason' => 'нет доступа к справочнику'];

        $props = json_decode($acTable->get('properties'), true);
        if (!is_array($props) or empty($props['autocomplete']['where']) or !is_array($props['autocomplete']['where'])) {
            return ['ok' => false, 'reason' => 'поиск по названию в справочнике не настроен'];
        }
        $ac = $props['autocomplete'];
        $this->addPackages($acTable->get('package_id'));
        $class = $acTable->get('class') ? $acTable->get('class') : $acTable->get('table');

        $cfg = [
            'class' => $class,
            'select' => [$class => '*'],
            'sortby' => ["{$class}.id" => 'ASC'],
            'return' => 'data',
        ];
        if (isset($ac['query']) and is_array($ac['query'])) $cfg = array_merge($cfg, $ac['query']);
        $cfg['limit'] = 50;
        if (empty($cfg['where'])) $cfg['where'] = [];

        $searchFields = [];
        $hasQuery = false;
        // Условие с числовым ключом — сырой SQL (напр. «... LIKE '%query%'»):
        // текст из Excel туда только экранированным, иначе это инъекция.
        // Условие «поле => значение» xPDO передаёт параметром — там как есть.
        $quoted = substr($this->modx->quote($text), 1, -1);
        foreach ($ac['where'] as $k => $v) {
            if (is_string($v) and strpos($v, 'query') !== false) {
                $cfg['where'][$k] = str_replace('query', is_int($k) ? $quoted : $text, $v);
                $hasQuery = true;
                $col = explode('.', explode(':', $k)[0]);
                $searchFields[] = end($col);
            } else {
                $cfg['where'][$k] = $v;
            }
        }
        if (!$hasQuery) return ['ok' => false, 'reason' => 'поиск по названию в справочнике не настроен'];
        // Ограничения самой колонки (where в её конфиге) — как в get_autocomplete.
        // Шаблоны Fenom пропускаем: их разворачивает только выпадающий список.
        if (!empty($desc['where']) and is_array($desc['where'])) {
            foreach ($desc['where'] as $k => $v) {
                if (is_string($v) and strpos($v, '{') !== false) continue;
                $cfg['where'][$k] = $v;
            }
        }

        $this->pdo->setConfig($cfg);
        $found = $this->pdo->run();
        if (!is_array($found)) $found = [];

        $want = $this->pasteLower($text);
        $hits = [];
        foreach ($found as $row) {
            if (!isset($row['id'])) continue;
            $labels = [];
            if (!empty($ac['tpl'])) {
                $html = $this->pdoTools->getChunk('@INLINE ' . $ac['tpl'], $row);
                // Как content_plain на клиенте: название до пути (div), без тегов.
                $html = preg_replace('/<div[\s\S]*$/i', '', (string)$html);
                $labels[] = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
            }
            // Отдельные поля тоже: в списке видно «название код тип», а в
            // Excel обычно одно название. Два совпадения — всё равно отказ ниже.
            $plainFields = $searchFields;
            if (!empty($ac['select']) and is_array($ac['select'])) $plainFields = array_merge($plainFields, $ac['select']);
            foreach (array_unique($plainFields) as $sf) {
                if ($sf === 'id') continue;
                if (isset($row[$sf]) and !is_array($row[$sf])) $labels[] = (string)$row[$sf];
            }
            foreach ($labels as $l) {
                if ($this->pasteLower($l) === $want) { $hits[(int)$row['id']] = true; break; }
            }
        }
        if (count($hits) === 1) return ['ok' => true, 'value' => (int)key($hits)];
        if (count($hits) > 1) return ['ok' => false, 'reason' => 'в справочнике несколько таких записей'];
        return ['ok' => false, 'reason' => 'нет в справочнике'];
    }

    /**
     * Разбор TSV из буфера Excel: ячейки с переносом/табуляцией Excel
     * оборачивает в кавычки, внутри кавычек "" — одна кавычка.
     * (Та же логика, что gtsShop helper::parse_tsv.)
     */
    protected function pasteParseTsv($text)
    {
        $TAB = chr(9); $LF = chr(10); $CR = chr(13);
        $rows = [];
        $row = [];
        $cell = '';
        $inQuotes = false;
        $src = str_replace([$CR . $LF, $CR], $LF, (string)$text);
        $len = mb_strlen($src, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($src, $i, 1, 'UTF-8');
            if ($inQuotes) {
                if ($ch === '"') {
                    if (mb_substr($src, $i + 1, 1, 'UTF-8') === '"') { $cell .= '"'; $i++; }
                    else $inQuotes = false;
                } else $cell .= $ch;
                continue;
            }
            if ($ch === '"' and $cell === '') { $inQuotes = true; continue; }
            if ($ch === $TAB) { $row[] = $cell; $cell = ''; continue; }
            if ($ch === $LF)  { $row[] = $cell; $rows[] = $row; $row = []; $cell = ''; continue; }
            $cell .= $ch;
        }
        if ($cell !== '' or count($row)) { $row[] = $cell; $rows[] = $row; }
        while (count($rows) and !$this->pasteRowHasValue(end($rows))) array_pop($rows);
        return $rows;
    }
}
