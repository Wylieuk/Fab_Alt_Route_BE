<?php
defined("isInSideApplication")?null:die('no access');
#useage redirect('profile',array('id'=>123, 'action'=>'lookup')); 
	function redirect($pageStr, $params=false){
		$query = '';
		if ($params){
			foreach($params as $key=>$value){
				$querys[] = $key.'='.$value; 	
			}
			$query = '&'.implode('&',$querys);
		}
		if (headers_sent() === false){
			header('Location: index.php?page='.$pageStr.$query);	
		}
		else{
			echo '<script>window.location.replace("index.php?page='.$pageStr.$query.'");</script>';	
		}
		exit;
	}