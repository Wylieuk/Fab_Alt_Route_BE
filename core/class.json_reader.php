<?php

class json_reader{

    public $ttl = null;
    public $flush = false;
	
	function __construct(bool $outputObject = false , $page=null){
        $this->page = $page;
        $this->outputAsArray = !$outputObject;
        $this->payload = null;
        $this->isJson = true;
        $this->headers = [];
    }
    
    public function setPayload($data){
        $this->payload = json_encode($data);
    }
	
	public function loadJson(string $url, bool $cachable=false){

		$this->cachable = $cachable;
        if ($this->isJson){
		    return $this->decodeJson($this->readUrl($url));
        } else {
            return $this->readUrl($url);
        }
	}

    public function setHeaders(array $headers = []){
        $this->headers = $headers;
    }
	
	private function readUrl($url){
        
		global $config;
        global $configCacheItems;

        global $page;

		if($this->cachable){
			$configCacheItems['json_reader']['cachable'] = true;
		} else {
            $configCacheItems['json_reader']['cachable'] = false;
        }
		$cache = new cache('json_reader', explode('&', $url. '&payload=' . $this->payload), true, $this->page);
		$result = $cache->fetch($this->flush);
		if (!$result )
		{//start non-cache content
			$verbose = fopen('php://temp', 'w+');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_ENCODING , "");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            if($this->payload) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($this->payload)
                    ], $this->headers)
                );
            }
            else if(isset($this->headers) && is_array($this->headers)){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            }
			$result = curl_exec($ch);
		
			if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404){
                new non_fatal_exception ('Curl can not open '.$url);
			}
		
			if ($result === FALSE) {
    			new non_fatal_exception ("CURL error (#%d): %s" . curl_errno($ch) . htmlspecialchars(curl_error($ch)));
                return '{}';
			}	

			
			if($config['curl_debug']){

                debug(array_merge([
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($this->payload ?? '')
                ], $this->headers ?? []));

                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);
                $curl_debug =  htmlspecialchars($verboseLog);

                debug(curl_getinfo($ch));

                debug($curl_debug, 'json_reader->readUrl, curl_debug');

                debug( $this->payload, 'payload');
               

			}
            curl_close($ch);
			$cache->save($result, $this->ttl);
		}
		if($config['curl_debug']){
			debug($this->decodeJson($result));
		}
		return $result;
	}
	
	private function decodeJson($json){
		if ($json){
			return json_decode($json, $this->outputAsArray);
		}
		return false;
	}
    
    static function isJson($string) {
        if (is_numeric($string)) {return false;}

        $t = json_decode($string);
        if (json_last_error() == JSON_ERROR_NONE){
            return true;
        }

        $t = json_decode(utf8_decode($string));
        if (json_last_error() == JSON_ERROR_NONE){
            return true;
        }

        $t = json_decode(encoding::fixUTF8($string));
        if (json_last_error() == JSON_ERROR_NONE){
            return true;
        }
        //debug([ $string, json_last_error() ] );//print_r( ($string) );exit;
        return false;
    }
   
	

}

?>