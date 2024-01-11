<?php
defined("isInSideApplication")?null:die('no access');

function log_error($new_error, $logFile = null){
    global $config;

    $logFile = $logFile ?? $config['error_log'];
    $cumulative_error_log = $new_error;

	
	if(file_exists($config['documentroot'].$logFile)){
		$log_file_contents = file_get_contents($config['documentroot'] . $logFile);
		$log_file_array = explode(PHP_EOL, $log_file_contents);
		$log_file_array = array_slice($log_file_array, 0-$config['error_log_count']);
		$cumulative_error_log = implode(PHP_EOL, $log_file_array).$new_error.PHP_EOL;
        file_put_contents($config['documentroot'].$logFile, $cumulative_error_log.PHP_EOL, LOCK_EX);
	}
}


function clear_Log($logFile){
    global $config;
    file_put_contents($config['documentroot'].$logFile, "", LOCK_EX);
}
