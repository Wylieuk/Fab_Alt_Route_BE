<?php
defined("isInSideApplication")?null:die('no access');
//usage debug ($array/object , 'name of array or object'); $name of variable only need for clarity
function debug($item, $item_name = '' ,$return = false){


	global $config;
	global $debug_count;
	$size = 0;
	try {$size = round((strlen(serialize($item))/1024),2).'kb';}catch (Exception $e) {}
	//try {$size = round((mb_strlen(serialize($item))/1024),2).'kb';}catch (Exception $e) {}
	
	if (!isset($debug_count)){
		$debug_count = 0;
		echo '<script nonce="'.$config['CspNonce'].'" src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';
		echo '<script nonce="'.$config['CspNonce'].'"  src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>';
		echo '<script  nonce="'.$config['CspNonce'].'" src="libs/js/debug.js"></script>';
		echo '<link nonce="'.$config['CspNonce'].'" href="templates/css/CSSloader.php?CSSfile=https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css">';
		
	}
	$string = '';
	$debug_count++;
	$item_name = ' ['.$item_name.']';
	$item_class = '';
	$backtrace = debug_backtrace();
	$item_type = ' ['.gettype(($backtrace[0]['args'][0])).']';
	//echo $backtrace[0]['args'][0];
	if (isset($backtrace[0]['args'][0])){
		if (is_object($backtrace[0]['args'][0])){
			$item_class =' [class='.get_class($backtrace[0]['args'][0]).']';
		}
	}
	else{
		$item_class = 'NULL';
	}
	
	
	//print_r($backtrace);
	
	if (isset($config['debug'])){
	  if ($config['debug']){
		if ($return){
			$string = '<pre>';
			if (is_bool($item)){
				$item ? $string .= 'true' : $string .= 'false';
			}
			else{
				$string .= print_r($item,true);
			}
			$string .= '</pre>';
			return $string;
		}else{
			if ($debug_count < 2){$string.= '<div class="debug_title debug_unselected">Debug Data</div>';}	
			$string .= '<br/ class = "debug_chunk hidden"><div class="debug_chunk hidden">';	
			$string .= '<div class="debug"><b>File: '.$backtrace[0]['file'].' | Line: '.$backtrace[0]['line'].'</b></div>';
			$string .= '<div class="debug"><b>Debug Data '.$item_type.$item_class.$item_name.' '.$size.'</b></div>';
			$string .= '<div class="debug">item count: '.count((array)$item).' | depth: '.debugDepth($item).'</div>';
			$string .= '<div class="debug">';
			$string .= '<pre>';
			if (is_bool($item)){
				$item ? $string .= 'true' : $string .= 'false';
			}
			else{
				//ob_start();
				//var_dump($item);
				//$string .= ob_get_clean();
				$string .= print_r($item,true);
			}
			$string .= '</pre>';
			$string .= '</div>';
			$string .= '</div>';
			$css = '
<style nonce="'.$config['CspNonce'].'">
.debug_chunk{
		display: inline-block;
		z-index:9999;
		font-family: arial!important;
		border-left: 20px solid #ccc;
		padding:20px;
		margin-left:30px;
		margin-bottom:10px;
		background-color:#eee;
		color:black;
		position:relative;
}

.debug {
	font-family: arial!important;
	margin-bottom:5px;	
}

.debug_data, .debug{
	font-family: arial!important;
	font-size:10px;
	clear:both;
}
	
.debug_title{
	font-family: arial!important;
	border-left: 20px solid #ccc;
	background-color:#eee;
	margin:5px 5px 10px 30px;
	display: inline-block;
	cursor:pointer;
	padding:5px;
}

.debug_unselected::after{
	 content:" ˃";	
}

.debug_selected::after{
	 content:" v";	
}

.hidden{
	display:none;	
}

</style>
';
		

		
			echo $css;
			echo $string;
		}}//end if debug
	}
	
}
if (!function_exists('debugDepth')) {
	function debugDepth($item)
	{
		$array = json_decode(json_encode($item), true);
		if (!is_array($array)) {
			return 1;
		}
		$max_depth = 1;
		foreach ($array as $value) {
			if (is_array($value)) {
				$depth = debugDepth($value) + 1;
				if ($depth > $max_depth) {
					$max_depth = $depth;
				}
			}
		}
		return $max_depth;
	}
}
