<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class user {
	
	function __construct(){
	}
	
	#user::loadAllUsers(1); //group 1, user::loadAllUsers(); //all groups
	static function loadAllUsers($limitToGroup='%'){
        global $config;
		$db = new db;
		$query = "SELECT 
			A.*,
			E.`data` AS extended, 
			CASE
				WHEN EXISTS (SELECT *
					FROM `{$config['coreTablePrefix']}user_sessions` B
					WHERE B.username = A.username)
				THEN 1
				ELSE 0
			END as `status`
		FROM `{$config['coreTablePrefix']}users` A
		LEFT JOIN `{$config['coreTablePrefix']}users_extended` E ON E.`user_id` = A.`id`
		WHERE A.`group_id` 
		LIKE :limitToGroup 
		ORDER BY username ASC";


		$results =  $db->preparedQuery($query, ['limitToGroup' => $limitToGroup])->fetch_array() ?? [];
		
		//drop passwords
		foreach ($results as $key => &$user){
			$user['extended'] = json_decode($user['extended'] ?? '{}');
			unset($results[$key]['password']);
			unset($results[$key]['checksum']);
		}
		
		return $results;
		
	}

	function afterLogin(){


		if(class_exists(user_group::getGroupName($this->group_id))){
			$reflectionClass = new ReflectionClass(user_group::getGroupName($this->group_id));
			if ($reflectionClass->getMethod(__FUNCTION__)->class == user_group::getGroupName($this->group_id)) {
				$__class__ = new (user_group::getGroupName($this->group_id))((array)$this);
				$__class__->{__FUNCTION__}();
				return;				
			}
		}

		return;
	}

	
	function loadUserPrefs(){
		global $default;
		
		//debug(strlen($this->prefs));
		
		if (strlen($this->prefs) < 1){$this->prefs = $default['user_prefs'];}	
		$this->prefs = json_decode($this->prefs);
		return $this->prefs;
	}
	
	static function userExists($username){
		global $config;
		$db = new db;
		$query = 'SELECT * FROM `'.$config['coreTablePrefix'].'users` WHERE `username` = :username';
		$db->preparedQuery($query, array('username'=>$username));
		return $db->fetch_array();
	}
	
	static function usernameInUseByAnotherId($username, $id=''){
		global $config;
		$db = new db;
		$query = 'SELECT * FROM `'.$config['coreTablePrefix'].'users` WHERE `id` != :id AND`username` = :username';
		$db->preparedQuery($query, array('id'=>$id, 'username'=>$username));
		return $db->fetch_array();
	}

	static function emailInUseByAnotherId($email, $id=''){
		global $config;
		$db = new db;
		$query = 'SELECT * FROM `'.$config['coreTablePrefix'].'users` WHERE `id` != :id AND `email` = :email';
		$db->preparedQuery($query, array('email'=>$email, 'id'=>$id));
		return $db->fetch_array();
	}
		
	static function getId($username){
		global $config;
		$db = new db;
		$query = 'SELECT * FROM `'.$config['coreTablePrefix'].'users` WHERE `username` = :username';
		$db->preparedQuery($query. array('username'=> $username));
		$result =  $db->fetch_array();
		return $result[0]['id'];
	}

	static function getOwner($id){
        return -1;
    }

    static function save($data, $reHashPassword = true){

		global $config;

        if (isset($data->username) && $data->username == ''){
            throw new \Exception('You must input a username.');
        }

        if (isset($data->group_id) && ( $data->group_id == '' || $data->group_id == 0 )){
            throw new \Exception('You must choose a group.');
        }
        
        // validate password
        if (isset($data->password)){
            $uppercase    = preg_match('@[A-Z]@', $data->password);
            $lowercase    = preg_match('@[a-z]@', $data->password);
            $number       = preg_match('@[0-9]@', $data->password);
            $specialChars = preg_match('@[^\w]@', $data->password);

            global $config;
            if($config['validatePasswordStrength'] && (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($data->password) < 8)){
                throw new \Exception('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.');
            }

            if ($reHashPassword) {
                $data->password = password_hash($data->password, PASSWORD_DEFAULT);
            }

        } else {
            //is there a user to update already
            if (isset($data->username) && !self::userExists($data->username)){
                throw new \Exception('You must input a password when creating a new account.');
            }
        }

        if (isset($data->enable_2fa) && $data->enable_2fa && (!isset($data->phone_number) || (strpos($data->phone_number, '+') !== 0) || (strlen($data->phone_number) < 12))){
            throw new \Exception('To enable two factor auth, you must input a cell phone number in international format [+44].');
        }

        unset($data->last_login);
		unset($data->checksum);

        $db = new db;
        $query = $db->build_insert($config['coreTablePrefix'].'users', (array) $data);
        $db->preparedQuery($query['statement'], $query['values']);
        $userId = $db->insert_id();


		$extendedData = [
			'data' => json_encode($data->extended ?? (object)[]),
			'user_id' => $userId
		];

		$query = $db->build_insert($config['coreTablePrefix'].'users_extended', (array)$extendedData);
		$db->preparedQuery($query['statement'], $query['values']);

		global $user;

		if(class_exists('log')){
			if(!empty($data->id)){

				$log = new log([
					'component'    => 'user',
					'component_id' => $data->id,
					'details'      => 'user updated',
					'user_id'       => $user->id ?? $data->id
				]);
				$log->save();
				}

				else {
					$log = new log([
						'component'    => 'user',
						'component_id' => $userId,
						'details'      => 'user created',
						'user_id'      => $userId
					]);
					$log->save();
			}
		}

        return $userId;
    }
	
	static function addUser($username, $password, $groupMembership){

        $uppercase    = preg_match('@[A-Z]@', $password);
        $lowercase    = preg_match('@[a-z]@', $password);
        $number       = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        global $config;
        if($config['validatePasswordStrength'] && (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8)){
            throw new \Exception('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.');
        }


		$data['username'] 		    = $username;
		$data['password'] 		    = password_hash($password, PASSWORD_DEFAULT);
		$data['group_membership'] 	= $groupMembership;
		$db = new db;
		$query = $db->build_insert($config['coreTablePrefix'].'users', $data);
		$db->preparedQuery($query['statement'], $query['values']);
		$userId = $db->insert_id();
		return $userId;
	}
	
	static function deleteUser($id){
		global $config;
		$db = new db;
		$query = 'DELETE FROM `'.$config['coreTablePrefix'].'users` WHERE `id`= :id';
		$db->preparedQuery($query, array('id'=>$id));
		if($db->affected_rows() < 1){return false;}
		return true;
	}
	
	static function saveUserPrefs($prefs, $user_id){
		global $config;
		$db = new db;
		$data = array('prefs' => $prefs);
		$query = $db->build_update($config['coreTablePrefix'].'user_sessions', $data, 'WHERE `id` LIKE :user_id');
		$db->preparedQuery($query, ['user_id' => $user_id]);
		return true;
	}
	
	function loadExisting($username){
		global $config;
		$db = new db;
		$query = 'SELECT *  FROM `'.$config['coreTablePrefix'].'users` WHERE `username` = :username';
		$db->preparedQuery($query, array('username'=>$username));
		$db_result = $db->fetch_array();
		if (is_array($db_result)){
			foreach ($db_result[0] as $key => $value){
				$this->$key = $value;
			}
			return true;
		}
		return false;
	}	
	
	static function getByUsername($username){
		$user = new user;
		if($user->loadExisting($username)){
			return $user;
		}
		return null;
	}
	

	function loadUserById($id){
		global $config;
		$db = new db;
		$query = 'SELECT *  FROM `user_sessions` WHERE `id` = :id';
		$db->preparedQuery($query, array('id'=>$id));
		$db_result = $db->fetch_array();
		
		if (is_array($db_result)){
			return $db_result[0];
		}
		return false;
	}
	
	static function getUserDetailsById($id){
		global $config;
		$db = new db;
		$query = 'SELECT *  FROM `'.$config['coreTablePrefix'].'users` WHERE `id` = :id';
		$db->preparedQuery($query, array('id'=>$id));
		$db_result = $db->fetch_array();
		if (is_array($db_result)){
			return $db_result;
		}
		return false;
	}
	
	static  function setLive($id){
		global $config;
		$db = new db;
		$query = $db->build_update('users', array('enabled'=>'1'), ' WHERE `id` = :id');
		$db->preparedQuery($query, ['id' => $id]);
		if ($db->affected_rows() != 1){
			return false;
		}
			return true;
	}
	
	static function logAccess($user){
		global $config;
		if (!$config['user_access_log']){return;}
		$userObj = new user;
		if($userObj->loadExisting($user['username'])){
			//debug($userObj);
			log_writer::logToDB($config['coreTablePrefix'] . 'user_access_log', $userObj, (string)$config['user_access_log_retention']*24*60*60);
		}
	}
	
	static function getName($id){
		global $config;
		$db = new db;
		$query = 'SELECT *  FROM `user_sessions` WHERE `id` = :id';
		$db->preparedQuery($query, array('id'=>$id));
		$db_result = $db->fetch_array();
		return $db_result[0]['first_name']. ' '.$db_result[0]['last_name'];		
	}
	
	
	static function getBlankUser(){
		global $company;
		//$form = new fields;
		$fields_to_skip = array('id', 'JWT', 'timestamp', 'prefs', 'last_login');
		$fields = form::get_fields('users', $fields_to_skip);		
		return $fields;
	}
	
	static function hasAccess($arrayOfGroupNames, $userData=null){

	
        global $user;

		//print_r([$user,$arrayOfGroupNames]);

		if (in_array($user->group_id ?? 'xxxxxx', $arrayOfGroupNames)){
			return true;
		}

		$arrayOfGroupIds = self::getArrayOfUserGroupIds($arrayOfGroupNames);


		if(!isset($user->group_id)){return false;}

		if (empty($userData)){
            //use logged on user data
			$group_id = $user->group_id;
		}
		else{
            //use parsed user data
			$group_id = $userData->group_id;
		}

		//print_r([$group_id, $arrayOfGroupIds]);
		
		if (in_array($group_id, $arrayOfGroupIds)){
			return true;
		}

		return false;
		
		
	}
	
	static function isSelf($userId){
		global $user;
		return ($user->id == $userId );
	}
	
	static function getArrayOfUserGroupIds($arrayOfGroupNames){
		global $config;
		$arrayOfGroupNames = array_combine($arrayOfGroupNames, $arrayOfGroupNames);
		$db = new db;
		$db->preparedQuery( 'SELECT * FROM `'.$config['coreTablePrefix'].'user_groups` WHERE `group_name` IN(:'.implode(', :',array_keys($arrayOfGroupNames)).')', $arrayOfGroupNames);
		$result = $db->fetch_array();
		foreach($result as $row){
			$arrayOfGroupIds[] = $row['id'];
		}
		return $arrayOfGroupIds;
	}
	
	
	function loggedOnUser($jwt_token){
		global $config;
		$jwt = new jwt($config['JSON_WEB_TOKEN_KEY']);
		$JSON_WEB_TOKEN = $jwt->read($jwt_token);
		$this->JWTData = $JSON_WEB_TOKEN;

		if (isset($JSON_WEB_TOKEN->xauthData)){
			return (object)$JSON_WEB_TOKEN->xauthData;
		}
		if (isset($JSON_WEB_TOKEN->apiData)){
			return (object)$JSON_WEB_TOKEN->apiData;
        }
        return $JSON_WEB_TOKEN;
	}	
	
	static function passwordChanged($id, $password){
		 $user_data = self::getUserDetailsById($id);
		 if($password != $user_data[0]['password']){
			return true;			 
		 }
		 return false;
	}
	
	static function setLastLoginTime($user_id){
		global $config;
		$data = array('id'=>$user_id, 'last_login' => db::database_time_format(time()));
		$db = new db;
		$query = $db->build_insert($config['coreTablePrefix'].'users', $data);
		$db->preparedQuery($query['statement'], $query['values']);
		
	}

    function decryptPasswordResetToken($username, $token){   
        $this->loadExisting($username);
        return json_decode(encryption::decrypt($token, $this->password));
    }

    function sendResetPasswordEmail($referrer = ''){
        
        global $config;

        $token = encryption::encrypt(json_encode((object)[
            'timestamp' => time(),
            'username' => $this->username
        ]), $this->password);

        $this->resetLink = $config['siteaddress'].'/index.php?page=reset_password&username=' . $this->username . '&action=clickedlink&token=' . $token .'&referrer=' . $referrer;

        $email = new email('password_reset'); // will look in template for email.emailtype.tpl
        $email->assignBodyVars('user', $this );
        $email->setAddress('setFrom', $config['from_email']);
        $email->setAddress('addAddress', $this->email);
        $email->setAttribute('Subject', 'Password reset request ' . str_replace('http://', '', str_replace('https://', '',$config['siteaddress'])));
        return $email->send();
    }

    static function getGroupMembers($groupId){
		global $config;
        $db = new db;
        $query = 'SELECT * FROM `'.$config['coreTablePrefix'].'users` WHERE `group_id` = :groupId';

        $result = $db->preparedQuery($query, ['groupId' => $groupId])->fetch_array() ?? [];

        foreach ($result as $k => &$v){
            $v = (object)$v;
            unset($v->password);
        }

        return $result;

    }

	static function logOutUser($username) {
		global $config;
		$db = new db;
		$query = 'DELETE FROM `'.$config['coreTablePrefix'].'user_sessions` WHERE `username` = :username';
		$db->preparedQuery($query, array('username'=>$username));
	}

	static function getAll($searchParams=[]){

        global $config;

        $__class__      = get_called_class();
        $searchSql      = '';


        $allowedSearch = [
			"live"		   => "u1.`id` = (SELECT 
									a.`vendor_id` 
									FROM `attractions` a 
									INNER JOIN `offers` AS o ON a.`id` = o.`attraction_id` AND o.`live` = :live
									WHERE a.`vendor_id` = u1.`id` 
									LIMIT 1
								)",
            "id"           => "u1.`id` = :id",
            "username"     => "u1.`username` = :username",
            "email"        => "u1.`email` = :email",
            "name"         => "u1.`name` = :name",
            "phone_number" => "u1.`phone_number` = :phone_number",
            "enabled"      => "u1.`enabled` = :enabled",
            "group_id"     => "u1.`group_id` = :group_id",
            "group_name"   => "g1.`group_name` = :group_name"
        ];

        

        $searchSql = 
             implode("\n AND ", array_intersect_key($allowedSearch, $searchParams));

        $searchParams = 
            array_intersect_key($searchParams, $allowedSearch);

        if(!empty($searchSql)){
            $searchSql = 'WHERE '. $searchSql;
        }

        $db = new db;

        $query = 
           "SELECT
                u1.*,
                u2.`data`,
                g1.`group_name`
            FROM `{$config['coreTablePrefix']}users` u1
            LEFT JOIN `{$config['coreTablePrefix']}users_extended` u2 ON u1.`id` = u2.`user_id`
            LEFT JOIN `{$config['coreTablePrefix']}user_groups` g1 ON g1.`id` = u1.`group_id`
            {$searchSql}
        ";

        $results = $db->preparedQuery($query, $searchParams)->fetch_array() ?? [];

        $ignore  = ['password', 'enable_2fa', 'checksum', 'group_id'];


        foreach($results as &$row){

			if($row['last_login'] == '0000-00-00 00:00:00'){
				$row['last_login'] = null;
			}

            foreach ((json_decode($row['data'] ?? '[]')) as $ePropKey => $ePropValue){
                $row[$ePropKey] = $ePropValue;
            }

            foreach($ignore as $ign){
                unset($row[$ign]);
            }

            unset($row['data']);
        }

        return $results;

    }

	static function sendAlert($user){

		global $config;

		$email = new email('set_new_user'); //will look in template for email.emailtype.tpl
        $email->assignBodyVars('user', $user);
        $email->setAddress('setFrom', $config['from_email'], 'Promo Toolkit');
        $email->setAddress('addAddress', $config['email_alert_target']);
        $email->setAttribute('Subject', 'Promo Toolkit new user');
        $email->send();

	}
}
?>