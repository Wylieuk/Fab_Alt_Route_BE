<?php
defined("isInSideApplication")?null:die('no access');

class config {

    function updateConfig(string $configJson) {
        global $config;
        if (!json_reader::isJson($configJson)) {
           throw new Exception('Error validating config'); 
        }

        $configJson = json_encode(json_decode($configJson), JSON_PRETTY_PRINT);
        file_put_contents($config['feConfigFile'], $configJson);
    }

    static function load() {
        global $config;
        return json_decode(file_get_contents($config['feConfigFile']));
    }

}