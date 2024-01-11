<?php
defined("isInSideApplication")?null:die('no access');


class image{
	
	
	function __construct($filename){
		$this->fileName = $filename;
		
		//comment($this->fileName)	;
		require_once 'libs/SimpleImage/class.SimpleImage.php';
	}
	
	
	function sizeDown($maxWidth, $maxHeight, $newFileName=FALSE){
		//comment($maxWidth.', '.$maxHeight);
		if(!file_exists($this->fileName)){
			return false;	
		}
		try {
			$image = new SimpleImage();

			$image->fromFile($this->fileName);
			$image->bestFit($maxWidth, $maxHeight);
			if($newFileName){
				$image->toFile($newFileName);
				return true;
			}else{
				return $image->toDataUri();
			}
		} catch(Exception $err){
			comment($err->getMessage());
			return false;
		}
		return true;
	}
	
	function createThumbnail($maxWidth, $maxHeight){
		global $config;
		if(!file_exists($this->fileName)){
			//comment($this->fileName);
			return false;	
		}
		$thumbFileName = substr($this->fileName,0,-4).'_thumb'.$maxWidth.'x'.$maxHeight.substr($this->fileName,-4);
		if(file_exists($thumbFileName)){
			return $thumbFileName;
		}
		elseif ($this->sizeDown($maxWidth, $maxHeight, $thumbFileName)){
			return $thumbFileName;
		}
		
		return false;
		
	}
	
	static function deleteAssocatedThumbs($mainImageFullPath){
		$fl = glob(substr($mainImageFullPath,0,-4).'_thumb*');
		//debug($fl , substr($mainImageFullPath,0,-4).'_thumb*');
		foreach ($fl as $f){
			unlink($f);	
		}
	}
	
	static function isImage($path){
		global $config;
		$a = getimagesize($path);
		$image_type = $a[2];
		if(in_array($image_type , $config['allowed_image_types'])){
			return true;
		}
		return false;
}
	
}


?>