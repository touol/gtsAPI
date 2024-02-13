<?php
/** @var modX $modx */
/** @var array $scriptProperties */
$name_lower = strtolower($app);
$debug = false;
$dev_path = 'http://'.$modx->getOption('http_host')
    . ':'
    . '3000/';
if($debug_mode = $modx->getOption('gtsapi_debug_mode',null,false)){
    if($pf = @fsockopen($modx->getOption('http_host'),3000, $err, $err_string, 1))
    {
        // $debug = true;
        fclose($pf);
        $checkdebug = file_get_contents('http://'.$modx->getOption('http_host').':3000/public/checkdebug.txt');
        if($checkdebug == $name_lower) $debug = true;
    }
}
$modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/'.'css/web/primevue/lara-light-green/theme.css');
$modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/'.'css/web/primevue/primeflex.min.css');
$modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/css/web/primeicons/primeicons.min.css');
if(!$debug){
    

    $assets_gtsapi_url = $modx->getOption('server_protocol').'://'.$modx->getOption('http_host').$modx->getOption('assets_url').'components/gtsapi/';
    $imports = [];
    if($load_vue = $modx->getOption('gtsapi_load_vue',null,true)){
        $imports['imports']['vue'] = $assets_gtsapi_url.'js/web/vue.global.prod.js';
        //$imports['imports']['../ru.json'] = $assets_gtsapi_url.'js/web/primevue/ru.json';
        $primevue_path = $modx->getOption('assets_path').'components/gtsapi/js/web/primevue/';
        if(file_exists($primevue_path.'importmaps.json')){
            $importmaps = json_decode(file_get_contents($primevue_path.'importmaps.json'),1);
            if(is_array($importmaps)){
                foreach($importmaps as $k=>$v){
                    $imports['imports'][$k] = $assets_gtsapi_url.'js/web/primevue/'.$v;
                }
            }
        }
    }
    
    if(!empty($imports)){
        $modx->regClientHTMLBlock(
            '<script type="importmap">
            '.json_encode($imports).'
            </script>'
        );
    }

    $assets_url = $modx->getOption('assets_url').'components/'
        .$name_lower.'/';
    $modx->regClientCSS($assets_url.'web/css/main.css');
    $modx->regClientHTMLBlock(
        '<script type="module" src="'.$assets_url.'web/js/main.js"></script>'
    );
}else{
    $modx->regClientHTMLBlock(
        '<script type="module" src="'.$dev_path.'@vite/client"></script>'
    );
    $modx->regClientHTMLBlock(
        '<script type="module" src="'.$dev_path.'src/main.js"></script>'
    );
}
return '<div id="'.$name_lower.'"></div>';