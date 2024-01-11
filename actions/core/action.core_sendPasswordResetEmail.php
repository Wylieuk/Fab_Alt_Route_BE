<?php
defined("isInSideApplication")?null:die('no access');

/*
* t: Sends a 'reset password email' to a user
***************************************/  

if (isset($this->email) && isset($this->username)){
    // check username and email combo exits.
    $user = new user;
    if ($user->loadExisting($this->username)){
        if (trim($this->email) === trim($user->email)){
            $result = $user->sendResetPasswordEmail($this->referrer);
        }
    };

    
}


$this->response = 'success'; // reply success no matter what as dont want to give away anything to hackers