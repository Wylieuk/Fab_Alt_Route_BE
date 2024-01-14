<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class xml_reader{
	
	function __construct(){
        $this->cachable = false;
	}
	
	function loadXml($url, $cachable=false){
		$this->cachable = $cachable;
		return $this->decodeXml($this->readUrl($url));
	}
	
	
	function readUrl($url){
		global $config;
		if($this->cachable){
			$config['cacheItems']['xml_reader']['cachable'] = true;
		}
		$cache = new cache('xml_reader', explode('&',$url), true, false);
		$result = $cache->fetch();
		if (!$result )
		{//start non-cache content
			$verbose = fopen('php://temp', 'w+');
			$ch = curl_init();
			//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type:text/xml'));
			curl_setopt($ch, CURLOPT_HEADER, 0 );
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, $verbose);
			$result=curl_exec($ch);
			
			if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404){
				new non_fatal_exception  ('Curl can not open '.$url);
			}
			
			if ($result === FALSE) {
				new non_fatal_exception ("CURL error (#%d): %s" . curl_errno($ch) . htmlspecialchars(curl_error($ch)));
                return '<root>no data</root>';
			}
	
			rewind($verbose);
			$verboseLog = stream_get_contents($verbose);
			$curl_debug =  htmlspecialchars($verboseLog);
			if($config['curl_debug']){
				debug($curl_debug, 'xml_reader->readUrl, curl_debug');
			}
			curl_close($ch);
			$cache->save($result );
		}
		return $result;
	}
	
	function decodeXml($xml){
		$xml2array = new xml2array;
		return $xml2array->convert(simplexml_load_string($xml));
	}
	


	

}
	
?>