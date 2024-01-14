<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class ftp{
	
	function __construct($server, $username=false, $password=false, $options=array()){
		
		
		$defaults = array(
			'passiveMode' => true
	    );
	    $options = array_merge($defaults, $options);
		
		
		// set up basic connection
		$this->conRes = ftp_connect($server);
		
		if ($username and $password){
			// login with username and password
			if(!$login_result = ftp_login($this->conRes, $username, $password)){
				trigger_error('Cannot login to FTP server '.$server.' with username '.$username.' and password');
			}
		}else{
			if(!$login_result = ftp_login($this->conRes, 'anonymous', 'guest')){
				trigger_error('Cannot login to FTP server '.$server.' with username anonymous and password guest');
			}
		}
		
		ftp_pasv($this->conRes, $options['passiveMode']);

	}
	
	function getDirList(){
		$this->dirList = ftp_mlsd($this->conRes, ".");
		if($this->dirList){
			if (count($this->dirList) > 0){
				return $this->dirList;
			}
		}
		return array();
	}
	
	function getLastModifiedTime($file){
		return ftp_mdtm($this->conRes, $file);
	}
	
	function getFile($srcFile, $desinationFolder){
		if (!ftp_get($this->conRes, $desinationFolder.'/'.$srcFile, $srcFile, FTP_BINARY)) {
    		return false;
		} 
		return true;
	}
	
	
	
	
	
}
