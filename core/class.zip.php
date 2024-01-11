<?php
defined("isInSideApplication")?null:die('no access');
class zip{
	
		function __construct(){
			$this->zip = new ZipArchive;
		}
	
		function extractTo($zipFile, $folder){
			if ($this->zip->open($zipFile) === TRUE) {
   				$this->zip->extractTo($folder);
   				$this->zip->close();
    			return true;
			} else {
    			return false;			}
		}	
}