
<?php

/**
 * Trait для работы с автокомплитом
 * Содержит методы обработки автокомплита для полей таблицы
 */
trait TableAutocompleteTrait
{
    /**
     * Получение данных автокомплита
     */
    public function get_autocomplete($rule, $request)
    {
        $default = [
            'class' => $rule['class'],
            'select' => [
                $rule['class'] => '*',
            ],
            'sortby' => [
                "{$rule['class']}.id" => 'ASC',
            ],
            'return' => 'data',
            'limit' => 0
        ];
        
        $autocomplete = $rule['properties']['autocomplete'];
        if (isset($autocomplete['query']) and is_array($autocomplete['query']))
            $default = array_merge($default, $autocomplete['query']);

        if (isset($autocomplete['select'])) {
            $selects_fields = [];
            foreach ($autocomplete['select'] as $field) {
                $selects_fields[] = $rule['class'] . '.' . $field;
            }
            $default['select'][$rule['class']] = implode(',', $selects_fields);
        }
        

        if (isset($request['query']) or !empty($request['parent']) or !empty($request['search'])) {
            if (empty($default['where'])) $default['where'] = [];
            $where = [];
            
            // Обработка стандартных where условий
            if (isset($autocomplete['where'])) {
                foreach ($autocomplete['where'] as $field => $value) {
                    if (strpos($value, 'query') !== false) {
                        if (!empty($request['query'])) {
                            $value = str_replace('query', $request['query'], $value);
                            $where[$field] = $value;
                        }
                    } else {
                        $where[$field] = $value;
                    }
                    if (!empty($request['parent'])) {
                        foreach ($request['parent'] as $pfield => $pval) {
                            if ($value == $pfield) {
                                $where[$field] = $pval;
                            }
                        }
                    }
                }
            }
            
            // Обработка множественных полей поиска для multiautocomplete
            if (!empty($request['search'])) {
                foreach ($request['search'] as $searchField => $searchConfig) {
                    if (isset($searchConfig['value']) && !empty($searchConfig['value'])) {
                        $where[$searchField] = $searchConfig['value'];
                    }
                }
            }
            
            $default['where'] = array_merge($default['where'], $where);
        }
        
        // Обработка where условий из поля автокомплита (только из конфигурации, безопасно)
        if (isset($autocomplete['field']) &&
            isset($rule['properties']['fields'][$autocomplete['field']]['where']) &&
            is_array($rule['properties']['fields'][$autocomplete['field']]['where'])) {
            
            $fieldWhere = $rule['properties']['fields'][$autocomplete['field']]['where'];
            
            // Обработка Fenom-шаблонов в значениях where (только модификатор date для безопасности)
            foreach ($fieldWhere as $key => $value) {
                if (is_string($value) && preg_match('/^\{[^}]*\|\s*date\s*:\s*["\'][^"\']*["\']\s*\}$/', $value)) {
                    // Используем pdoTools для обработки Fenom-шаблона только с модификатором date
                    $fieldWhere[$key] = $this->pdoTools->getChunk("@INLINE " . $value, []);
                }
            }
            
            if (empty($default['where'])) $default['where'] = [];
            $default['where'] = array_merge($default['where'], $fieldWhere);
        }

        // Обработка where из запроса (только модификатор date для безопасности)
        if (!empty($request['where']) && is_array($request['where'])) {
            $requestWhere = $request['where'];
            
            // Обработка Fenom-шаблонов в значениях where (только модификатор date)
            foreach ($requestWhere as $key => $value) {
                if (is_string($value) && preg_match('/^\{[^}]*\|\s*date\s*:\s*["\'][^"\']*["\']\s*\}$/', $value)) {
                    // Используем pdoTools для обработки Fenom-шаблона только с модификатором date
                    $requestWhere[$key] = $this->pdoTools->getChunk("@INLINE " . $value, []);
                }
            }
            
            if (empty($default['where'])) $default['where'] = [];
            $default['where'] = array_merge($default['where'], $requestWhere);
        }
        if (isset($request['ids']) and is_array($request['ids'])) {
            if (empty($default['where'])) $default['where'] = [];
            $default['where']["{$rule['class']}.id:IN"] = $request['ids'];
        }
        $default['decodeJSON'] = 1;
        if (!empty($request['id'])) {
            $default['where']["{$rule['class']}.id"] = $request['id'];
        }
        if (!empty($request['show_id']) and isset($autocomplete['show_id_where'])) {
            $default['where'][1001] = "({$rule['class']}.id = {$request['show_id']} or {$autocomplete['show_id_where']} = {$request['show_id']})";
        }
        if (isset($autocomplete['limit'])) {
            $default['limit'] = $autocomplete['limit'];
        }
        if (isset($request['offset'])) {
            $default['offset'] = $request['offset'];
        } else {
            $request['offset'] = 0;
        }
        
        // Добавляем поддержку limit из запроса для виртуального скроллинга
        if (isset($request['limit'])) {
            $default['limit'] = $request['limit'];
        }
        
        $default['setTotal'] = true;
        
        if ($request['sortField']) {
            $default['sortby'] = [
                "{$request['sortField']}" => $request['sortOrder'] == 1 ? 'ASC' : 'DESC',
            ];
        }
        if ($request['multiSortMeta']) {
            $default['sortby'] = [];
            foreach ($request['multiSortMeta'] as $sort) {
                $default['sortby']["{$sort['field']}"] = $sort['order'] == 1 ? 'ASC' : 'DESC';
            }
        }
        $this->pdo->setConfig($default);
        $rows0 = $this->pdo->run();
        if (!empty($autocomplete['tpl'])) {
            foreach ($rows0 as $k => $row) {
                $rows0[$k]['content'] = $this->pdoTools->getChunk("@INLINE " . $autocomplete['tpl'], $row);
            }
        }
        
        $total = (int)$this->modx->getPlaceholder('total');
        
        $default = '';
        if (isset($autocomplete['default_row']) and is_array($autocomplete['default_row'])) {
            if ($obj = $this->modx->getObject($rule['class'], $autocomplete['default_row'])) {
                $default = $obj->id;
            }
        }
        $out = [
            'rows' => $rows0,
            'total' => $total,
            'default' => $default,
            'log' => $this->pdo->getTime()
        ];
        
        // Добавляем шаблон из конфигурации autocomplete для динамического отображения
        if (!empty($autocomplete['template'])) {
            $out['template'] = $autocomplete['template'];
        }
        
        if ($rule['properties']['showLog']) $out['log'] = $this->pdo->getTime();

        return $this->success('', $out);
    }

