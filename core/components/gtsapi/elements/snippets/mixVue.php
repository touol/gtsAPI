<?php
/** @var modX $modx */
/** @var array $scriptProperties */
$name_lower = strtolower($app);
$debug = false;
$vapi = 4;
// $dev_path = 'http://'.$modx->getOption('http_host')
//     . ':'
//     . '3000/';
// if($debug_mode = $modx->getOption('gtsapi_debug_mode',null,false)){
//     if($pf = @fsockopen($modx->getOption('http_host'),3000, $err, $err_string, 1))
//     {
//         // $debug = true;
//         fclose($pf);
//         $checkdebug = file_get_contents('http://'.$modx->getOption('http_host').':3000/public/checkdebug.txt');
//         if($checkdebug == $name_lower) $debug = true;
//     }
// }
// $modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/'.'css/web/primevue/lara-light-green/theme.css');
// $modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/'.'css/web/primevue/primeflex.min.css');
// $modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/css/web/primeicons/primeicons.min.css');
if($package = $modx->getObject('transport.modTransportPackage', ['package_name:LIKE' => 'gtssapi'])) {
    $vapi = strtotime($package->installed);
}
$modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/js/web/pvtables/style.css?v='.$vapi);
if(!$debug){
    

    $assets_gtsapi_url = $modx->getOption('server_protocol').'://'.$modx->getOption('http_host').$modx->getOption('assets_url').'components/gtsapi/';
    $imports = [];
    if($load_vue = $modx->getOption('gtsapi_load_vue',null,true)){
        $imports['imports']['vue'] = $assets_gtsapi_url.'js/web/vue.esm-browser.js';
        $pvtables_path = $modx->getOption('assets_path').'components/gtsapi/js/web/pvtables/';
        $imports['imports']['pvtables/dist/pvtables'] = $assets_gtsapi_url.'js/web/pvtables/pvtables.js?v='.$vapi;
    }
    
    if(!empty($imports)){
        $modx->regClientHTMLBlock(
            '<script type="importmap">
            '.json_encode($imports).'
            </script>'
        );
    }

    $v = 0;
    if($package = $modx->getObject('transport.modTransportPackage', ['package_name:LIKE' => $name_lower])) {
        $v = strtotime($package->installed);
    }
    $assets_url = $modx->getOption('assets_url').'components/'
        .$name_lower.'/';
    $modx->regClientCSS($assets_url.'web/css/main.css?v='.$v);
    if(isset($config) and is_array($config)){
        $modx->regClientHTMLBlock(
            '<script>
                let '.$name_lower.'Configs ='.json_encode($config).'
            </script>'
        );
    }
    $modx->regClientHTMLBlock(
        '<script type="module" src="'.$assets_url.'web/js/main.js?v='.$v.'"></script>'
    );
}
// else{
//     $modx->regClientHTMLBlock(
//         '<script type="module" src="'.$dev_path.'@vite/client"></script>'
//     );
//     $modx->regClientHTMLBlock(
//         '<script type="module" src="'.$dev_path.'src/main.js"></script>'
//     );
// }
return '<div id="'.$name_lower.'"></div>';
