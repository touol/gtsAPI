<?php
/** @var modX $modx */
/** @var array $scriptProperties */

// PK таблицы modx_transport_packages = signature, поэтому без sortby getObject
// берёт первую (старую) версию пакета — её updated фиксирован, ?v=... не обновляется.
// Берём актуальную по updated DESC.
$vapi = 6;
$c = $modx->newQuery('transport.modTransportPackage');
$c->where(['package_name:LIKE' => '%gtsapi%']);
$c->sortby('updated', 'DESC');
$c->limit(1);
if($package = $modx->getObject('transport.modTransportPackage', $c)) {
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
        // Сборка Vue: prod (147 КБ) или dev (478 КБ, с предупреждениями в консоли).
        // Обе с компилятором шаблонов — он нужен и на проде: на нём держатся
        // динамические шаблоны (экшены и колонки PVTables приходят строками из
        // конфига таблицы, vue3-runtime-template-next, чанки Fenom). Компилятора
        // нет только у runtime-сборок (vue.runtime.*) — их сюда ставить нельзя.
        $vue_file = $modx->getOption('gtsapi_vue_build', null, 'prod') === 'dev'
            ? 'vue.esm-browser.js'
            : 'vue.esm-browser.prod.js';
        $imports['imports']['vue'] = $assets_gtsapi_url.'js/web/'.$vue_file;
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
        let PVTableConfigTable ="'.$table.'"
        </script>'
    );
    $modx->regClientHTMLBlock(
        '<script type="module">
        
        import { createApp } from \'vue\'
        import myPVTables from \'pvtables/dist/pvtables\'
        import { PVTable } from \'pvtables/dist/pvtables\'
        const app = createApp(PVTable)
        app.use(myPVTables);
        
        app.mount(\'#pvtable\')

        </script>'
    );

return '<div id="pvtable"></div>';
