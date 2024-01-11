<?php
defined("isInSideApplication")?null:die('no access');
class curl
{
    function __construct(){
        $this->options = array();
    }

    function execute()
    {
        global $config;
        $verbose = fopen('php://temp', 'w+');
        $ch = curl_init();

        foreach ($this->options as $headerType => $headerValue){
            //debug($headerType.' '. $headerValue);
            curl_setopt($ch, $headerType, $headerValue);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $result = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404) {
            new exception ('Curl can not open url');
        }

        if ($result === FALSE) {
            new exception ("CURL error (#%d): %s" . curl_errno($ch) . htmlspecialchars(curl_error($ch)));
            return '<root>no data</root>';
        }

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        $curl_debug =  htmlspecialchars($verboseLog);
        if ($config['curl_debug']) {
            debug($curl_debug, 'curl_debug');
        }
        curl_close($ch);
        return $result;
    }
}
