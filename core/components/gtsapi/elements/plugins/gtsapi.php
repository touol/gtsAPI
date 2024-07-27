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