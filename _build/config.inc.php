<?php

if (!defined('MODX_CORE_PATH')) {
    $path = dirname(__FILE__);
    while (!file_exists($path . '/core/config/config.inc.php') && (strlen($path) > 1)) {
        $path = dirname($path);
    }
    define('MODX_CORE_PATH', $path . '/core/');
}

return [
    'name' => 'gtsAPI',
    'name_lower' => 'gtsapi',
    'version' => '1.1.7',
    'release' => 'beta',
    // Install package to site right after build
    'install' => true,
    'encryption_enable' => false,
    'encryption' => array(
        'username' => '',
        'api_key' => '',
    ),
    // Which elements should be updated on package upgrade
    'update' => [
        'chunks' => true,
        'menus' => true,
        'plugins' => true,
        'resources' => false,
        // НЕ перезаписывать настройки при обновлении: в них лежат значения,
        // выставленные админом сайта, — метка сайта (gtsapi_site_key),
        // конфигурация страниц админки, флаги. Каждая установка затирала их
        // значениями из сборки; именно так gtsapi_site_key оказалась пустой
        // и на деве, и на проде, а вместе с ней перестали ставиться
        // справочные данные, привязанные к сайту.
        // На ПЕРВОЙ установке настройки всё равно создаются — этот флаг
        // управляет только обновлением уже существующих.
        'settings' => false,
        'snippets' => true,
        'templates' => false,
        'widgets' => false,
        'policies' => false,
        'events' => false,
    ],
    // Which elements should be static by default
    'static' => [
        'plugins' => false,
        'snippets' => false,
        'chunks' => false,
    ],
    // Log settings
    'log_level' => !empty($_REQUEST['download']) ? 0 : 3,
    'log_target' => php_sapi_name() == 'cli' ? 'ECHO' : 'HTML',
    // Download gtsapi.zip after build
    'download' => !empty($_REQUEST['download']),
    // Copy file
    'copy' => !empty($_REQUEST['copy']),
    'copy_server' => '',
    #'copy_server' => 'http://s16305.h4.modhost.pro/copy_package.php',
    'auto_install' => false,
];