<?php
defined("isInSideApplication")?null:die('no access');
/*
$email = new email('emailtype'); will look in template for email.emailtype.tpl
$email->assignBodyVars('vars', $vars);
$email->setAddress('setFrom', 'no_reply@promotoolkit.co.uk');
$email->setAddress('addAddress', 'test1@email.com');
$email->setAddress('addAddress', 'test2@email.com');
$email->setAttribute('Subject', 'some subject');
$email->send();
*/
#[AllowDynamicProperties]
class email
	{

		function __construct($emailType)
		{
			//require_once 'libs/PHPmailer/class.PHPmailer.php';
			$this->type      = $emailType;
			$this->PHPmailer = new PHPMailer;
			$this->smarty    = new_smarty();
			$this->bodyVars  = new stdClass;
            $this->isHtml      = true;

            $this->PHPmailer->CharSet = 'UTF-8';
		}

		function setAttribute($attr, $value )
		{
			$this->PHPmailer->$attr = $value;
		}

		function addAttachment($path) {
			$this->PHPmailer->AddAttachment($path);
		}


		function setAddress($type, $value, $optional='')
		{
			$this->PHPmailer->$type($value, $optional);
		}

		function getBody()
		{ #display body for debugging only
			return $this->prepareBody();
		}

		function send()
		{
			//include('config/config.php');
			global $config;
			$this->PHPmailer->Body = $this->prepareBody();
			
            if ($config['debugEmail']){
                echo '*****Email body******<br/>' . $this->PHPmailer->Body . '<br/>******Email body end*****';
            }
			try {
				$this->PHPmailer->isHTML($this->isHtml);    // Set email format to HTML 
				
				if ($config['enableOutgoingEmail']) {
					
					if (!$this->PHPmailer->send()){
                        throw new exception('Message could not be sent. Mailer Error: ' . $this->PHPmailer->ErrorInfo);
                    } else {
						if ($config['debugEmail']){
							echo '*****sent!*****';
						}
					}
					
				}
			} catch (Exception $e) {
				throw new exception('Message could not be sent. Mailer Error: ' . $this->PHPmailer->ErrorInfo);
			}
            
            return true;
		}

		function assignBodyVars($name, $value)
		{
			$this->bodyVars->$name = $value;
		}

		function prepareBody()
		{
            global $config;
			$this->smarty->assign('config', $config);
			$this->smarty->assign('template_vars', json_decode(json_encode($this->bodyVars)));
			return $this->smarty->fetch('email.' . $this->type . '.tpl');
		}
	}
