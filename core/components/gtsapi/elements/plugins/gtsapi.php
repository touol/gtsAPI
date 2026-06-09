<?php
/** @var modX $modx */
/* @var array $scriptProperties */
//echo $_SERVER['REQUEST_URI'];exit();
switch ($modx->event->name) {
    case 'OnMODXInit':
        $gtsAPI = $modx->getService('gtsapi', 'gtsAPI', 
                $modx->getOption('gtsapi_core_path', $scriptProperties, $modx->getOption('core_path') . 'components/gtsapi/') . 'model/');
        if ($gtsAPI instanceof gtsAPI) {
            $gsPluginsCorePath = MODX_CORE_PATH . 'components/gtsapi/plugins/';
            $gtsAPIFieldTables = $modx->getIterator('gtsAPIFieldTable',['add_base'=>1]);
            $plugins = [];
            if($gtsAPIFieldTables && is_iterable($gtsAPIFieldTables)){
                foreach($gtsAPIFieldTables as $gtsAPIFieldTable){
                if(!$gtsAPITable = $modx->getObject('gtsAPITable',['table'=>$gtsAPIFieldTable->name_table])) continue;
                if(!$gtsAPIPackage = $modx->getObject('gtsAPIPackage',$gtsAPITable->package_id)) continue;
                $package = $gtsAPIPackage->name;
                if(empty($gtsAPITable->class)){
                    $class = $gtsAPITable->table;
                }else{
                    $class = $gtsAPITable->class;
                }
                if(!file_exists($gsPluginsCorePath .strtolower($class).'.map.inc.php')) continue;
                $plugins[$package][]['xpdo_meta_map'][$class] = 
                    require_once $gsPluginsCorePath .strtolower($class).'.map.inc.php';
                }
            }
            if(!empty($plugins) && is_array($plugins)){
                foreach ($plugins as $package => $plugins2) {
                    if(is_array($plugins2)){
                        foreach ($plugins2 as $plugin) {
                        // For legacy plugins
                        if (isset($plugin['xpdo_meta_map']) && is_array($plugin['xpdo_meta_map'])) {
                            $plugin['map'] = $plugin['xpdo_meta_map'];
                        }
                        if (isset($plugin['map']) && is_array($plugin['map'])) {
                            foreach ($plugin['map'] as $class => $map) {
                                if (!isset($modx->map[$class])) {
                                    $modx->loadClass($class, MODX_CORE_PATH . 'components/'.strtolower($package).'/'.'model/' .strtolower($package). '/');
                                }
                                if (isset($modx->map[$class]) && is_array($map)) {
                                    foreach ($map as $key => $values) {
                                        $modx->map[$class][$key] = array_merge($modx->map[$class][$key], $values);
                                    }
                                    //$modx->log(1,"loadMap ".print_r($modx->map[$class],1));
                                }
                            }
                        }
                        }
                    }
                }
            }
        }
    break;
    case 'OnHandleRequest':
        $uri = explode('?',$_SERVER['REQUEST_URI']);
        $uri = explode('/',$uri[0]);
        if($uri[1] == 'api'){
            // Перехват ФАТАЛЬНЫХ ошибок (исчерпание памяти, max_execution_time,
            // parse) — try/catch их НЕ ловит. Пишем в лог, чтобы найти источник 500.
            register_shutdown_function(function() use ($modx, $uri) {
                $e = error_get_last();
                if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
                    $modx->log(modX::LOG_LEVEL_ERROR,
                        '[gtsAPI plugin] FATAL: ' . $e['message'] .
                        ' in ' . $e['file'] . ':' . $e['line'] .
                        ' | uri=' . implode('/', $uri) . ' | method=' . ($_SERVER['REQUEST_METHOD'] ?? '')
                    );
                }
            });

            /* @var gtsAPI $gtsAPI*/
            $gtsAPI = $modx->getService('gtsapi', 'gtsAPI', 
                $modx->getOption('gtsapi_core_path', $scriptProperties, $modx->getOption('core_path') . 'components/gtsapi/') . 'model/');
            if ($gtsAPI instanceof gtsAPI) {
                $start_time = microtime(true);
                try {
                    $resp = $gtsAPI->route($uri,$_SERVER['REQUEST_METHOD'],$_REQUEST);
                } catch (\Throwable $e) {
                    // Исключение/ошибка маршрутизации — пишем трассировку в лог
                    // и отдаём JSON с сообщением вместо «голого» 500.
                    $modx->log(modX::LOG_LEVEL_ERROR,
                        '[gtsAPI plugin] EXCEPTION: ' . $e->getMessage() .
                        ' in ' . $e->getFile() . ':' . $e->getLine() .
                        ' | uri=' . implode('/', $uri) . ' | method=' . $_SERVER['REQUEST_METHOD'] .
                        "\nTrace:\n" . $e->getTraceAsString()
                    );
                    $resp = ['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()];
                }

                header("Access-Control-Allow-Credentials: true");
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, HEAD, OPTIONS, PUT, DELETE, PATCH");
                header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
                $resp['time'] = number_format(round(microtime(true) - $start_time, 7), 7);
                // JSON_INVALID_UTF8_SUBSTITUTE — битые UTF-8 байты заменяются на U+FFFD
                // вместо тихого возврата false с пустым ответом. Параллельно лог,
                // чтобы найти источник битых данных.
                $json = json_encode($resp, JSON_INVALID_UTF8_SUBSTITUTE);
                if ($json === false) {
                    $modx->log(modX::LOG_LEVEL_ERROR,
                        '[gtsAPI plugin] json_encode failed: ' . json_last_error_msg() .
                        ' | uri=' . $uri
                    );
                    $json = json_encode([
                        'success' => false,
                        'message' => 'JSON encode error: ' . json_last_error_msg(),
                    ]);
                } elseif (json_last_error() !== JSON_ERROR_NONE) {
                    // Запросы прошли, но были битые байты — фиксируем чтобы найти источник
                    $modx->log(modX::LOG_LEVEL_ERROR,
                        '[gtsAPI plugin] json_encode warning (битый UTF-8 в данных): ' .
                        json_last_error_msg() . ' | uri=' . $uri
                    );
                }
                exit($json);
            }
        }
        break;
}
return '';
