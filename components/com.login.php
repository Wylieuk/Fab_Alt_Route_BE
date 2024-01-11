<?php
defined("isInSideApplication")?null:die('no access');
//debug($page->submitted_data);exit;

$_SESSION['securityToken'] = encryption::medHash($config['siteaddress'].session_id());

if (isset($page->submitted_data['login'])){
	$this->login_failed = true;
}else{
	$this->login_failed = false;
}
?>