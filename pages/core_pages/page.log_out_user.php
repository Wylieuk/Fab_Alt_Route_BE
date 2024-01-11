<?php
defined("isInSideApplication")?null:die('no access');

$output['error'] = false;

try {
    if (!isset($page->data['username'])) {
        throw new Exception( 'Error: username required' );
    }

    if (!user::hasAccess(array('admin'))) {
        headers::permissionError();
    }
    
    user::logOutUser($page->data['username']);
    
    $output['response'] = 'Successfully logged out user: ' . $page->data['username'];
}
catch(exception $e) {
    $output['error'] = $e->getMessage();
}

headers::accessControlAsRefer();
headers::allowCredencials();
headers::json();

echo (json_encode($output, JSON_PRETTY_PRINT));
exit;