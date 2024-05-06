<?php

#[AllowDynamicProperties]
class emailTwoFactorAuth{

    private $config;
    

    function __construct(array $config){

        $this->config = (object)[
            'enabled'         =>  $config['2faEnabled'],  
            'tokenLifeTime'   => 60*5
        ];

        $this->db = new db;

    }

    public function isEnabled(){
        return $this->config->enabled;
    }


    /*
    * t: send token by email
    ***************************************/
    public function triggerNewToken($user){

        $this->purgeTokens();

        if (!$token = $this->fetchToken($user)){

            $token = [
                'code' => random_int(100000, 999999),
                'expires' => timestamp::db_format(time() + $this->config->tokenLifeTime),
                'user_id' => $user['id']
            ];

            $this->saveToken($user, $token); 
        }

        $this->sendToken($user, $token);


        return (object)[
            'status' => 'pending'
        ];
    }

     /*
    * t: verify token sent by sms to cell number
    ***************************************/
    public function verifyToken($user, $token){

        $this->purgeTokens();

        if($dbToken = $this->fetchToken($user)){

            if (trim($token) == ($dbToken['code'] ?? false)){

                $this->removeUserToken($user);

                return (object)[
                    'status' => 'approved'
                ];
            }
        }

        return (object)[
            'status' => 'not approved'
        ];
    }

    private function fetchToken($user){
        global $config;
        return $this->db->preparedQuery('SELECT * FROM `'.$config['coreTablePrefix'].'two_factor_auth_tokens` WHERE `user_id` = :userId', ['userId' => $user['id']])->fetch_array()[0] ?? null;
    }


    private function saveToken($user, $token){
        global $config;
        $q = $this->db->build_insert(''.$config['coreTablePrefix'].'two_factor_auth_tokens', $token);
        $this->db->preparedQuery($q['statement'], $q['values']);
    }

    private function purgeTokens(){
        global $config;
        $this->db->query('DELETE FROM `'.$config['coreTablePrefix'].'two_factor_auth_tokens` WHERE `expires` < CURRENT_TIMESTAMP()');
    }

    private function removeUserToken($user){
        global $config;
        $this->db->preparedQuery('DELETE FROM `'.$config['coreTablePrefix'].'two_factor_auth_tokens` WHERE `user_id` = :userId', ['userId' => $user['id']]); 
    }

    private function sendToken($user, $token){

        global $config;

        $email = new email('twofa_token'); // will look in template for email.emailtype.tpl
        $email->assignBodyVars('user', $user);
        $email->assignBodyVars('token', $token);
        $email->setAddress('setFrom', 'no_reply@' . str_replace('http://', '', str_replace('https://', '',$_SERVER['HTTP_HOST'])));
        $email->setAddress('addAddress', $user['email']);
        $email->setAttribute('Subject', 'Your two factor authentication code');
        return $email->send();

    }


}
