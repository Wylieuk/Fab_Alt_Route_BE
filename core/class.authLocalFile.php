<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class authLocalFile{


		function __construct(){
			
		}

		function isAuthenticated($username,$password){
			$username = strtolower($username);
			global $config;
			include('config/config.users.php');

			//authenicated
			if (isset($users[$username])){
				if ($users[$username] == $password){
					$this->username = $username;
					return $this;	
				}
			}
			
			//failed
			return false;
		}









}
?>