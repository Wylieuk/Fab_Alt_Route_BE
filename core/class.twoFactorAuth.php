<?php

#[AllowDynamicProperties]
class twoFactorAuth{

    public $config;
    

    function __construct(array $config){

        $this->config = (object)[
            'enabled'         =>  $config['2faEnabled'], 
            'channel'         =>  $config['2faChannel']
        ];


        $handler = [
            'email' => 'emailTwoFactorAuth', // use class emailTwoFactorAuth
            'sms' => 'twilioTwoFactorAuth', // use class twilioTwoFactorAuth
        ][$this->config->channel];

        $this->handler = new $handler($config);

    }

    public function isEnabled(){
        return $this->config->enabled;
    }


     /*
    * t: send token to user
    ***************************************/
    public function triggerNewToken($user){
        return $this->handler->triggerNewToken($user);
    }


    /*
    * t: verify token sent by sms to cell number
    ***************************************/
    public function verifyToken($user, string $token){
        return $this->handler->verifyToken($user, $token);
    }


}