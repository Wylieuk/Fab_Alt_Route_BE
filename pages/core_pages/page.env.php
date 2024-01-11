<?php

defined("isInSideApplication")?null:die('no access');

$output = (object)[
    'time' => date('Y-m-d\TH:i:s'),
    'timeZone' => date_default_timezone_get()
];



ob_start('ob_gzhandler');

headers::accessControlAsRefer();
header('Content-Encoding: gzip');
//headers::allowCredencials();
//headers::set('X-JWT', $JWT->getToken());
//headers::set('X-sessionId', session_id());
//headers:: expose(array('X-JWT', 'X-sessionId'));
headers::json();
echo (json_encode($output, JSON_PRETTY_PRINT));
exit;