<?php
defined("isInSideApplication")?null:die('no access');
class user_group{
	
	static function getGroupName($groupId){

		


		global $config;
		global $cachedGroupName;
		
		if (isset($cachedGroupName[$groupId])){
			return $cachedGroupName[$groupId];
		}
		
		$db = new db;
		$query = 'SELECT * FROM `'.$config['coreTablePrefix'].'user_groups` WHERE `id` = :groupId';
		$db->preparedQuery($query, ['groupId' => $groupId]);
		$result =  $db->fetch_array();

		$cachedGroupName[$groupId] = $result[0]['group_name'] ?? '';
		return $cachedGroupName[$groupId];
	}
	
	static function getIdFromGroupName($groupName){
		global $config;
		global $cachedGroupId;
		
		if (isset($cachedGroupId[$groupName])){
			return $cachedGroupId[$groupName];
		}
		$db = new db;
		$query = 'SELECT * FROM `'.$config['coreTablePrefix'].'user_groups` WHERE `group_name` = :groupName';
		$db->preparedQuery($query, ['groupName' => $groupName]);
		$result =  $db->fetch_array();
		$cachedGroupId[$groupName] =  $result[0]['id'];
		return $cachedGroupId[$groupName];
	}	
	
	static function getAllGroups(){
		global $config;
		$db = new db;
		$query = 'SELECT * FROM `'.$config['coreTablePrefix'].'user_groups` ORDER BY `id`';
        $db->query($query);
        if (!$db->error()){
		    $result =  $db->fetch_array();		
            return $result;
        } else {
            return false;
        }
	}
}

?>