<?php defined("isInSideApplication")?null:die('no access');


class non_fatal_exception extends Exception{

    function __construct($message, $code=0, Throwable $previous=null){
        global $config;

        $_show_errors           = $config['show_errors'];
        $_stopOnError           = $config['stopOnError'];

        $config['show_errors']  = false;
        $config['stopOnError']  = false;

        trigger_error($message);

        $config['show_errors']  = $_show_errors;
        $config['stopOnError']  = $_stopOnError;

        parent::__construct($message, $code, $previous);

    }



}