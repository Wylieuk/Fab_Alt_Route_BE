<?php
defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class authenticate{
	
	private $passed					= NULL;
	private $looked_up				= NULL;
		
	function __construct(){
		$this->db = new db;
		$this->allowSessionRefresh = true;
		if(isset($_REQUEST['refreshSession'])){
			if($_REQUEST['refreshSession'] == 0){
				$this->allowSessionRefresh = false;
			}
		}
	}
	
	private function setPassedCredentials($username=NULL, $password=NULL){
		$this->passed['username']			=	strtolower(trim($username));
		$this->passed['password']			=	$password;
		
	}
		
	public function checkCredentials ($username=NULL, $password=NULL, $twoFactorAuthToken=null){

		global $config;
		global $event;
		$this->setPassedCredentials($username,$password);

        $loginResult = false;
		
		switch ($config['auth_type']){
		
		case 'localFile':
			$authLocalFile = new authLocalFile;
			if(!$this->xauth = $authLocalFile->isAuthenticated($username,$password)){
				//$event->trigger('afterLoginFailed');
				return false;
			}	
			$loginResult = true;	
		break;
		
		case 'ldap':
			$ldap = new ldap;
			if(!$this->xauth = $ldap->isAuthenticated($username,$password)){
				//$event->trigger('afterLoginFailed');
				return false;
			}	
			$loginResult = true;	
		break;
		
		case 'xauth':
			$xauth = new xauth;
			if(!$this->xauth = $xauth->isAuthenticated($username,$password)){
				//$event->trigger('afterLoginFailed');
				return false;
			}	
			$loginResult = true;		
		break;
		
        case 'db':

            
        
			if (!$this->lookupUser($this->passed['username'])){
                
				//$event->trigger('afterLoginFailed');
				return false;
			}
		


            if ($this->passed['username'] == strtolower(trim($this->looked_up['username'])) and password_verify($this->passed['password'], $this->looked_up['password']) and $this->looked_up['enabled']){
					//debug($this->looked_up);
					global $event;
                    $userPassed = $this->passed;
					$event->add('afterLoginSuccess',function()use($userPassed){
                        user::logAccess($userPassed);
                        user::setLastLoginTime($this->looked_up['id']);
					});
					$event->trigger('afterLoginSuccess');

                    $loginResult = $this->checkTwoFactorAuthToken($this->looked_up, $twoFactorAuthToken);

				}
		break;
		
		default:
			trigger_error('No reconised auth type set in $config[\'auth_type\']');	
		}

        if ($loginResult === true){
			
            return $loginResult;
        } else {
            $event->trigger('afterLoginFailed');
            return false;
        }
	}

    private function checkTwoFactorAuthToken($user, $twoFactorAuthCode){

        
        global $config;

        if (!$config['2faEnabled'] || !$user['enable_2fa']){
            return true;
        }


        //debug($twoFactorAuthCode);exit;

        $tfa = new twoFactorAuth($config);

        if($twoFactorAuthCode === null || $twoFactorAuthCode === 'null'){


            if (!isset($user['phone_number']) || (strpos($user['phone_number'], '+') !== 0) || (strlen($user['phone_number']) < 12)){
                headers::accessControlAsRefer();
                headers::json();
                die(json_encode(['error' => 'There is a problem with the users stored phone number, unable to send sms. Please constact support.']));
            }

            $reply = $tfa->triggerNewToken(trim($user['phone_number']));

            if ( $reply->status == 'pending'){

                headers::accessControlAsRefer();
                headers::json();
                die(json_encode(['error' => '2fa_code_required']));

            } else {

                headers::accessControlAsRefer();
                headers::json();
                die(json_encode(['error' => 'Error: Communicating with 2 factor auth service']));

            }

        } else {

            $reply = $tfa->verifyToken(trim($user['phone_number']), trim($twoFactorAuthCode));

            if (isset($reply->status) && $reply->status === 'approved'){

                return true;

            } else if (isset($reply->status) && $reply->status !== 'approved' ){

                headers::accessControlAsRefer();
                headers::json();
                die(json_encode(['error' => 'Two-Factor-Authenication code incorrect']));

            } else {

                return false;
                trigger_error('Error: bad response from 2FA service' . json_encode($reply ));

            }

        }
    }
		
 	function createUserSession($username=NULL){
		global $config;
		$user = new user;
		//$this->saveNewUserSession(array('sessionId' => session_id()));
		return $this->userSessionExists($username);
	}
	
	public function deleteExpiredSessions(){
		global $config;
		$this->db = new db;
		$query = 'DELETE FROM `'.$config['coreTablePrefix'].'user_sessions` WHERE `timestamp` < "'.timestamp::db_format(time() - $config['JSON_WEB_TOKEN_EXPIRATION']).'"';
		$this->db->query($query);
	}
	
	public function userSessionValid(){ 
		global $config;
		if ((strtotime($this->looked_up['timestamp'])+$config['JSON_WEB_TOKEN_EXPIRATION']) > time()){
			$this->refreshSession();
			return true;
		}
		return false;
	}
	
	private function refreshSession(){
		if ($this->allowSessionRefresh){
			global $config;
			$this->db = new db;
			$data['timestamp'] = $this->db->database_time_format(time());
			$query = $this->db->build_update($config['coreTablePrefix'].'user_sessions', $data, 'WHERE `sessionId` LIKE "'.$this->looked_up['session_id'].'"' );
			$this->db->query($query);
		}
	}
	
	function deleteUserSessions(){
		global $config;
		$this->db = new db;
		$query = 'DELETE FROM `'.$config['coreTablePrefix'].'user_sessions` WHERE `sessionId` LIKE "'.session_id().'"';
		$this->db->query($query);
	}
	
	
	function saveNewUserSession($data){
		global $config;
		$data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$this->deleteUserSessions();
		$this->db = new db;
		$query    = $this->db->build_insert($config['coreTablePrefix'].'user_sessions', $data);
		$this->db->preparedQuery($query['statement'], $query['values']);
	}

	public function isCurrentIpAddress(){
		return $this->ip_address == $_SERVER['REMOTE_ADDR'];
	}
	
	public function userSessionExists(){
		global $config;
		$user = new user;
		if ($this->loadExistingSession()){
			$this->looked_up['iss']                            = 'HWA';
			$this->looked_up['db_id']                          = $this->id;
			$this->looked_up['ip_address']                     = $this->ip_address;
			$this->looked_up['username']                       = $this->username;
			$this->looked_up['session_id']                     = $this->sessionId;
			if (isset($this->xauth)){$this->looked_up['xauth'] = $this->xauth;}
			$this->looked_up['timestamp']                      = $this->timestamp;
			$this->lookupJWT();
			return true;
		}
		return false;
	}
	
	
	public function lookUpUser ($username=NULL){

		global $config;
		$user = new user;
		
		if ($user->loadExisting($username)){
			//debug($user);
			
			foreach ($user as $userPropertyKey => $userPropertyValue){
				$this->looked_up[$userPropertyKey] = $userPropertyValue;				
			}
			$this->looked_up['iss']					=			$config['siteaddress'];
			if ($config['auth_type'] == 'xauth'){
				$this->looked_up['xauth']			=			$this->xauth;
			}
			if ($config['auth_type'] == 'xauth'){
				$this->looked_up['xauth']			=			$this->xauth;
			}
			//$this->looked_up['prefs']				=			$user->loadUserPrefs($user->id);
			//$this->lookupJWT($username);
			

			return true;
		}

		return false;
	}
	
	public function getJWT(){
		return $this->looked_up['JWT'];
	}
	
	public function lookupJWT (){
		global $config;
		$user = new user;
		if ($this->JWT !=''){
			$this->looked_up['JWT']	=	$this->JWT;
		}
	}
	

	public function userDetails(){
		global $config;
		unset ($this->looked_up['password']);
		unset ($this->passed);
        
        if(isset($this->xauth)){
            $this->looked_up['xauthData'] = $this->xauth;
        }    
        
		return $this->looked_up;
	}
	
	
	function loadExistingSession(){
		global $config;
		$this->db = new db;
		$query = 'SELECT *  FROM `'.$config['coreTablePrefix'].'user_sessions` WHERE `sessionId` = "'.session_id().'"';
		$this->db->query($query);
		$db_result = $this->db->fetch_array();

        //debug($db_result);exit;
		if (is_array($db_result)){
			foreach ($db_result[0] as $key => $value){
				$this->$key = $value;
			}
			return true;
		}
		return false;
	}
	
	
		
	
	
	
}