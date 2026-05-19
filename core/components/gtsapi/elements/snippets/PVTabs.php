<?php
/** @var modX $modx */
/** @var array $scriptProperties */
$vapi = 6;
if($package = $modx->getObject('transport.modTransportPackage', ['package_name:LIKE' => '%gtsapi%'])) {
    $vapi = strtotime($package->updated);
}
if($_SERVER['SERVER_PORT'] == 80){
        $http1 = "http";
    }else if($_SERVER['SERVER_PORT'] == 443){
        $http1 = "https";
    }
$modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/js/web/pvtables/pvtables.css?v='.$vapi);

    $assets_gtsapi_url = $http1.'://'.$modx->getOption('http_host').$modx->getOption('assets_url').'components/gtsapi/';
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
    $modx->regClientHTMLBlock(
        '<script>
        let PVTabsConfigs ='.json_encode($tabs).'
        </script>'
    );
    $modx->regClientHTMLBlock(
        '<script type="module">
        
        import { createApp } from \'vue\'
        import myPVTables from \'pvtables/dist/pvtables\'
        import { PVTab } from \'pvtables/dist/pvtables\'
        const app = createApp(PVTab)
        app.use(myPVTables);
        
        app.mount(\'#pvtab\')

        </script>'
    );

return '<div id="pvtab"></div>';
