<?php
defined("isInSideApplication")?null:die('no access');
/*usage
add anywhere.. builds an array of anonymous functions (closures) to run
$event->afterSaveSubmission[] = function(){stuff to do when event called;};


add where you want events to be triggered
$event->trigger('afterSaveSubmission');

*/


class event{
	
	function __contruct(){
		global $config;
		$this->log = array();

	}
	
	
	function trigger($eventInstance){
		
		global $config;
		if($config['debug_event']){
			comment($eventInstance);
		}
		if ($config['debug_event']){
			$bt = debug_backtrace();
			$caller = array_shift($bt);
			$this->log[] 		= 'Event '.$eventInstance.' triggered in '.$caller['file'].' at line '. $caller['line'];
		}
		if (!isset($this->$eventInstance)){return false;}
		if (!is_array($this->$eventInstance)){return false;}
		foreach($this->$eventInstance as $function){
			$function();
		}
			return true;
	}
	
	
	function add($type, $action){
		global $config;
		if ($config['debug_event']){
			$bt = debug_backtrace();
			$caller = array_shift($bt);
			$this->log[] 		= 'Event '.$type.' added in '.$caller['file'].' at line '. $caller['line'];
		}
		$this->{$type}[] 	= $action;
	}
	
	function __destruct() {
		global $config;
		if ($config['debug_event']){
			if (isset($this->log)){
				debug($this->log);
			}
		}
	}
	
	
	
	
	
	
	
}


?>