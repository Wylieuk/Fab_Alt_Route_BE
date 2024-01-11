<?php
defined("isInSideApplication")?null:die('no access');

function data_submit($data, $files, &$page){
	/*	
	//add security to forms by adding <input type="hidden" name="securityToken" id="securityToken" value="{$smarty.session.securityToken}">
	if (isset($data['securityToken']) and isset($_SESSION['securityToken'])){
		if($data['securityToken'] !== $_SESSION['securityToken']){
			die('invalid securityToken');
		}
    }
    */

	$error = '';
	//echo __FILE__;
	global $config;
	global $lang;
	//trim of unwanted items
	unset ($data['ajax']);
	unset ($data['PHPSESSID']);
	//unset ($data['username']);
	//unset ($data['password']);
    unset($data['JWT']);
 
	$returnedData['dirty'] = $data;
	foreach($data as $key=>$val){
		$returnedData['clean'][$key] = sanitize::userInput($val);
	}


	foreach ($returnedData['clean'] as $query_name => $query_value) {
		if (is_array($query_value)) {
			$query_value = serialize($query_value);
		}
		$page->query_string_array[$query_name] = (string) $query_name . '=' . urlencode((string) $query_value);
		$page->query_array[$query_name] = $query_value;
	}

	$page->query_string 	    =  implode('&amp;', $page->query_string_array);
	if (PHP_SAPI !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
		$page->full_url 		=  'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . implode('&', $page->query_string_array);
		$page->current_url 		=  'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . implode('&amp;', $page->query_string_array);
	} else {
		$page->full_url 		= '';
		$page->current_url 		= '';
	}
	//}

	if (count($files)>0){
		$returnedData['clean']['files'] = $files;
	}
/*
	if (isset($returnedData['clean']['action'])){
		$action = new action($page);
		$returnedData['clean'] = (array)$action->create($returnedData['clean']['action'], $returnedData['clean']);
	}
*/	
	

	
	return $returnedData;	
}
