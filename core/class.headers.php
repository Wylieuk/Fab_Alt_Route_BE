<?php

defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class headers{

    static function accessControlAsRefer(){
        global $config;

        if ($config['allow_cors'] || !isset($config['origin'])){
            if (isset($_SERVER['HTTP_REFERER']) &&  $_SERVER['HTTP_REFERER'] != null) {
                $origin = trim($_SERVER['HTTP_REFERER'], '/');
                header("Access-Control-Allow-Origin: $origin");
            } else {
                header("Access-Control-Allow-Origin: null");
            }
        } else if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $config['origin']) === 0){
            header('Access-Control-Allow-Origin: ' . $config['origin']);
        } else {
            echo('ERROR: No cross domain or direct access allowed.');
            trigger_error('No cross domain or direct access allowed');
            exit;
        }

    }

    static function allowCredencials(){
        header("Access-Control-Allow-Credentials: true");
    }

    static function json(){
        header('Content-Type: application/json');
        header("Access-Control-Allow-Credentials: true");
    }

    static function compression(){
        header('Content-Encoding: gzip');
    }


    static function set($type, $value){
        header($type.': ' . $value);
    }

    static function expose($headers){
        header("Access-Control-Expose-Headers: ".implode(', ',$headers));
    }

    static function permissionError($text = "Permission error")
    {

        $response['status'] = $text;
        if (!in_array('ob_gzhandler', ob_list_handlers())) {
            ob_start('ob_gzhandler');
        } else {
            ob_start('ob_gzhandler');
        }
        
        //self::forbidden();
        self::accessControlAsRefer();
        self::allowCredencials();
        self::json();
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    static function notModified(){
        self::accessControlAsRefer();
        self::allowCredencials();
        header('HTTP/1.1 304 Not Modified');
    }

    static function zipfile($file, $friendlyName='file.zip'){
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($file));
        header("Content-Disposition: attachment; filename=\"".basename($friendlyName)."\"");
    }

    static function csv($friendlyName = 'file.csv'){
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$friendlyName}");
    }





}