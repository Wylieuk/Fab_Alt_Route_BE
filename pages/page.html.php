<?php
defined("isInSideApplication")?null:die('no access');

$this->runtimeInfo = false;

$output = (object)[
    'response' => null,
    'error' => false
];

try{


    $this->smarty = new_smarty();

    loadScripts($this);
    $this->addHtmlHeader();
    

    $allowedActions = [
        'get_incident_summary',
        'get_fault_summary',
        'get_stranded_train_report',
        'get_stranded_service_report_group'
    ];

    if (!isset($this->data['action'])) {
        throw new Exception('Action not set');
    }

    


    /*
    * t: execute main action
    ***************************************/
    if (in_array($this->data['action'], $allowedActions)){


        switch ($this->data['action']){

            case 'get_incident_summary':
                $this->addHardComponent ('get_incident_summary', $this->data);
                $this->addHardComponent ('get_log', $this->data);

            break;

            case 'get_fault_summary':
                $this->addHardComponent ('get_fault_summary', $this->data);

            break;

            case 'get_stranded_train_report':
                $this->addHardComponent ('stranded_train_report', $this->data);

            break; 

            case 'get_stranded_service_report_group':
                $this->addHardComponent ('get_stranded_service_report_group', $this->data);

            break; 

            default:
            throw new Exception('Action missing from switch');
        } 

        
    } else {
        throw new Exception('Action not supported');
    }


    /*
    * t: logging all non 'gets'. 
    ***************************************/
    if(isset($this->data['action']) && strPos($this->data['action'], 'get') !== 0){
        $this->setAction('log_action', ['data' => $this->data, 'result' => $output->response]);
    }

}

catch(\Exception $e){
    if ($config['env'] == 'development') {
        trigger_error("{$e->getMessage()} {$e->getFile()}:{$e->getLine()}");
    } 
    else {
        trigger_error($output->error = $e->getMessage());
    }

    if($config['logHandledErrors']){
        log_error("0x".time().' '."{$e->getMessage()} {$e->getFile()}:{$e->getLine()}");
    }

}

$this->addHtmlFooter();