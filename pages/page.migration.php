<?php
defined("isInSideApplication")?null:die('no access');
$output = (object)[
    'response' => null,
    'error' => false
];


try{

    $allowedActions = array_keys($config['group_access']);

    if (!isset($this->data['action'])) {
        throw new Exception('Action not set');
    }

    /*
    * t: execute any preprocess
    ***************************************/
    switch (true){
        default:
    }

    /*
    * t: execute main action
    ***************************************/
    if (in_array($this->data['action'], $allowedActions)){

        
        // if(!isset($config['group_access'][$this->data['action']])) {
        //     throw new Exception("Action {$this->data['action']} access config not set");
        // }

        // global $user;
        // print_r($user);

        // if (!user::hasAccess( $config['group_access'][$this->data['action']] )){
        //     throw new Exception('Not Authorised');
        // }
        
        $output->response = $this->setAction($this->data['action'], ['data' => $this->data, 'dirtyData' => $this->dirtyData])->response;
        
        
    } else {
        throw new Exception('Action not supported');
    }
    
}

catch(\Exception $e){
    if ($config['env'] == 'development') {
        $output->error = "{$e->getMessage()} {$e->getFile()}:{$e->getLine()}:{$e->getTraceAsString()}";
    } 
    else {
        $output->error = $e->getMessage();
    }

    if($config['logHandledErrors']){
        log_error("0x".time().' '."{$e->getMessage()} {$e->getFile()}:{$e->getLine()}");
    }

}

//limit output to what is requested
$output->response = data::limitStructure($output->response ?? null, $this->data['responseStructure'] ?? null) ?? $output->response ?? null;

//add and check hash token to see if data changed, if not then serve 304 Not Modified
if (!data::modified($this->data['token'] ?? null, $output->token = (data::createToken($output->response ?? null)))){
    headers::accessControlAsRefer();
    headers::allowCredencials();
    header('Content-Type: application/json');
    die(json_encode(["applicationNotModified" => 1]));
}


//ob_start('ob_gzhandler');
headers::accessControlAsRefer();
headers::allowCredencials();
headers::json();

die (json_encode($output, JSON_PRETTY_PRINT));