    /**
     * Обогащает rows_delta опциями автокомплитов под значения upsert-строк.
     *
     * rows_delta (точечное обновление таблицы) несёт новые/изменённые строки, в т.ч. со
     * СВЕЖИМ значением autocomplete-поля (напр. product_id → только что выбранный продукт).
     * Клиентский справочник опций (autocompleteSettings) этих id ещё не содержит — он грузился
     * при чтении под товары, которые тогда были в таблице. Без опции ячейка автокомплита не
     * покажет подпись (имя продукта / путь каталога) до полной перезагрузки таблицы.
     *
     * autocomplete() фильтрует опции РОВНО по значениям, присутствующим в переданных строках
     * (where id:IN), и рендерит content по tpl — то же, что делает read. Кладём результат в
     * rows_delta.autocomplete; клиент мёрджит его в свой справочник (не заменяя полный список).
     *
     * @param array $data  ссылка на data ответа (может содержать rows_delta.upsert)
     * @param array $rule  правило таблицы, чьи строки в upsert (его fields → autocomplete-поля)
     */
    public function enrichRowsDeltaAutocomplete(&$data, $rule)
    {
        if (empty($rule['properties']['fields'])) return;
        // offset=0 — не пропускать limit:0-автокомплиты (напр. продукция); autocomplete() всё равно
        // ограничит выборку id из строк, весь каталог рендериться не будет.

        // 1) Строки rows_delta (пересчитанное поддерево — напр. update строки или каскад по родителю).
        if (!empty($data['rows_delta']['upsert'])) {
            $ac = $this->autocompletes($rule['properties']['fields'], $data['rows_delta']['upsert'], 0);
            if (!empty($ac)) $data['rows_delta']['autocomplete'] = $ac;
        }

        // 2) Только что созданная/обновлённая строка (data.object). Для НОВОЙ верхнеуровневой детали
        // rows_delta пуст (родителя для пересчёта нет), но клиент добавляет строку из data.object —
        // без подписей product_id/материала ячейки пустуют до F5. Кладём словари в data.autocomplete
        // (клиент мёрджит так же, как при read). autocomplete() ограничит выборку значениями строки.
        if (!empty($data['object']) && is_array($data['object'])) {
            $acObj = $this->autocompletes($rule['properties']['fields'], [$data['object']], 0);
            if (!empty($acObj)) $data['autocomplete'] = $acObj;
        }
    }

