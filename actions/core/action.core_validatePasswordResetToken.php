<?php

defined("isInSideApplication")?null:die('no access');

$maxLifetime = 2020;// minutes

$user = new user;

try {
    $token = $user->decryptPasswordResetToken($this->username, $this->token);
}

catch(Throwable $e){
    return ['error' => 'ERROR: Bad token'];
}


if (!isset($token->username) || $this->username !== $token->username){
    return ['error' => 'ERROR: Invalid token'];
}


if(!isset($token->timestamp)){

    return ['error' => 'ERROR: Bad token'];
}


if (floor((time() - $token->timestamp) / 60) > $maxLifetime){

    return ['error' => 'Sorry the Link has expired'];
}

return ['error' => false, 'user' => $user, 'token' => $this->token, 'referrer' => $this->referrer];