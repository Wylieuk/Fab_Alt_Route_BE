<?php

defined("isInSideApplication")?null:die('no access');


$output = new stdClass;
$output->error = false;

$pagetype = 'json';

try {

    switch (true){

        case isset($this->data['action']) && $this->data['action'] === 'clickedlink' && isset($this->data['token']) && isset($this->data['username']):

            $acceptedParams = assignParams(['token' => null, 'username' => null, 'referrer' => null], $this);
            
            $output->response = $this->setAction('core_validatePasswordResetToken', $acceptedParams)->response;

            $pagetype  = 'html';
            $this->runtimeInfo = false;
            $this->smarty = $smarty = new_smarty();
            $user = false;
            loadScripts($this);
            $this->addHtmlHeader();
            $this->addHardComponent ('reset_password_form', (array)$output->response);
            $this->addHtmlFooter();

            break;


        case isset($this->data['username']) && isset($this->data['email']):

   

            $acceptedParams = assignParams(['username' => null, 'email' => null, 'referrer' => null], $this);
                
            $output->response = $this->setAction('core_sendPasswordResetEmail', $acceptedParams)->response;
            //$this->data['core_sendPasswordResetEmail']->response; 

            break;


        case isset($this->data['action']) && $this->data['action'] === 'execute' && isset($this->data['token']) && isset($this->data['username']):

         

            $acceptedParams = assignParams(['token' => null, 'username' => null, 'password' => null, 'referrer' => null], $this);

            
            $_t = $this->setAction('core_validatePasswordResetToken', $acceptedParams)->response;  
            $response =  $this->setAction('core_saveUserPassword', ['data' => $acceptedParams, 'user' => $_t['user']])->response;
                
            $pagetype  = 'html';
            $this->runtimeInfo = false;
            $this->smarty = new_smarty();
            $user = false;



            loadScripts($this);
            $this->addHtmlHeader();
            $this->addHardComponent('reset_password_form', (array)$response);
            $this->addHtmlFooter(); 

            break;


        default:
            throw new Exception('Invaild instruction');

    }

    
    
    
}
catch(\Throwable $e)
{
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



if ($pagetype !== 'html' || $output->error){

    ob_start('ob_gzhandler');
    headers::accessControlAsRefer();
    headers::allowCredencials();
    headers::json();

    die (json_encode($output, JSON_PRETTY_PRINT));

}

function assignParams($acceptedParams, $page){

    foreach ($acceptedParams as $param => $v){
        if (isset($page->data[$param])){
            $acceptedParams[$param] = $page->data[$param];
        }
        if($acceptedParams[$param] === null){
            throw new Exception($param . ' not set');
        }
    }

    return $acceptedParams;
}
