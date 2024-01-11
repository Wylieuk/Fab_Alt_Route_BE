<?php
defined("isInSideApplication")?null:die('no access');

function new_smarty(){
	global $config;
	global $lang;
	require_once ('libs/smarty/libs/Smarty.class.php');
	$smarty = new Smarty();
	require('config/config.smarty.php');
		
	return $smarty;
	
	
}

?>