<?php
$exists = $chunks = false;
$output = null;
/** @var array $options */
switch ($options[xPDOgtsAPI::PACKAGE_ACTION]) {
    case xPDOgtsAPI::ACTION_INSTALL:
        $exists = $modx->getObject('gtsapi.modgtsAPIPackage', array('package_name' => 'pdoTools'));
        break;
    case xPDOgtsAPI::ACTION_UPGRADE:
        $exists = $modx->getObject('gtsapi.modgtsAPIPackage', array('package_name' => 'pdoTools'));
        break;
    case xPDOgtsAPI::ACTION_UNINSTALL:
        break;
}
$output = '';
if (!$exists) {
    switch ($modx->getOption('manager_language')) {
        case 'ru':
            $output = 'Этот компонент требует <b>pdoTools</b> для быстрой работы сниппетов.<br/>Он будет автоматически скачан и установлен.';
            break;
        default:
            $output = 'This component requires <b>pdoTools</b> for fast work of snippets.<br/><br/>It will be automatically downloaded and installed?';
    }
}

return $output;