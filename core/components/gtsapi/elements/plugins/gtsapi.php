<?php
/** @var modX $modx */
/* @var array $scriptProperties */
//echo $_SERVER['REQUEST_URI'];exit();
switch ($modx->event->name) {
    case 'OnHandleRequest':
        $uri = explode('?',$_SERVER['REQUEST_URI']);
        $uri = explode('/',$uri[0]);
        if($uri[1] == 'api'){
            
            /* @var gtsAPI $gtsAPI*/
            $gtsAPI = $modx->getService('gtsapi', 'gtsAPI', 
                $modx->getOption('gtsapi_core_path', $scriptProperties, $modx->getOption('core_path') . 'components/gtsapi/') . 'model/');
            if ($gtsAPI instanceof gtsAPI) {
                $resp = $gtsAPI->route($uri,$_SERVER['REQUEST_METHOD'],$_REQUEST);
                exit(json_encode($resp));
            }
        }
        break;
}
return '';