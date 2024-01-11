<?php defined("isInSideApplication")?null:die('no access');


class toc extends user{

    public $primaryFields = [
        "username",
        "email",
        "name",
        "job_title",
        "phone_number",
        "password",
    ];
    
    static function get(string $id){
        $userData = (object)self::load($id);
        $userData->id = (int)$id;

        foreach(json_decode($userData->data ?? '[]') as $k => $v){
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

        unset($data->password_confim);

        if(parent::userExists($data->username)){
            throw new Exception('Sorry that username is already in use');
        }

        if(parent::emailInUseByAnotherId($data->email)){
            throw new Exception('Sorry that email address is already in use');
        }

        $extendedProperties = (object)[];
        foreach($data as $k => $v){
            $extendedProperties[$k] = $v;
        }

        $data->group_id = user_group::getIdFromGroupName('toc');
        $data->enabled  = 0;
        $userId         = parent::save($data);

        self::saveExtended($userId, json_encode($extendedProperties));

        return $userId;
    }

    static function update($data){

        if(parent::userExists($data->username)){
            throw new Exception('Sorry that username is already in use');
        }

        if(parent::emailInUseByAnotherId($data->email)){
            throw new Exception('Sorry that email address is already in use');
        }

        $data->group_id = user_group::getIdFromGroupName('toc');
        return parent::save($data);
    }

    static function saveExtended($userId, $data){
        $db = new db;
        $query = $db->build_insert('users_extended', ['user_id' => $userId, 'data' => $data]);

        $db->preparedQuery($query['statement'], $query['values']);

    }
    
}