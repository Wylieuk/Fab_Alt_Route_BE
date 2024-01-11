<?php
defined("isInSideApplication")?null:die('no access');

function build_query($requestArray){
	foreach ($requestArray as $key => &$value){
			$value = $key.'='.$value;
	}
	return implode('&',$requestArray);
}

/*
	if($_SESSION['request']){
		debug($_SESSION);
		echo '<script>window.location = "index.php?',build_query($_SESSION['request']).'";</script>';
		exit;
	}
	*/
?>