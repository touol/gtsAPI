<?php
$log = "1\r\n";
file_put_contents(dirname(__FILE__).'/log.txt',$log);
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';
$log .= $modx->getOption("site_name");
$log .= "2\r\n";
file_put_contents(dirname(__FILE__).'/log.txt',$log);
$gtsAPI = $modx->getService('gtsAPI', 'gtsAPI', MODX_CORE_PATH . 'components/gtsapi/model/', []);
if (!$gtsAPI) {
    $log .= "Не удалось загрузить сервис gtsAPI!\r\n";
    file_put_contents(dirname(__FILE__).'/log.txt',$log);
    return "";
}
$log .= "start!\r\n";
file_put_contents(dirname(__FILE__).'/log.txt',$log);
$resp = $gtsAPI->cron([]);
$log .= $resp['message']."\r\n";
$log .= $resp['data']['log']."\r\n";
file_put_contents(dirname(__FILE__).'/log.txt',$log);
exit;
