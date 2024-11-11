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
            if(!empty($plugins)){
                foreach ($plugins as $package => $plugins2) {
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
                                if (isset($modx->map[$class])) {
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
    break;
    case 'OnHandleRequest':
        $uri = explode('?',$_SERVER['REQUEST_URI']);
        $uri = explode('/',$uri[0]);
        if($uri[1] == 'api'){
            
            /* @var gtsAPI $gtsAPI*/
            $gtsAPI = $modx->getService('gtsapi', 'gtsAPI', 
                $modx->getOption('gtsapi_core_path', $scriptProperties, $modx->getOption('core_path') . 'components/gtsapi/') . 'model/');
            if ($gtsAPI instanceof gtsAPI) {
                $start_time = microtime(true);
                $resp = $gtsAPI->route($uri,$_SERVER['REQUEST_METHOD'],$_REQUEST);
                
                header("Access-Control-Allow-Credentials: true");
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, HEAD, OPTIONS, PUT, DELETE, PATCH");
                header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
                $resp['time'] = number_format(round(microtime(true) - $start_time, 7), 7);
                exit(json_encode($resp));
            }
        }
        break;
}
return '';