<?php defined("isInSideApplication")?null:die('no access');

global $config;
$output = (object)[
    'response' => null,
    'error' => false
];


try {

    if(isset($this->data['message']) &&  isset($this->data['file']) && isset($this->data['line'])){
        log_error(date('y-m-d H:i') . ' Error: ' . $this->data['message'] . ' | File: '. $this->data['file'] . ' | Line: ' . $this->data['line'], $config['error_log_frontend']);
        $output->response = 'success';
    }

    else {
        throw new Exception('Incomplete log passed');
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

//limit output to what is requested
$output->response = data::limitStructure($output->response ?? null, $this->data['responseStructure'] ?? null) ?? $output->response ?? null;

//add and check hash token to see if data changed, if not then serve 304 Not Modified
if (!data::modified($this->data['token'] ?? null, $output->token = (data::createToken($output->response ?? null)))){
    headers::accessControlAsRefer();
    headers::allowCredencials();
    //header('HTTP/1.1 304 Not Modified');
    header('Content-Type: application/json');
    die(json_encode(["applicationNotModified" => 1]));
}



ob_start('ob_gzhandler');
headers::accessControlAsRefer();
headers::allowCredencials();
headers::json();

die (json_encode($output, JSON_PRETTY_PRINT));