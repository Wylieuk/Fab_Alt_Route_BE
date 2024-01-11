<?php

define("isInSideApplication", true);

header("Content-Security-Policy: default-src 'none'; object-src 'none'");
header("X-Frame-Options: deny");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1");
// Start configure

include_once('../config/config.php');

$config['csp_log']                              = true;
$config['csp_log_file']                         = '../logs/csp-violations.log';
$config['csp_log_file_size']                    = 500; //lines
// End configuration
http_response_code(204); // HTTP 204 No Content

$json_data = file_get_contents('php://input');
$json_data = json_decode($json_data);


// We pretty print the JSON before adding it to the log file
if ($json_data && $config['csp_log']) {

    $json_data->clientIP = $_SERVER['REMOTE_ADDR'];
    $json_data->date = date("Y-m-d m:i:s");
    
    $json_data = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    $cumulative_log = $json_data;
    if (file_exists($config['csp_log_file'])) {
        $log_file_contents = file_get_contents($config['csp_log_file']);
        $log_file_array = explode("}\n", $log_file_contents);
        $log_file_array = array_slice($log_file_array, 0 - $config['csp_log_file_size']);
        $cumulative_log = implode("}\n", $log_file_array) . $json_data;
    }

    file_put_contents($config['csp_log_file'], $cumulative_log . "\n", LOCK_EX);
}