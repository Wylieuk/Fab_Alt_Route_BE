<?php
defined("isInSideApplication")?null:die('no access');

//unsets JWT cookie
setcookie('JWT', '', time() - 3600, dirname($_SERVER['PHP_SELF']) . '/', '', false, false);
//setcookie('PHPSESSID', '', time() - 3600, '/', '', false, false);

$response['loginToken'] = encryption::medHash($config['siteaddress'] . session_id());
//debug($_SERVER);








ob_start('ob_gzhandler');
headers::accessControlAsRefer();
headers::allowCredencials();
headers::json();

echo (json_encode($response, JSON_PRETTY_PRINT));
exit;