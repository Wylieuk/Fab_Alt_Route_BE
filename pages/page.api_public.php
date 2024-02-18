<?php
defined("isInSideApplication")?null:die('no access');
$output = (object)[
    'response' => null,
    'error' => false
];


try{

    $allowedActions = [
        'set_new_user',
        'set_activate_user_self',
        'get_station'
    ];

    if (!isset($this->data['action'])) {
        throw new Exception('Action not set');
    }

    /*
    * t: execute any preprocess
    ***************************************/
    switch (true){
        
        // case $this->data['action'] == 'get_location':
        //     $this->data['kb_station'] = $this->setAction('get_kb_station', ['data' => $this->data])->response;
        //     break;
        // default:

    }

    /*
    * t: execute main action
    ***************************************/
    if (in_array($this->data['action'], $allowedActions)){       
        $output->response = $this->setAction($this->data['action'], ['data' => $this->data, 'dirtyData' => $this->dirtyData])->response;
    } else {
        throw new Exception('Action not supported');
    }

    /*
    * t: logging all non 'gets'. 
    ***************************************/
    // if(strPos($this->data['action'], 'get') !== 0 && strPos($this->data['action'], 'mail') !== 0){
    //     $this->setAction('set_log_action', ['data' => $this->data, 'dirtyData' => $this->dirtyData, 'result' => $output->response]);
    // }
    
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
    exit();
}


//ob_start('ob_gzhandler');
headers::accessControlAsRefer();
headers::allowCredencials();
headers::json();

die (json_encode($output, JSON_PRETTY_PRINT));