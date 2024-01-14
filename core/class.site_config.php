<?php
defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class site_config{
	
	
	
	function getALL(){
		$db = new db;
		$query = 'SELECT * FROM `site_config`';
		$db->query($query);
		$results = $db->fetch_array();
		
		foreach ($results as $result){
			$this->{$result['name']}['value'] = 	$result['value'];
			$this->{$result['name']}['type'] = 	$result['type'];				
		}
		return $this;
	}
	
	static function loadCONFIG(){
		$db = new db;
		$query = 'SELECT * FROM `site_config`';
		$db->query($query);
		$results = $db->fetch_array();
		
		foreach ($results as $result){
			$config[$result['name']] =	$result['value'];			
		}
		return $config;
	}
	
	function saveALL($data){
		
		if (!is_array($data)){return false;}
		
		foreach ($data as $name => $value){
			if (!$this->saveSETTING($name, $value)){return false;}
		}
		return true;
	}
	
	function getSETTING($name){
		if ($this->getALL()){
			return $this->$name;
		}
		return false;
	}
	
	function saveSETTING($name, $value){echo __FUNCTION__;
		$setting_to_save['name'] = $name;
		$setting_to_save['value'] = $value;
		$db = new db;
		$query = $db->build_update('site_config', $setting_to_save, 'WHERE `name` LIKE :name');
		//echo $query;
		if (!$db->preparedQuery($query, ['name' => $name])){return false;}
		return true;
	}
	
	static function save($data){
		print_r($data);
		$this_site_config = new site_config;
		if (!$this_site_config->saveALL($data)){return false;}
		return true;
	}
	
}
	

?>