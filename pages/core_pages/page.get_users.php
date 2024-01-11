<?php
defined("isInSideApplication")?null:die('no access');

if (!user::hasAccess(array('admin', 'manager'))) {
    headers::permissionError();
}

$response = user::loadAllUsers();

$modifiedResponse['response'] = $response;
$modifiedResponse['token'] = encryption::medHash(json_encode($response));

if (isset($this->data['token']) && $this->data['token'] === $modifiedResponse['token']) {
    if (isset($_SERVER['HTTP_REFERER']) &&  $_SERVER['HTTP_REFERER'] != null) {
        $origin = trim($_SERVER['HTTP_REFERER'], '/');
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    }
    
    header('Content-Type: application/json');
    die(json_encode(["applicationNotModified" => 1]));
}

ob_start('ob_gzhandler');

if (isset($_SERVER['HTTP_REFERER']) &&  $_SERVER['HTTP_REFERER'] != null) {
    $origin = trim($_SERVER['HTTP_REFERER'], '/');
    header("Access-Control-Allow-Origin: $origin");
} else {
    //trigger_error('ORIGIN NOT SET');
}
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

echo (json_encode($modifiedResponse, JSON_PRETTY_PRINT));
exit;