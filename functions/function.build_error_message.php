<?php
defined("isInSideApplication")?null:die('no access');

function build_error_message ($file = false, $class = false, $function = false, $message = false){
	global $user;
	$error_segment['time_stamp'] 								= date('d-m-y h:i:s');
	if (isset($user->username)){$error_segment['username'] 		= $user->username;}
	if ($file){$error_segment['file'] 							= $file;}
	if ($class){$error_segment['class'] 						= $class;}
	if ($function){$error_segment['function'] 					= $function;}
	if (is_string($message)) {
		if ($message){$error_segment['message'] 					= strip_tags($message);}
	}
	
	
	foreach ($error_segment as $key => $segment){
		if (is_string($segment)){
			$error_text[] = $key.': '.$segment;
		}
		else{
			$error_text[] = $key . ': ' . current((array)$segment);
		}
	}
	
	$error_text = implode(' | ', $error_text);
	
	return $error_text.PHP_EOL;
}

?>