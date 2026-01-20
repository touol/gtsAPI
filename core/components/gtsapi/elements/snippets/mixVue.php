<?php
/** @var modX $modx */
/** @var array $scriptProperties */
$name_lower = strtolower($app);
$debug = false;
$vapi = 4;

// Проверяем параметр enableSSR
$enableSSR = !empty($enableSSR) ? (bool)$enableSSR : false;

// Проверяем параметр enableCache
if (isset($enableCache)) {
    // Если enableCache = 0 или '0' или 'false' или false то false
    $enableCache = !in_array($enableCache, [0, '0', 'false', false], true);
} else {
    $enableCache = true; // по умолчанию true
}
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
    $vapi = strtotime($package->updated);
}
$modx->regClientCSS($modx->getOption('assets_url').'components/gtsapi/js/web/pvtables/pvtables.css?v='.$vapi);
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
// SSR рендеринг если включен параметр enableSSR
if ($enableSSR) {
    try {
        // Подключаем класс SSR рендерера
        $rendererPath = $modx->getOption('core_path') . 'components/gtsapi/classes/VueNodeSSRRenderer.class.php';
        if (file_exists($rendererPath)) {
            require_once $rendererPath;
            
            // Создаем рендерер с параметром кэширования
            $ssrRenderer = new VueNodeSSRRenderer($modx, $enableCache);
            
            // Подготавливаем конфигурацию для SSR
            $ssrConfig = isset($config) && is_array($config) ? $config : [];
            $ssrConfig['appName'] = $name_lower;
            
            // Рендерим компонент на сервере
            $ssrHtml = $ssrRenderer->render($app, $ssrConfig);
            
            // Добавляем данные для гидратации
            if (isset($config) && is_array($config)) {
                $modx->regClientHTMLBlock(
                    '<script>
                        window.__SSR_DATA__ = ' . json_encode($config) . ';
                    </script>'
                );
            }
            
            // Добавляем скрипт для гидратации
            $modx->regClientHTMLBlock(
                '<script type="module">
                    // Ждем загрузки DOM
                    document.addEventListener("DOMContentLoaded", function() {
                        // Гидратация Vue приложения
                        if (window.Vue && window.__SSR_DATA__) {
                            const app = Vue.createApp({
                                data() {
                                    return window.__SSR_DATA__ || {};
                                },
                                mounted() {
                                    console.log("Vue app hydrated successfully");
                                }
                            });
                            app.mount("#' . $name_lower . '");
                        }
                    });
                </script>'
            );
            
            // Очищаем рендерер
            $ssrRenderer->cleanup();
            
            return $ssrHtml;
            
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, 'VueSSRRenderer class not found: ' . $rendererPath);
        }
        
    } catch (Exception $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'SSR rendering failed: ' . $e->getMessage());
        
        // Для отладки - показываем ошибку в HTML комментарии
        $errorComment = '<!-- SSR Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        
        // Fallback к обычному клиентскому рендерингу
        // Если компонент использует внешние зависимости (pvtables и т.д.), 
        // которые недоступны в Node.js, просто продолжаем с клиентским рендерингом
        
        // Возвращаем div с ошибкой для отладки
        return $errorComment . '<div id="'.$name_lower.'"></div>';
    }
}

return '<div id="'.$name_lower.'"></div>';
