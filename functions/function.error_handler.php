<?php

function error_handler($errno, $errstr, $errfile, $errline, $errcontext=''){
    

	$display_error = array();
    global $global_error_count;
    $global_error_count ++;

    global $config;
    


	global $PHP_errors;
	$template_error 		= false;
	 $time_of_error 		=  date($config['date_format']);
	 $error_levels 			= array(
        1     =>	 'E_ERROR',
        2     =>	 'E_WARNING',
       	4     =>	 'E_PARSE',
        8     =>	 'E_NOTICE',
        16    =>	 'E'.$config['coreTablePrefix'].'ERROR',
        32    =>	 'E'.$config['coreTablePrefix'].'WARNING',
       	64    => 	 'E_COMPILE_ERROR',
        128   =>	 'E_COMPILE_WARNING',
        256   =>	 'E_USER_ERROR',
        512   =>	 'E_USER_WARNING',
        1024  =>	 'E_USER_NOTICE',
        2048  =>	 'E_STRICT',
        4096  =>	 'E_RECOVERABLE_ERROR',
        8192  =>	 'E_DEPRECATED',
        16384 =>	 'E_USER_DEPRECATED',
        32767 =>	 'E_ALL'
    );	
	
	if (!isset($PHP_errors)){$PHP_errors="";}
	$error = "0x".time()." $time_of_error $error_levels[$errno]: $errstr | File: $errfile | Line: $errline. ";
	$PHP_errors .= $error; 
	
	if (stripos($error, '.tpl')!==false and stripos($error, 'E_NOTICE')!==false ){
		$template_error = true;
	}


  
	//debug(debug_backtrace());


	if (!$template_error) {
		$display_error[] = "$time_of_error $error_levels[$errno] <b>$errstr</b> in file <b>$errfile</b> at line <b>$errline</b>.";
		foreach (debug_backtrace() as $step) {
			if (isset($step['file']) and $step['file'] != $errfile) {
				$display_error[] = '<i>Called by ' . $step['file'] . ' at line ' . $step['line'] . '</i>';
			}
		}

	} else {
        //echo simple error for templates
        $t = explode('\\', $errfile);
        $errfile = end($t);
        
		$display_error[] = "$error_levels[$errno] <b>$errstr</b> in file <b>$errfile</b> at line <b>$errline</b>.<br/>\n";
	}
	$errorOutput =  implode('<br/>', $display_error);

	if ($config['show_errors']) {
        if (strtoupper(ini_get('display_errors')) != 'OFF' && ini_get('display_errors') != 0){
		    echo $errorOutput;
        }
	}
    
    /*
    * !!: Add $eet below for ERROREMAILS
    ***************************************/
    $eet = new email_error_tracker($errstr, $errfile, $errline);

	if ($config['email_errors']) {

        /*
        * !!: Add if statement below for ERROREMAILS
        ***************************************/
        if ($eet->email_already_sent) {
            $eet->save(0);
        } else {
            $_REQUEST['r_page'] = !empty($_REQUEST['r_page']) ? base64_decode($_REQUEST['r_page']) : null;

            $email = new email('error_output'); //will look in template for email.emailtype.tpl
            $email->type = 'email_fatal_errors';
            $email->assignBodyVars('siteAddress', $config['siteaddress'] != ''?$config['siteaddress']:__FILE__);
    
            if(isset($errorOutput)){
                $email->assignBodyVars('errorOutput', $errorOutput);
            }
    
            if(isset($_SERVER)){
                $email->assignBodyVars('serverVars', $_SERVER);
            }
    
            if(isset($_REQUEST)){
                $email->assignBodyVars('request', $_REQUEST);
            }
    
            global $user;
            if(isset($user)){
                $email->assignBodyVars('user', json_encode($user));
            }

            /*
            * !!: Add lines 111 to 113 for ERROREMAILS
            ***************************************/
            if ($config['limit_email_error_alerts'] !== 0) {
                $email->assignBodyVars('emailCount', $eet->error_count);
            }
            
            global $page;
            if(isset($page) && isset($page->requestBody)){
                if (!is_string($page->requestBody)){
                    $email->assignBodyVars('requestBody', json_encode($page->requestBody));
                } else {
                    $email->assignBodyVars('requestBody', $page->requestBody);
                }
               
            }
    
            
    
            $email->setAddress('setFrom', $config['from_email'], 'Website Error Reporter');
            $email->setAddress('addAddress', $config['email_errors_target']);
            $email->setAttribute('Subject', 'ERROR: '.($config['siteaddress'] != ''?$config['siteaddress']:__FILE__));
            $email->send();
            /*
            * !!: Add line 134 for ERROREMAILS
            ***************************************/
            $eet->save(1);
        }
	}



	if ($config['debug_errors']){
		global $page;
		if(isset($page->debug)){
			debug($page->debug);
		}
		if($config['write_debug_errors']){
			if(isset($page->debug)){
				$debugData = debug($page->debug,'Debug data',true);
			}
		}
	}
	
	global $user;

    
	
	if (!$template_error ){
		log_error("0x".time(). ' ' .$_SERVER['REMOTE_ADDR'] . ' ' . ($user->username ?? 'Unknown').' '.strip_tags(implode(PHP_EOL, $display_error)));
		if ($config['write_debug_errors'] and $config['debug_errors']){
			if(isset($page->debug)){
				log_error($debugData);
			}
		}
	}
	
	if ($config['stopOnError']){exit;}
	//return false; //show normal php errors
	return true; //not show normal php errors
}

