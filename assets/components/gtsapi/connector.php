<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
} else {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var gtsAPI $gtsAPI */
$gtsAPI = $modx->getService('gtsAPI', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/');
$modx->lexicon->load('gtsapi:default');

// handle request
$corePath = $modx->getOption('gtsapi_core_path', null, $modx->getOption('core_path') . 'components/gtsapi/');
$path = $modx->getOption('processorsPath', $gtsAPI->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);