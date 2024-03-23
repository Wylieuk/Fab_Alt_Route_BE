<?php
defined("isInSideApplication")?null:die('no access');

if (!user::hasAccess(array('admin'))) {
    headers::permissionError();
}

headers::accessControlAsRefer();
headers::allowCredencials();
headers::set('X-sessionId', session_id());
headers::expose(array('X-sessionId'));

$cc = new config();
$cc->updateConfig($page->dirtyData['config']);
$output['response'] = 'Config Updated';

// Send the JSON...
headers::json();
die(json_encode($output));
