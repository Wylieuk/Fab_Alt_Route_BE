<?php
#[AllowDynamicProperties]
class sanitize{
  
    
    static function userInput($dirtyValue){

        switch (true){

            case $dirtyValue === 'null':
                return null;

            case is_null($dirtyValue):
                return $dirtyValue;

            case is_numeric($dirtyValue):
                return $dirtyValue;

            case is_bool($dirtyValue):
                return $dirtyValue;
            
            case json_reader::isJson($dirtyValue):
                return self::json($dirtyValue);

            case is_array($dirtyValue):
                return self::array($dirtyValue);
                    
            case is_string($dirtyValue):
                return self::filter($dirtyValue, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);

            default:
                trigger_error('Unknown variable type in query params');
        }

    }
    
    static function filter($dirtyValue, $filterType, $flags){

        if (is_bool($dirtyValue)){
            return $dirtyValue;
            //return $dirtyValue?1:0;
        }

        if (is_null($dirtyValue)){
            return null;
        }

        return filter_var($dirtyValue, $filterType, $flags);
    }

    static function json($dirtyValue){

        //attempt to decode as is
        $decodedJson = json_decode(($dirtyValue), JSON_OBJECT_AS_ARRAY);
        if (json_last_error() == JSON_ERROR_NONE){
            $array = self::array($decodedJson);
     
            //$array = sanitize::userInput($array);
            return json_encode($array);
        }

        //attempt to decode as after converting to basic
        $decodedJson = json_decode(utf8_encode($dirtyValue), JSON_OBJECT_AS_ARRAY);
        if (json_last_error() == JSON_ERROR_NONE){
            $array = self::array(json_decode(utf8_encode($dirtyValue), JSON_OBJECT_AS_ARRAY));
            return json_encode($array);
        }

        //attempt to detect and decode as after converting to utf8
        $decodedJson = json_decode(Encoding::fixUTF8(($dirtyValue)), JSON_OBJECT_AS_ARRAY);
        
        if (json_last_error() == JSON_ERROR_NONE){
            $array = self::array($decodedJson);
            return json_encode($array);
        }

        //huston we have a problem
        throw new Exception('Could not decode JSON to uft8');
        
    }

    static function array($array){

        if (is_bool($array)){
            return $array;
        }

        if (is_null($array)){
            return $array;
        }

        if (is_string($array)){
            return self::filter($array, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);;
        }

        array_walk_recursive($array, function (&$v) {
            $v = self::filter($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
        });

        return $array;
    }

    static function cleanHtml($html, $allowedTags=[]){
        if (empty($html)) {
            return $html;
        }
        return strip_tags($html, $allowedTags);
    }

    static function safeHtml(){
        return [
            "<a>",
            "<area>",
            "<b>",
            "<br>",
            "<div>",
            "<em>",
            "<h1>",
            "<i>",
            "<img>",
            "<li>",
            "<map>",
            "<ol>",
            "<p>",
            "<s>",
            "<span>",
            "<strong>",
            "<table>",
            "<tbody>",
            "<td>",
            "<th>",
            "<thead>",
            "<tfoot>",
            "<tr>",
            "<u>",
            "<ul>"
        ];
    }

    static function detectEncoding($string){

        debug(mb_detect_encoding($string));
    }


}