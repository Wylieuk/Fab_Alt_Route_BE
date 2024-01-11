<?php


defined("isInSideApplication")?null:die('no access');

global $user;

$response = array('status'=>'authenticated');

//debug($JWT->getToken());exit;
ob_start('ob_gzhandler');
headers::accessControlAsRefer();
headers::allowCredencials();


global $JWT;
headers::set('X-JWT', $JWT->getToken());
headers::set('X-sessionId', session_id());
headers:: expose(array('X-JWT', 'X-sessionId'));
headers::json();
echo (json_encode($response, JSON_PRETTY_PRINT));
exit;