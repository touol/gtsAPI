<?php
if (!function_exists('_normHostData')) {
    function _normHostData($host)
    {
        $host = strtolower(trim((string)$host));
        $host = preg_replace('~^https?://~', '', $host);
        $host = explode('/', $host)[0];
        $host = explode(':', $host)[0];
        $host = preg_replace('~^www\.~', '', $host);

        return rtrim($host, '.');
    }
}
if (!function_exists('_pickSiteData')) {
    /**
     * Выбор блока data.json под текущий сайт.
     *
     * Поддерживаются два формата:
     *   1) { "<пакет>": { "<Таблица>": {rows: …} } }                  — как раньше, для всех сайтов
     *   2) { "<сайты>": { "<пакет>": { "<Таблица>": {rows: …} } } }   — данные только для своих сайтов
     *
     * Второй формат нужен, когда пакет выкладывается публично, а строки справочников
     * (доп. поля, справочные записи) осмысленны только на наших инсталляциях.
     *
     * Сайт в ключе (через запятую) опознаётся так же, как в resources.json:
     *   - метка сайта   — настройка `gtsapi_site_key` ('modx28', 'vk24'…). РЕКОМЕНДУЕТСЯ:
     *                     не раскрывает адреса в публичном пакете;
     *   - хост          — 'site.ru' (регистр, порт и www. не важны);
     *   - хеш хоста     — 'sha1:9f2c…'.
     * Ключ '*' (или 'default') — блок для всех остальных сайтов. Нет подходящего блока
     * и нет '*' — данные просто не ставятся, это штатный случай для чужого сайта.
     *
     * Формат определяется по структуре, а не по имени ключа: у пакета внутри лежат
     * таблицы с 'rows'/'type', у сайта — ещё один уровень вложенности.
     */
    function _pickSiteData($modx, array $data, $namespace)
    {
        if (empty($data)) {
            return [];
        }

        $first = reset($data);
        if (is_array($first)) {
            $inner = reset($first);
            // {пакет: {Таблица: {rows: …}}} — старый формат
            if (is_array($inner) && (isset($inner['rows']) || isset($inner['type']) || isset($inner['key']))) {
                return $data;
            }
        }

        $host = _normHostData($modx->getOption('http_host', null, defined('MODX_HTTP_HOST') ? MODX_HTTP_HOST : ''));
        $hostHash = $host !== '' ? sha1($host) : '';
        $siteKey = strtolower(trim((string)$modx->getOption('gtsapi_site_key', null, '')));
        $fallback = null;

        foreach ($data as $key => $block) {
            foreach (array_map('trim', explode(',', (string)$key)) as $token) {
                if ($token === '') {
                    continue;
                }
                if ($token === '*' || strtolower($token) === 'default') {
                    $fallback = $block;
                    continue;
                }
                if ($siteKey !== '' && strtolower($token) === $siteKey) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] данные: сайт '{$siteKey}' (метка)");

                    return $block;
                }
                if (stripos($token, 'sha1:') === 0) {
                    if ($hostHash !== '' && strtolower(substr($token, 5)) === substr($hostHash, 0, strlen($token) - 5)) {
                        $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] данные: сайт опознан по хешу хоста");

                        return $block;
                    }
                    continue;
                }
                if ($host !== '' && _normHostData($token) === $host) {
                    $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] данные: конфигурация сайта '{$token}'");

                    return $block;
                }
            }
        }

        if ($fallback !== null) {
            $modx->log(modX::LOG_LEVEL_INFO, "[{$namespace}] данные: конфигурация по умолчанию (сайт '{$host}')");

            return $fallback;
        }

        $modx->log(modX::LOG_LEVEL_INFO,
            "[DPLOG] [{$namespace}] данные: для этого сайта блока нет "
            . "(метка gtsapi_site_key='{$siteKey}') — справочные строки не ставятся, это нормально");

        return [];
    }
}

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    // $modx->log(1,"xPDOTransport {$options['namespace']}");
    if(!function_exists('add_data_package')){
        function add_data_package($modx,$package,$v){
            $modx->addPackage($package, MODX_CORE_PATH . 'components/'.$package.'/model/');
            $run_add_fields = false;
            foreach($v as $table=>$v2){
                // $modx->log(1,"add_data_package {$table}");
                if(in_array($table,['gtsAPIFieldTable','gtsAPIFieldGroupTableLink','gtsAPIField','gtsAPIFieldGroupLink','gtsAPIFieldGroup'])) $run_add_fields = true;

                if(isset($v2['type'])){
                    if($v2['type'] == 'link'){
                        foreach($v2['rows'] as $row){
                            $search = [];
                            $set = [];
                            foreach($row as $field=>$desc){
                                if(is_array($desc)){
                                    if(!$obj = $modx->getObject($desc['table'],[$desc['key']=>$desc[$desc['key']]])) continue 2;
                                    $search[$field] = $obj->id;
                                    $set[$field] = $obj->id;
                                }else{
                                    $set[$field] = $desc;
                                }
                            }
                            if(!$obj = $modx->getObject($table,$search)){
                                $obj = $modx->newObject($table);
                            }
                            if($obj){
                                $obj->fromArray(array_merge([], $set), '', true, true);
                                if(!$obj->save()){
                                    $modx->log(1,"Error saving {$table} ".print_r($search,1));
                                }
                            }
                            
                        }
                    }
                }else{
                    foreach($v2['rows'] as $row){
                        // $modx->log(1,"add_data_package {$table} ".print_r($row,1));
                        if(!$obj = $modx->getObject($table,[$v2['key']=>$row[$v2['key']]])){
                            $obj = $modx->newObject($table);
                        }
                        if($obj){
                            foreach($row as $k=>$v){
                                if(is_array($v)) $row[$k] = json_encode($v);
                            }
                            $obj->fromArray(array_merge([], $row), '', true, true);
                            if(!$obj->save()){
                                $modx->log(1,"Error saving {$table} {$v2['key']} {$row[$v2['key']]}");
                            }
                        }else{
                            $modx->log(1,"Error getting {$table} {$v2['key']} {$row[$v2['key']]}");
                        }
                    }
                }
            }
            return $run_add_fields;
        }
    }
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            // $modx->addPackage($options['namespace'], MODX_CORE_PATH . 'components/'.$options['namespace'].'/model/');
            $file = MODX_CORE_PATH . 'components/'.$options['namespace'].'/data.json';
            // Пакет может не везти справочных данных вовсе — это норма, а не повод
            // сыпать PHP warning в лог установки
            if (!is_file($file)) {
                break;
            }

            $data = json_decode(file_get_contents($file),1);
            if(is_array($data)){
                // Данные могут быть разложены по сайтам — берём блок своего сайта
                $data = _pickSiteData($modx, $data, $options['namespace']);
            }
            if(is_array($data) and !empty($data)){
                if(isset($data['gtsapi'])){
                    if(add_data_package($modx,'gtsapi',$data['gtsapi'])){
                        // Динамические поля gtsAPI применяем прямо при установке, иначе
                        // строки в справочнике есть, а колонок в базе нет — и их приходится
                        // добивать вручную (AddFields в gtsAPI).
                        //
                        // ⚠️ updateFields() не только добавляет: колонку, которой нет ни в
                        // схеме компонента, ни в справочнике полей, он ДРОПАЕТ. И проходит он
                        // по ВСЕМ таблицам с add_base=1 — не только по устанавливаемому пакету.
                        // Поэтому запуск только тогда, когда файлы схем уже на месте.
                        $loaded = include_once(MODX_CORE_PATH . 'components/gtsapi/classes/addfields.class.php');
                        if ($loaded) {
                            $addFields = new AddFields($modx,[]);
                            // false — установка только добавляет и правит колонки.
                            // Снос оставшихся колонок делается вручную: кнопка
                            // «Снести колонки удалённых полей» в админке gtsAPI.
                            $resp = $addFields->updateFields(false);
                            // Отчёт с меткой [DPLOG] возвращается вызывающей стороне в install_log
                            // (gtsDeploy). Иначе на чужом сайте не видно, что именно сделали с колонками.
                            $d = isset($resp['data']) ? $resp['data'] : [];
                            $modx->log(modX::LOG_LEVEL_INFO, '[DPLOG] gtsAPI доп.поля:'
                                . ' добавлено ' . count($d['added']) . (empty($d['added']) ? '' : ' (' . implode(', ', $d['added']) . ')')
                                . ', изменено ' . count($d['altered']) . (empty($d['altered']) ? '' : ' (' . implode(', ', $d['altered']) . ')')
                                . ', без изменений ' . (int)$d['skipped']
                                . ', владеет схема ' . count($d['in_schema'])
                                . ', под снос ' . count($d['pending_remove']) . (empty($d['pending_remove']) ? '' : ' (' . implode(', ', $d['pending_remove']) . ')'));
                        }
                    }
                    unset($data['gtsapi']);
                }
                foreach ($data as $package => $v) {
                    $modx->log(1,"add_data_package {$package}");
                    add_data_package($modx,$package,$v);
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}
 

return true;