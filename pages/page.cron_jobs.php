<?php
defined("isInSideApplication")?null:die('no access');

$output = (object)[
    'response' => null,
    'error' => false
];

try{

    $allowedActions = [
      'job_refresh_components',
      'job_import_fault_mail',
      'job_refresh_incidents',
      'job_retire_out_of_date_offers'
    ];

    if (!isset($this->data['action'])) {
        throw new Exception('Action not set');
    }

    if (in_array($this->data['action'], $allowedActions)){
        $output->response = $this->setAction($this->data['action'], ['data' => $this->data])->response;
    } else {
        throw new Exception('Action not supported');
    }

}

catch(\Exception $e){
    if ($config['env'] == 'development') {
        $output->error = "{$e->getMessage()} {$e->getFile()}:{$e->getLine()}";
    } 
    else {
        $output->error = $e->getMessage();
    }

    if($config['logHandledErrors']){
        log_error("0x".time().' '."{$e->getMessage()} {$e->getFile()}:{$e->getLine()}");
    }

}

/* 
$output->token = data::createToken($output->response);

if (!data::modified($this->data['token'] ?? null, $output->token)){
    headers::accessControlAsRefer();
    headers::allowCredencials();
    //header('HTTP/1.1 304 Not Modified');
    header('Content-Type: application/json');
    die(json_encode(["applicationNotModified" => 1]));
    exit();
}
*/



ob_start('ob_gzhandler');
headers::accessControlAsRefer();
headers::allowCredencials();
headers::json();

die (json_encode($output, JSON_PRETTY_PRINT));

