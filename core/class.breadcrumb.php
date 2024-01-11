<?php
defined("isInSideApplication")?null:die('no access');
class breadcrumb{
	
	function __constructor(){
		
	}
	
	function add($text, $url){
		$this->elements[$text] = $url;
	}
	
	function discardSnipet($crumb){
		unset($this->query_urlsnipets_array[$crumb]);
		unset($this->query_text_array[$crumb]);
	}
	
	
}
?>