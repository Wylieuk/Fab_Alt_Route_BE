<?php

defined("isInSideApplication")?null:die('no access');

if (!user::hasAccess(array('admin'))) {
    headers::permissionError();
}

$output['response'] = 'Error deleteing user data';
try{
    $enc = new encryption;
    $insertId = user::deleteUser(json_decode($enc->cryptJsDecrypt($this->data['user'], VARIABLE_CYPHER_KEY))->id);
} 
catch (exception $e) {
    $output['error'] = 'Error deleteing user data';
}


$output['response'] = 'Successfully deleted user';


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
echo (json_encode($output, JSON_PRETTY_PRINT));
exit;