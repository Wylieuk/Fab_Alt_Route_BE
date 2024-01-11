<?php

defined("isInSideApplication")?null:die('no access');

/*
* t: sets or updates a user password
***************************************/  


$user = $this->user ?? null;

try{
    if (!empty($user)){
        $user->password = trim($this->data['password']);
        $userId = user::save($user);
    } else {
        throw new Exception('ERROR: Bad token');
    }
}
catch(\Throwable $e)
{
    global $config;
    $this->response['error'] = $e->getMessage() . ' <a nonce="'.$config['CspNonce'].'" href="javascript:history.back()" class="broswer-back">Back</a>'; 
    return $this->response;
}

$this->data['referrer'] = base64_decode($this->data['referrer']);

$this->response =  ['error' => ($userId != $user->id ? 'Error saving user' : false), 'status' => "Your password has been reset, please click <a href='{$this->data['referrer']}' >here</a> to login with your new password"];

