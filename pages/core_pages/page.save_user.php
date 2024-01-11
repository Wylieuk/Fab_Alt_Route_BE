<?php

defined("isInSideApplication")?null:die('no access');



try {

    $enc = new encryption;
    $u = json_decode($enc->cryptJsDecrypt($this->data['user'], VARIABLE_CYPHER_KEY));

    if (user::hasAccess(array('admin')) || user::isSelf($u->id)) {


       // all good
    } else {
        headers::permissionError();
    }

    if(empty($u->id) && user::usernameInUseByAnotherId($u->username)){
        throw new Exception('Sorry this username is already in use');
    }    
    
    if(empty($u->id) && user::emailInUseByAnotherId($u->email)){
        throw new Exception('Sorry this email address is already in use');
    }

    /*
    * t: unset all items that non-admins cannot alter.
    ***************************************/
    if(!user::hasAccess(array('admin'))){ //user is not an admin
        foreach($u as $key => $val){
            $allowedSelfSave = ['id', 'email', 'name', 'job_title','password', 'phone_number', 'extended_attributes'];
            if (!in_array($key, $allowedSelfSave)){
                unset($u->{$key});
            }
        }
    }


    if(!empty($u->extended_attributes)){
        $u->extended_attributes = json_encode($u->extended_attributes);
    } else {
        unset($u->extended_attributes);
    }

    $insertId = user::save($u, true, $this->data['captchaToken'] ?? 'xxx');

    if ($insertId > 0){
        //$output['response']['userId'] = $insertId;
        $output['response'] = 'Successfully Updated user';
    }
}

catch(\Throwable $e)
{
    
    
    if ($config['env'] == 'development'){
        $output['error'] = $e->getMessage().'F:'.$e->getFile().'L:'.$e->getLine();
    } else {
        $output['error'] = $e->getMessage();
    }
}

ob_start('ob_gzhandler');

if (isset($_SERVER['HTTP_REFERER']) &&  $_SERVER['HTTP_REFERER'] != null) {
    $origin = trim($_SERVER['HTTP_REFERER'], '/');
    header("Access-Control-Allow-Origin: $origin");
} else {
    //trigger_error('ORIGIN NOT SET');
}
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
header('Content-Encoding: gzip');
echo (json_encode($output, JSON_PRETTY_PRINT));
exit;