<?php

#[AllowDynamicProperties]
class twoFactorAuth{

    private $config;
    

    function __construct(array $config){

        $this->config = (object)[
            'enabled'         =>  $config['2faEnabled'], 
            'baseUrl'         =>  $config['2faBaseUrl'],  
            'serviceId'       =>  $config['2faTwilioServiceId'],
            'accountSid'      =>  $config['2faAccountSid'],
            'authToken'       =>  $config['2faAuthToken']  
        ];



    }

    public function isEnabled(){
        return $this->config->enabled;
    }


    /*
    * v:
    * t: create new service for this app (can be pre-created via https://www.twilio.com/console/verify)
    ***************************************/
    public function createSevice($serviceFriendlyName){

        $service = '/Verifications';

        $params = [
            'FriendlyName' => $serviceFriendlyName,
        ];

        $this->sendData($service, $params);

    }
    

    /*
    * t: send token by sms to cell number
    ***************************************/
    public function triggerNewToken($cellNumber){

        $service = '/Verifications';

        $params = [
            'To' => $cellNumber,
            'Channel'=> 'sms'
        ];

        return $this->sendData($service, $params);

    }

     /*
    * t: verify token sent by sms to cell number
    ***************************************/
    public function verifyToken($cellNumber, $token){
        
        $service = '/VerificationCheck';

        $params = [
            'To' => $cellNumber,
            'Code'=> $token
        ];

        return $this->sendData($service, $params);

    }
    

    /*
    * t: universal function to send request to the 2fa provider service
    ***************************************/
    private function sendData($service, $params){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config->baseUrl.'Services/'.$this->config->serviceId.$service);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config->accountSid.":".$this->config->authToken);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
//debug(json_decode($result));
        return $result;
    }


}
