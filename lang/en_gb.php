<?php
defined("isInSideApplication")?null:die('no access');

$lang['login_title']  = 'Login';

function lang($key){
    global $lang;
    return $lang[$key] ?? $key;

}
