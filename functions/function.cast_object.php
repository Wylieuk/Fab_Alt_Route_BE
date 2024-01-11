<?php
defined("isInSideApplication")?null:die('no access');

function cast_object($arr) {
   	if(is_array($arr)) $arr = (object) $arr;
   	if(is_object($arr)) {
       	$new = new stdClass();
       	foreach($arr as $key => $val) {
			//$key = str_replace(' ', '_', $key);
           	$new->$key = cast_object($val);
       	}
   	}
   	else $new = $arr;
   return $new;       
}

?>