<?php
defined("isInSideApplication")?null:die('no access');

function script_run_time(){
	
	$time_end = microtime(true);
	$runtime = $time_end - $_SESSION['time_start'];
	return round($runtime, 2);
}

function script_memory_use(){
	round ((memory_get_peak_usage(true)/1024/1024), 2);
}
