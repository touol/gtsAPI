<?php
if (empty($_REQUEST['action']) and empty($_REQUEST['gtsapi_action'])) {
    $message = 'Access denied action.php';
    echo json_encode(
            ['success' => false,
            'message' => $message,]
            );
    return;
}

define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$_REQUEST['action'] = $_REQUEST['action'] ? $_REQUEST['action'] : $_REQUEST['gtsapi_action'];

if(!$gtsapi = $modx->getService("gtsapi","gtsapi",
    MODX_CORE_PATH."components/gtsapi/model/",[])){
    $message =  'Could not create gtsapi!';
	echo json_encode(
		['success' => false,
		'message' => $message,]
		);
	return;
}

$modx->lexicon->load('gtsapi:default');

$response = $gtsapi->handleRequest($_REQUEST['action'],$_REQUEST);

echo json_encode($response);
exit;