<?php

function login_check(){
global $config;

	$headers = getallheaders();

	//print_r($headers);
	if(!empty($headers['Authorization'])){
		switch (true){

			// basic auth = username:password
			case strpos($headers['Authorization'], 'Basic') === 0:
				$basicAuthHeader = explode(':', base64_decode(substr($headers['Authorization'], 6)));
				$_REQUEST['username'] = $basicAuthHeader[0] ?? '';
				$_REQUEST['password'] = $basicAuthHeader[1] ?? '';
				break;

			// bearer  = 1234567890....
			case strpos($headers['Authorization'], 'Bearer') === 0:
				$bearerHeader = substr($headers['Authorization'], 7);
				$_REQUEST['apikey'] = $bearerHeader;
				break;
				
		}
	}



	if(!empty($headers['Authorization']) && strpos($headers['Authorization'], 'Basic') === 0){
		$basicAuthHeader = explode(':', base64_decode(substr($headers['Authorization'], 6)));
		$_REQUEST['username'] = $basicAuthHeader[0] ?? '';
		$_REQUEST['password'] = $basicAuthHeader[1] ?? '';
	}
	
	//print_r((getallheaders()['Authorization']));

    
	if (isset($_REQUEST['securityToken']) and isset($_REQUEST['username']) and isset($_SESSION['securityToken'])) {
		if ($_REQUEST['securityToken'] !== $_SESSION['securityToken']) {
           // log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | form securityToken mismatch' . ' | User: ' . $_REQUEST['username'] .  PHP_EOL, true);
			//return false;
		}
    }

    if($config['restrictLoginToIps'] && !block::allExceptIps($config['blockAllExceptIps'])){
        log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | user IP not whitelisted | User: ' . $_REQUEST['username'] . PHP_EOL, true);
        return false;
    }
	//debug($_SESSION['sessionId']);
	//debug(session_id());

 
	global $config;
	global $authenticateenticated_user;
//echo __FUNCTION__;
	$authenticate = new authenticate;
    
	$authenticate->deleteExpiredSessions();
	
	//check if login token exists and matches
	//require_once ('class.jwt.php');
	$jwt = new jwt($config['JSON_WEB_TOKEN_KEY']);
	$jwt->set_client_storage_type($config['JSON_WEB_TOKEN_STORAGE']);

	if (isset($_REQUEST['page']) && @$_REQUEST['page'] == 'login'){	
		$jwt->delete_token();
		$authenticate->deleteUserSessions();
		session_regenerate_id();
		return false;
    }

   
     
	
	if(isset($_REQUEST['apikey'])){
		$jwt->delete_token();
		$authenticate->deleteUserSessions();
		session_regenerate_id();
		$apiAuth = new apiAuth;
		if($apiUserDetails = $apiAuth->isAuthenticated($_REQUEST['apikey'])){
			
			$jwt = new jwt($config['JSON_WEB_TOKEN_KEY']);
			$jwt->set_client_storage_type($config['JSON_WEB_TOKEN_STORAGE']);
			//foreach ($apiUserDetails as $key => $user_detail){
				$jwt->set_payload('apiData', $apiUserDetails);
			//}
			$token = $jwt->create_token();
			$authenticate->saveNewUserSession(array('username' => 'apiKey_'.$_REQUEST['apikey'],'sessionId'=>session_id(), 'JWT'=>$token));	
			$user = new user;
			foreach($user->loggedOnUser($token) as $k => $v){
				$user->{$k} = $v;
			};
			unset($user->JWTData);
			$user->afterLogin();
			return $token;
		}
	}
	
    //debug($_REQUEST);exit; 
	//username and password is used
	if (isset($_REQUEST['username']) && isset($_REQUEST['password'])){
        if (block::isBruteForcedBlocked(['ip' => ' | ' . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN')])){
            log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | User logged denied (bruteforce exceeded) | User: ' . $_REQUEST['username'] . PHP_EOL, true);
           
            headers::accessControlAsRefer();
            headers::json();
            die(json_encode(['error' => 'Login attempts exceeded, please try again later', 'response' => 'Login attempts exceeded, please try again later']));
        }
        //debug($_REQUEST);exit;
		$jwt->delete_token();
		$authenticate->deleteUserSessions();
		session_regenerate_id();
        $authenticate->createUserSession($_REQUEST['username']);
       
        

		if ($authenticate->checkCredentials($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['twoFactorAuthCode'] ?? null, $_REQUEST['remember2fa'] ?? null)){         
			$user_details = $authenticate->userDetails();
			$jwt = new jwt($config['JSON_WEB_TOKEN_KEY']);
			$jwt->set_client_storage_type($config['JSON_WEB_TOKEN_STORAGE']);
//debug($user_details);exit;
			foreach ($user_details as $key => $user_detail){
				$jwt->set_payload($key, $user_detail);
            }
            $jwt->set_payload('ip_address', ' | ' . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'));
			$token = $jwt->create_token();
            $authenticate->saveNewUserSession(array('username' => $_REQUEST['username'], 'sessionId'=>session_id(), 'JWT'=>$token));
            log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | User logged in successfully | User: ' . $_REQUEST['username'] . PHP_EOL, true);


			$user = new user;
			foreach($user->loggedOnUser($token) as $k => $v){
				$user->{$k} = $v;
			};
			unset($user->JWTData);
			$user->afterLogin();

			return $token;
		} else {

            // added to stop bruteforce attacks
            block::logFailedLogin(['username' => $_REQUEST['username'], 'ip' => ' | ' . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN')]);

            log_writer::write($config['authentication_log'],  ' | ' . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN') . ' | ' . __FUNCTION__.':'.__LINE__. ' | userCredentials failed | User: ' . $_REQUEST['username'] . PHP_EOL, true);	
		    return false;
        }
		
	}

   

	// if relying on existing token
	//check if token set
	if ($token = $jwt->verify()) {
       
        $token_array = json_decode(json_encode($jwt->read($token)), true);
        

        if (isset($token_array['username'])){
            $attemptedUserName = $token_array['username'];
        }
        else{
            $attemptedUserName = '';
        }

		if (!$authenticate->userSessionExists()) { 
			log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | userSessionExists failed | User: ' . $attemptedUserName . PHP_EOL, true);
            $authenticate->deleteUserSessions();
            $jwt->delete_token();
			return false;
		}

		if (!$authenticate->userSessionValid()) { 
			log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | userSessionValid failed  | User: ' . $attemptedUserName . PHP_EOL, true);
            $authenticate->deleteUserSessions();
            $jwt->delete_token();
			return false;
		}

		if ($token != $authenticate->getJWT()) { 
			log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | Token (cookie) looks incorrect | User: ' . $attemptedUserName . PHP_EOL, true);
			$authenticate->deleteUserSessions();
			$jwt->delete_token();
			return false;
		}

		if (!$authenticate->isCurrentIpAddress() and $config['useIpAddressBinding']) {# 
            $authenticate->deleteUserSessions();
            $jwt->delete_token();
			return false;
        }

        

        

		return $token;
	} else {
        $jwt->delete_token();
		//log_writer::write($config['authentication_log'],  ' | ' . __FUNCTION__ . ':' . __LINE__ . ' | Token (cookie) failed verification' . PHP_EOL, true);
	}
    //login failed both above
	$authenticate->deleteUserSessions();
	$jwt->delete_token();
	return false;
}




?>