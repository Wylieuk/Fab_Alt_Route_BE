<?php
defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class log_writer{
			
		static function write($filename, $str_data, $append=true, $endofLine = false){
			if($endofLine){$endofLine = PHP_EOL;}else{$endofLine = '';}
			global $config;
			if ($config['write_log']){
				$options = '';
				if ($append){file_put_contents($filename, date('d-m-Y H:i:s') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . $str_data.$endofLine, FILE_APPEND);}
				else{file_put_contents($filename, date('d-m-Y H:i:s').' '.$str_data.PHP_EOL);}
			}	
			if($config['show_log']){
				echo '<div>'.$str_data.'</div>';
			}
			
		}
			
			
		static function buildMessage ($file, $record_type, $record_count , $PHPerror){

			if ($record_type){		$error[]		=  'Record_type: '.$record_type;		}
			if ($record_count){		$error[]		=  '$record_number: '.$record_count;		}
			if ($PHPerror){			$error[]		=  'Error: '.strip_tags($PHPerror);		}
			if ($file){				$error[]		=  'Called By: '.$file;		}
			
			$error_text = implode(' | ',$error);
			$error_text.PHP_EOL;
			return $error_text;
		}

		static function logToDB($table, $data, $purgeAge=false){
            global $config;
            if (!isset($config['logUserAccessToDB']) || !$config['logUserAccessToDB']){return;}
			$db = new db;
			$query = $db->build_insert($table, array('username' =>  $data->username, 'ip' => $_SERVER['REMOTE_ADDR']));
			$db->preparedQuery($query['statement'], $query['values']);
				
			if($purgeAge){
				$age = time()-$purgeAge;
				$query = 'DELETE FROM `'.$table.'` WHERE  UNIX_TIMESTAMP(`timestamp`) < :age';
				$db->preparedQuery($query, ['age' => $age]);
			}
		}
}
?>