    /**
     * Обработка множественных автокомплитов
     */
    public function autocompletes($fields, $rows0, $offset)
    {
        if (empty($fields)) return [];
        $autocompletes = [];
        foreach ($fields as $field => $desc) {
            if (isset($desc['type'])) {
                if ($desc['type'] == 'autocomplete' and isset($desc['table'])) {

                    // Поле с table_by ссылается на РАЗНЫЕ таблицы в разных строках
                    // (напр. «Материал»: материалы производства или ТМЦ — смотря какой
                    // тип закупа). Одним списком опций тут не обойтись: id в разных
                    // справочниках совпадают, и подписи перепутались бы.
                    // Поэтому бьём строки по таблицам и грузим опции для каждой.
                    $byTable = $this->splitRowsByTable($desc, $field, $rows0);

                    foreach ($byTable as $tableName => $tableRows) {
                        $res = $this->autocompleteForTable($tableName, $field, $tableRows, $offset);
                        if ($res === null) continue;

                        if ($tableName === $desc['table']) {
                            // Основная таблица поля — там же, где и раньше: rows
                            $autocompletes[$field] = isset($autocompletes[$field])
                                ? array_merge($res, $autocompletes[$field])
                                : $res;
                        } else {
                            // Переключённые таблицы — отдельными наборами
                            if (!isset($autocompletes[$field])) $autocompletes[$field] = ['rows' => []];
                            $autocompletes[$field]['by_table'][$tableName] = $res;
                        }
                    }
                } else if ($desc['type'] == 'multiautocomplete' and isset($desc['table']) and isset($desc['search'])) {
                    // Обработка multiautocomplete
                    if ($gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $desc['table'], 'active' => 1])) {
                        $properties = json_decode($gtsAPITable->properties, 1);
                        if (is_array($properties) and isset($properties['autocomplete'])) {
                            $this->addPackages($gtsAPITable->package_id);
                            $autocomplete = $properties['autocomplete'];
                            if (isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) continue;
                            $autocomplete['field'] = $field;
                            $autocomplete['table'] = $desc['table'];
                            $autocomplete['class'] = $gtsAPITable->class ? $gtsAPITable->class : $desc['table'];
                            
                            // Сначала загружаем главный автокомплит — в его строках находятся
                            // значения search-полей (search keys — это поля целевой таблицы,
                            // не родительской).
                            $autocompleteResult = $this->autocomplete($autocomplete, $rows0);

                            $searchFieldsData = [];
                            foreach ($desc['search'] as $searchFieldKey => $searchFieldConfig) {
                                if (!isset($searchFieldConfig['table'])) continue;
                                if (!($searchGtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $searchFieldConfig['table'], 'active' => 1]))) continue;
                                $searchProperties = json_decode($searchGtsAPITable->properties, 1);
                                if (!is_array($searchProperties) || !isset($searchProperties['autocomplete'])) continue;

                                $this->addPackages($searchGtsAPITable->package_id);
                                $searchAutocomplete = $searchProperties['autocomplete'];
                                $searchAutocomplete['field'] = $searchFieldKey;
                                $searchAutocomplete['table'] = $searchFieldConfig['table'];
                                $searchAutocomplete['class'] = $searchGtsAPITable->class ? $searchGtsAPITable->class : $searchFieldConfig['table'];

                                // Значения берём из уже загруженных строк целевого автокомплита —
                                // именно там лежит, напр., period_id на строке tSkladOrders.
                                $fakeRows = [];
                                foreach ($autocompleteResult['rows'] as $acRow) {
                                    if (isset($acRow[$searchFieldKey]) && !empty($acRow[$searchFieldKey])) {
                                        $fakeRows[] = [$searchFieldKey => $acRow[$searchFieldKey]];
                                    }
                                }
                                if (!empty($fakeRows)) {
                                    $searchFieldsData[$searchFieldKey] = $this->autocomplete($searchAutocomplete, $fakeRows);
                                }
                            }

                            $autocompleteResult['searchFields'] = $searchFieldsData;
                            $autocompletes[$field] = $autocompleteResult;
                        }
                    }
                }
            }
        }
        return $autocompletes;
    }

    /**
     * Обработка одного автокомплита
     */
    /**
     * Разложить строки по таблицам, на которые ссылается поле.
     *
     * Без table_by это одна группа — таблица поля. С table_by таблица берётся по
     * значению соседнего поля строки (по той же карте, что и на клиенте).
     *
     * @return array [имя таблицы => строки]
     */
    protected function splitRowsByTable($desc, $field, $rows0)
    {
        $default = $desc['table'];
        if (empty($desc['table_by']['field']) || empty($desc['table_by']['map']) || empty($rows0)) {
            return [$default => $rows0];
        }

        $by = $desc['table_by'];
        $groups = [];
        foreach ($rows0 as $row) {
            $table = $default;
            if (isset($row[$by['field']])) {
                $key = $row[$by['field']];
                if (isset($by['map'][$key])) {
                    $hit = $by['map'][$key];
                    $table = is_array($hit) ? $hit['table'] : $hit;
                }
            }
            $groups[$table][] = $row;
        }
        // Основная таблица должна быть в ответе всегда: клиент ждёт rows
        if (!isset($groups[$default])) $groups[$default] = [];
        return $groups;
    }

    /**
     * Опции одной таблицы под значения переданных строк.
     *
     * @return array|null null — если таблица не зарегистрирована или без autocomplete
     */
    protected function autocompleteForTable($tableName, $field, $rows, $offset)
    {
        $gtsAPITable = $this->modx->getObject('gtsAPITable', ['table' => $tableName, 'active' => 1]);
        if (!$gtsAPITable) return null;

        $properties = json_decode($gtsAPITable->properties, 1);
        if (!is_array($properties) || !isset($properties['autocomplete'])) return null;

        $this->addPackages($gtsAPITable->package_id);
        $autocomplete = $properties['autocomplete'];
        if (isset($autocomplete['limit']) and $autocomplete['limit'] == 0 and $offset != 0) return null;

        $autocomplete['field'] = $field;
        $autocomplete['table'] = $tableName;
        $autocomplete['class'] = $gtsAPITable->class ? $gtsAPITable->class : $tableName;
        return $this->autocomplete($autocomplete, $rows);
    }

    public function autocomplete($autocomplete, $rows0)
    {
        if (!isset($autocomplete['limit'])) $autocomplete['limit'] = 15;
        $default = [
            'class' => $autocomplete['class'],
            'select' => [
                $autocomplete['class'] => '*',
            ],
            'sortby' => [
                "{$autocomplete['class']}.id" => 'ASC',
            ],
            'return' => 'data',
            'limit' => $autocomplete['limit']
        ];
        if (isset($autocomplete['select'])) {
            $selects_fields = [];
            foreach ($autocomplete['select'] as $field) {
                $selects_fields[] = $autocomplete['class'] . '.' . $field;
            }
            $default['select'][$autocomplete['class']] = implode(',', $selects_fields);
        }
        if (isset($autocomplete['query']) and is_array($autocomplete['query']))
            $default = array_merge($default, $autocomplete['query']);
        // Подстановка лейблов в строки таблицы: грузим ТОЛЬКО значения, реально присутствующие
        // в строках (rows0) — независимо от limit. Полный список (выпадашка/опции фильтра) идёт
        // отдельно (get_autocomplete / вызов с пустым rows0). Без этого limit:0-автокомплиты
        // (напр. продукция, 441 запись) рендерили ВСЕ записи + tpl на КАЖДОЕ чтение таблицы → тормоза.
        $ids = [];
        foreach ($rows0 as $row) {
            if (isset($row[$autocomplete['field']]) && (int)$row[$autocomplete['field']] > 0) {
                $ids[$row[$autocomplete['field']]] = $row[$autocomplete['field']];
            }
        }
        if (!empty($ids)) {
            $default['where'][$autocomplete['class'] . '.id:IN'] = $ids;
            $default['limit'] = 0;
        }
        $default['setTotal'] = true;
        $this->pdo->setConfig($default);
        $autocomplete['rows'] = $this->pdo->run();
        if (!empty($autocomplete['tpl'])) {
            foreach ($autocomplete['rows'] as $k => $row) {
                $autocomplete['rows'][$k]['content'] = $this->pdoTools->getChunk("@INLINE " . $autocomplete['tpl'], $row);
            }
        }
        $default = '';
        if (isset($autocomplete['default_row']) and is_array($autocomplete['default_row'])) {
            if ($obj = $this->modx->getObject($rule['class'], $autocomplete['default_row'])) {
                $autocomplete['default_value'] = $obj->id;
            }
        }
        $autocomplete['log'] = $this->pdo->getTime();
        $autocomplete['total'] = (int)$this->modx->getPlaceholder('total');
        return $autocomplete;
    }
}