function fatalErrorShutdownHandler(){
    
    /*set headers to keep session alive if error happens (only aplicable to api scenario)*/
	global $config;
	global $lang;
	


  $last_error = error_get_last();


  if ($last_error && ($last_error['type'] === 1 or $last_error['type'] === 4) ) {

    if (strtoupper(ini_get('display_errors')) != 'OFF' && ini_get('display_errors') != 0){
        echo '<br/><br/>' . date('Y-m-d H:i:s').' '.nl2br(strip_tags($last_error['message']));
    }

   
		
		chdir (__DIR__.'/..');

        require_once ('libs/smarty/libs/Smarty.class.php');

		//echo '**' . getcwd() . '**';

		spl_autoload_register(function ($class_name) {

            if (is_file('core/class.'.$class_name.'.php')){
                require_once ('core/class.'.$class_name . '.php');
            }

		});

        /*
        * !!: add $eet below for ERROREMAILS
        ***************************************/
        $eet = new email_error_tracker($last_error['message'], $last_error['file'], $last_error['line']);

        global $user;

        log_error("0x".time(). ' ' .$_SERVER['REMOTE_ADDR'] . ' ' . ($user->username ?? 'Unknown').' '. date('Y-m-d H:i:s').' '.strip_tags($last_error['message']));
    

		if ($config['email_fatal_errors']) {

            $_REQUEST['r_page'] = !empty($_REQUEST['r_page']) ? base64_decode($_REQUEST['r_page']) : null;

            /*
            * !!: add if statement below for ERROREMAILS
            ***************************************/
            if ($eet->email_already_sent) {
                $eet->save(0);
            } else {
                $email = new email('error_output'); //will look in template for email.emailtype.tpl
                $email->type = 'email_fatal_errors';
                $email->assignBodyVars('siteAddress', $config['siteaddress'] != ''?$config['siteaddress']:__FILE__);
                if(isset($last_error['message'])){
                    $email->assignBodyVars('errorOutput', $last_error['message']);
                }
                if(isset($_SERVER)){
                    $email->assignBodyVars('serverVars', $_SERVER);
                }
                if(isset($_REQUEST)){
                    $email->assignBodyVars('request', $_REQUEST);
                }
                global $user;
                if(isset($user)){
                    $email->assignBodyVars('user', json_encode($user));
                }

                /*
                * !!: add lines 243 to 245 for ERROREMAILS
                ***************************************/
                if ($config['limit_email_error_alerts'] !== 0) {
                    $email->assignBodyVars('emailCount', $eet->error_count);
                }
    
                $email->setAddress('setFrom', $config['from_email'], 'Website Error Reporter');
                $email->setAddress('addAddress', $config['email_errors_target']);
                $email->setAttribute('Subject', 'ERROR: ' . ($config['siteaddress'] != ''?$config['siteaddress']:__FILE__));                
                $email->send();
                /*
                * !!: add lines 253 for ERROREMAILS
                ***************************************/
                $eet->save(1);
            }
		}   
  }
}