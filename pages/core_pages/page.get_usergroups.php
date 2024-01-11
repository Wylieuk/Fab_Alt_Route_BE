<?php

defined("isInSideApplication")?null:die('no access');

$response = user_group::getAllGroups();

foreach($response as &$group){
    $group['value'] = $group['group_name'];
}

$modifiedResponse['response'] = $response;
$modifiedResponse['token'] = encryption::medHash(json_encode($response));


if (isset($this->data['token']) && $this->data['token'] === $modifiedResponse['token']) {
    if (isset($_SERVER['HTTP_REFERER']) &&  $_SERVER['HTTP_REFERER'] != null) {
        $origin = trim($_SERVER['HTTP_REFERER'], '/');
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    }
    //header("HTTP/1.1 304 Not Modified");
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
header('Content-Encoding: gzip');
echo (json_encode($modifiedResponse, JSON_PRETTY_PRINT));
exit;