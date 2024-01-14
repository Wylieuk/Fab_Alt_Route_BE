<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class apiAuth{


		function __construct(){
			
		}

		function isAuthenticated($passedApiKey){
			//$username = strtolower($username);
			global $config;
			include('config/config.users.php');
			
			//authenicated
			if (in_array($passedApiKey, $apiKey)){
                    $this->username = 'api';
                    $this->group_id = 'api_group';
					//$this->apiKey = $passedApiKey;
					return $this;	
			}
			
			//failed
			return false;
		}









}
?>