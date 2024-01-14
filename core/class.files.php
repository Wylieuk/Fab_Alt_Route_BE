<?php

class files{

	static function deleteDirectory($dir) {
	    if (!file_exists($dir)) {
   	     return true;
   	 }
	
   	 if (!is_dir($dir)) {
   	     return unlink($dir);
   	 }
	
	    foreach (scandir($dir) as $item) {
	        if ($item == '.' || $item == '..') {
	            continue;
   	     	}
   	     	if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
   	     	    return false;
   	     	}
   		}
    	return rmdir($dir);
	}

	static function deleteFile($file){
		if (file_exists($file)) {
            return unlink($file);
        } else {
            return true;
        }
	}

	static function renameDirectory($dir, $newDir){
		if (!is_dir($dir)) {
			return false;
   		}
		return rename($dir, $newDir);
	}
	
	static function copyDirectory($src, $dst) { 
	    $dir = opendir($src);
		 if (!file_exists($dst)) {
    		@mkdir($dst); 
		 }
    	while(false !== ( $file = readdir($dir)) ) { 
        	if (( $file != '.' ) && ( $file != '..' )) { 
           	 if ( is_dir($src . '/' . $file) ) { 
           	     self::copyDirectory($src . '/' . $file,$dst . '/' . $file); 
           	 } 
           	 else { 
           	     if(!copy($src . '/' . $file,$dst . '/' . $file)){
					 trigger_error('ERROR: Cannot copy '. $src.' -> '.$dst);
					return false; 
				 }
           	 } 
        	} 	
    	} 
    	closedir($dir); 
		return true;
	} 

	static function findOldestFile($dir, $wildcard=false){
		if (!$files = self::getDirFiles($dir, $wildcard)){
			return false;
		}
		usort($files, function ($a, $b) {
			return $a['modified'] <=> $b['modified'];
		});
			return current($files);
	}
		

	static function findYoungestFile($dir, $wildcard = false){
		if (!$files = self::getDirFiles($dir, $wildcard)) {
			return false;
		}
		usort($files, function ($a, $b) {
			return $a['modified'] <=> $b['modified'];
		});
			return end($files);
	}

	static function getDirFiles($dir, $wildcard=false){
		if (!is_dir($dir)) {
			trigger_error($dir . ' is not a directory');
		}
		$files  = [];
		$dirObj = dir($dir);
		while (false !== ($entry = $dirObj->read())) {
			if (is_file($dir . DIRECTORY_SEPARATOR . $entry)) {
				if ($wildcard) {
					if (preg_match('/' . $wildcard . '/', $entry, $matches) === 1) {
						$file['path'] = $dir . DIRECTORY_SEPARATOR;
						$file['name'] = $entry;
                        $file['modified'] = filemtime($dir . DIRECTORY_SEPARATOR . $entry);
                        $file['size'] = filesize ($dir . DIRECTORY_SEPARATOR . $entry);
						$files[] = $file;
					}
				} else {
					$file['path'] = $dir . DIRECTORY_SEPARATOR;
					$file['name'] = $entry;
                    $file['modified'] = filemtime($dir . DIRECTORY_SEPARATOR . $entry);
                    $file['size'] = filesize ($dir . DIRECTORY_SEPARATOR . $entry);
					$files[] = $file;
				}
			}
		}
		return $files;
	}

    static function deleteDirFilesOlderThan($dirName, $timestamp) {
        $files = self::getDirFiles($dirName);
        $deleted = 0;

        foreach ($files as $file) {
            if ($file['modified'] < $timestamp) {
                self::deleteFile($file['path'].$file['name']);
                $deleted ++;
            }
        }

        return $deleted . 'files deleted';
    }
}



?>