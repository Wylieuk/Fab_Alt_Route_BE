<?php defined("isInSideApplication")?null:die('no access');


class vendor extends user{

    function __construct($data){
        $this->assign($data);
        $this->group_id = user_group::getIdFromGroupName('vendor');
	}

    function assign($data){
        foreach ($data as $k => $v){
            $this->{$k} = $v;
        }
    }

    function saveVendor(){
        global $config;

        if(empty($this->id) || empty($this->checksum)){
            throw new Exception('Missing checksum');
        }

        if(!$this->checksumValid()){
            throw new Exception('invalid checksum');
        }

        unset($this->checksum);

        $db = new db;
        $query = $db->build_insert("{$config['coreTablePrefix']}users", (array)$this);
        $db->preparedQuery($query['statement'], $query['values']);
        return $db->insert_id();
    }

    function afterLogin(){

		$log = new log([
			'component'    => 'vendor',
			'component_id' => $this->id,
			'details'      => 'user logged in',
			'user_id'       => $this->id
		]);
		$log->save();
        
    }

    function checksumValid(){
        global $config;
        $db = new db;
        return $this->id == current($db->preparedQuery("SELECT `id` FROM `{$config['coreTablePrefix']}users` WHERE `checksum` = :checksum", ['checksum' => $this->checksum])->fetch_array()  ?? []) ['id'] ?? null;
    }

    static function getOwner($id){
        return -1;
    }

    static function get(string $id){
        $userData = (object)self::load($id);
        $userData->id = (int)$id;

        foreach(json_decode($userData->data ?? []) as $k => $v){
            $userData->{$k} = $v;
        }

        unset($userData->enable_2fa);
        unset($userData->group_id);
        unset($userData->enabled);
        unset($userData->last_login);
        unset($userData->user_id);
        unset($userData->password);
        unset($userData->data);

        return new vendor($userData);
    }
    
    static function load(int $id){
        global $config;
        $db = new db;

        $query = "SELECT 
                    * 
                FROM `{$config['coreTablePrefix']}users` u1
                LEFT JOIN `{$config['coreTablePrefix']}users_extended` u2 ON u1.`id` = u2.`user_id`
                WHERE 
                    u1.`id` = :id";

        return current($db->preparedQuery($query, ['id' => $id])->fetch_array() ?? []);
    }

    static function vSave($data){

        $primaryFields = [
            "id",
            "username",
            "email",
            "name",
            "job_title",
            "phone_number",
            "password",
            "checksum",
            "enable_2fa",
            "enabled",
            "last_login"
        ];

        if(!empty($data->id)){
            $existingUser = (object)(user::getUserDetailsById($data->id)[0] ?? null);
        }

        unset($data->password_confirm);

        if(!empty($data->username) && parent::userExists($data->username) && ($existingUser->username ?? null) != $data->username){
            throw new Exception('Sorry that username is already in use');
        }

        if(!empty($data->email) && parent::emailInUseByAnotherId($data->email) && ($existingUser->email ?? null) != $data->email){
            throw new Exception('Sorry that email address is already in use');
        }

        $extendedProperties = (object)[];
        foreach($data as $k => $v){
            $extendedProperties->{$k} = $v;
        }
        foreach($primaryFields as $pField){
            unset($extendedProperties->{$pField});
        }

        $data->group_id = user_group::getIdFromGroupName('vendor');

        if(empty($data->id)){
            $data->enabled  = 0;
        }

        $userId = parent::save($data);

        if(!empty($userId)){
            self::saveExtended($userId, json_encode($extendedProperties));
        } else {
            throw new Exception('Failed to add user');
        }

        return $userId;
    }

    static function sendActivationEmail($userId, $referrer){
        global $config;

        $data = self::load($userId);

        $data['id'] = $userId;

        $email = new email('user_self_activate'); //will look in template for email.emailtype.tpl
        $email->assignBodyVars('user', $data);
        $email->assignBodyVars('config', $config);
        $email->assignBodyVars('referrer', $referrer);
        $email->setAddress('setFrom', $config['from_email'], 'Promo Toolkit');
        $email->setAddress('addAddress', $data['email']);
        $email->setAttribute('Subject', 'Promo Toolkit new account activation');
        $email->send();
    }

    static function update($data){
        return self::vSave($data);
    }

    static function saveExtended($userId, $data){

        $db = new db;
        $query = $db->build_insert('users_extended', ['user_id' => $userId, 'data' => $data]);

        $db->preparedQuery($query['statement'], $query['values']);

    }


    static function getAllPossibleProperties(){
        $__class__ = get_called_class();
        $item = new $__class__([]);

        $db = new db;

        $props = [];

        global $config;

        $query = "SELECT 
                    * 
                FROM `{$config['coreTablePrefix']}users` u1
                LEFT JOIN `{$config['coreTablePrefix']}users_extended` u2 ON u1.`id` = u2.`user_id`
                WHERE 
                    u1.`group_id` != 1";

        foreach ($db->query($query)->fetch_array() ?? [] as $row){

            foreach(json_decode($row['data'] ?? '[]') as $key => $value){
                $props[] = $key;
            }
            unset($row['data']);
            
            foreach($row as $key => $value){
                $props[] = $key;
            }
        }

        $ignoreList = [
            "_table",
            "password",
            "enable_2fa",
            "user_id",
            "checksum",
        ];

        return array_diff(array_unique($props), $ignoreList);

    }
    
    static function getLiveOfferCount($user_id){

        $db = new db;

        return current($db->preparedQuery(
            "SELECT 
                count(o.id) AS 'live_offer_count'
            FROM `attractions` a 
            LEFT JOIN `offers` o ON o.`attraction_id` = a.`id` 
            WHERE 
                a.`vendor_id` = :user_id 
            AND o.`live` = '1'
            "
        , ['user_id' => $user_id]
        )->fetch_array() ?? [])['live_offer_count'] ?? 0;

    }
    
    
}