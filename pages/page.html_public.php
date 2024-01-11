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

$this->runtimeInfo = false;

$this->smarty = $smarty = new_smarty();

loadScripts($this);

$this->addHtmlHeader();
$this->addHardComponent ($this->data['action']);
$this->addHtmlFooter();

