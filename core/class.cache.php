<?php defined("isInSideApplication")?null:die('no access');


/*
* t: cache any item
***************************************/

/*
usage 
    $cache = new cache('json_reader', urlqueryarray, true, $this->page);
		$result = $cache->fetch(true/false);
		if (!$result){// start non-cache content
            $result = 'stuff'
            $cache->save($result, ?$ttl);
        }

       debug($result)
    
*/

/**
 * @param cache_item string Name of item you want to cache
 * @param query_array array KeyValue pair that becomes the  file hash
 * @param noComments bool Output comments toi show cached content (only use for html)
 * @param page page Used when $query_array is not used to build the httpQuery used
 * @internal info: adding &flush_cache=true to the url will flush and recreate all the the caches for the url
 * @return void
 ***************************************/
#[AllowDynamicProperties]
class cache {

	private $cache_file_name;
	private $cache_file_suffix;

    	
	//set cache file name and find cif file database timestamp
	function __construct(string $cache_item = '', $query_array=[], bool $noComments=false, $page=null) {


        $cache_item = str_replace(['/', '\'', '<', '>', ':', '*', '|' ], '_', $cache_item);

        global $config;

        require_once('config/config.cache.php');

        $this->cache_file_suffix = $config['compressCache'] ? '.cache.gz' : '.cache';
		
		if (!$config['cache']){
			$this->purge();
			return;
		}

		$this->noComments = $noComments;
        $this->cache_item = $cache_item;


        // get hashable collection
        switch (true){

            case ($query_array && isset($query_array['data'])):
                $this->queryArray = $query_array['data'];
                break;

            case ($query_array && count($query_array) > 0):
                $this->queryArray = $query_array;                
                break;
            
            case !empty($page):
                $this->queryArray = $page->data;
                break;

            default:
                $this->cache_item_cachable = false;
                return;        
        }
	

        if (!is_array($this->queryArray)){ 
            $this->queryArray = [$this->queryArray];
        }

        // unset items to ignore in hast
        unset($this->queryArray['token']);
        unset($this->queryArray['flush_cache']);

        $this->expiry_timestamp     = $this->get_expiry_timestamp();
        $this->cache_file_name      = $config['cache_folder'] . '/' . $this->cache_item . '^' . $this->getQueryHash() . $this->cache_file_suffix;
        $this->cache_item_cachable  = $config['cacheItems'][$this->cache_item]['cachable'] ?? false;
                						
		if (!$this->cache_item_cachable){
            $this->removeCacheFile();
            return;
        }
        		
		$this->purge();	
			
	}
	
	
	private function getQueryHash(){

		global $page;
        
        $deletes = array('time');


		if ($this->queryArray){
            $query_array = $this->queryArray;
        }
		else {
            $query_array = $page->query_array;
        }
		
		foreach ($deletes as $itemToDelFromQuery){
			unset($query_array[$itemToDelFromQuery])	;
        }
 
		return hash('sha256', json_encode($query_array));
	}





    /**
     * @param flush bool flush the cache true/false
     * @return bool|object the cached object
     ***************************************/
	public function fetch($flush = false){
		global $config;
		if (!$config['cache']){
			return false;
		}
		if (!$this->cache_item_cachable){
			return false;
		}

		//check file has expired
		if ($config['cache'] && !$this->item_expired() && $flush != true){
			$contents = $this->uncompress(file_get_contents($this->cache_file_name));
			if($this->noComments){
				$data = $contents;
			}
			else{
				$data = '<!--start cached content['.$this->cache_item.'] -->'.$contents.'<!--end cached content -->';
			}
			return $data;
		}else{		
			return false;		
		}
	}




    /**
     * @param content object item to save to the cache
     * @param ttl int seconds to cache for (overrides global $config)
     * @return 
     ***************************************/
	public function save(string $content,  $ttl=false ){	

        global $config;        

		if ($config['cache'] && $this->cache_item_cachable){
			if(file_put_contents($this->cache_file_name, $this->compress($content))){
				if($ttl){
					touch($this->cache_file_name, time()+$ttl-$config['cache_lifetime']);
				}
				return true;
			}
			return false;
		}
	}
	
	private function compress($content){
        global $config;
        if ($config['compressCache']){
            return gzencode($content);
        } else {
            return $content;
        }
    }

    private function uncompress($content){
        global $config;
        if ($config['compressCache']){
            return gzdecode($content);
        } else {
            return $content;
        }
    }

    private function removeCacheFile(){
        global $config;
        $path = $config['cache_folder'] . '/';
        if (file_exists($this->cache_file_name)){
            unlink($this->cache_file_name);
        }
    }
	
	//delete all expired files
	private function purge(){
        
		global $config;
		global $cache__purged;

		if (isset($cache__purged) && $cache__purged){
            return; //only purge once per app instance
        }

        $cache__purged = true;       
		
        $path = $config['cache_folder'].'/';
        $file_count = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                
                if (file_exists($path.$file) && is_file($path.$file)){
                    $file_count++;
                }
                
                if ( $this->item_expired() && file_exists($path.$file) && is_file($path . $file) && $path.$file == $this->cache_file_name) {
                    //delete expired files
                    unlink($path . $file);
                }

                if (!$config['cache'] && !$cache__purged && file_exists($path.$file) && is_file($path.$file) && (strpos($path.$file, $this->cache_file_suffix) !== false)){
                    //caching off purge everything
                    unlink($path . $file);
                }

                // purge all out of date files
                if ( file_exists($path.$file) && is_file($path . $file) && filemtime($path.$file) < ($this->expiry_timestamp ?? 0)){
                    unlink($path . $file);
                }
            }
        closedir($handle); 
        }

        if( $file_count == 0){$purged = true;}

	}	

    static function purgeAll(){
        global $config;
        $path = $config['cache_folder'].DIRECTORY_SEPARATOR;
        $file_count = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {        
                if (file_exists($path . $file) && is_file($path . $file) ) {
                       unlink($path . $file);
                }
            }
        closedir($handle); 
        }
    }
	
	private function item_expired(){			
		if (!file_exists($this->cache_file_name ?? '')){ return true; }
		if ( filemtime($this->cache_file_name ?? '') < $this->expiry_timestamp){
			return true;
		}
		return false;		
	}
	
	
	
	private function get_expiry_timestamp(){
		global $config;
		//require_once($config['documentroot'].'/classes/class.db.php');
		return time()-$config['cache_lifetime'];
	}

	
}

?>