<?php
defined("isInSideApplication")?null:die('no access');

function email ( $recipient, $subject ,$from_email, $from_name, $body, $files=false, $cc_addresses=false ){
	global $config;
		
	$mail = new PHPMailer;
	
	
	$mail->From = $from_email;
	$mail->FromName = $from_name;
	$mail->addAddress($recipient);  
	
	if ($cc_addresses){
		if (is_array($cc_addresses)){
			foreach($cc_addresses as $cc_address){
				$mail->addCC($cc_address);
			}
		}	
	}
	

	if ($files){
		if(is_array($files)){
			foreach ($files as $file){
				if (is_file($file)){   
					$mail->addAttachment($file);
				}else{
					trigger_error('File not found');
				return false;
				}
			}
		}else{trigger_error('Files should be an array');}
	}
	

	
	$mail->Subject = $subject ;
	$mail->Body    = $body;
	
	if(!$mail->send()) {
		trigger_error('Email could not be sent, Mailer Error: ' . $mail->ErrorInfo);
		return false;
	} else {
		return true;
    echo 'Email has been sent';
	}
	
}