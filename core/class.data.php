<?php


defined("isInSideApplication")?null:die('no access');


class data {
    /*
    * usage: example of structure
    * usage:     stdClass Object (
    * usage:         [configuration] => stdClass Object (
    * usage:                 [name] => 'String' 
    * usage:                 [project] => 'Number' 
    * usage:                 [contexts] => Array() <- specify empty object or array to include all
    * usage:                 [ruleGroups] => stdClass Object () 
    * usage:                 [previousNightTimes] => 'booleen'
    * usage:             )
    * usage:          [services] => Array ( <- array just add one array element to include all entries that are the same in the returned array
    * usage:                [0] => stdClass Object (
    * usage:                      [meta] => stdClass Object (
    * usage:                              [uid] => String
    * usage:                              [dbId] => String
    * usage:                              [flags] => stdClass Object (
    * usage:                                   [origin] => stdClass Object () <- specifing a property wil include only what is specified
    * usage:                                   [runsTo] => String
    * usage:                              )
    * usage:                              [callingPoints] => stdClass Object () <- emtpy object will include all object properties include all
    * usage:                         )
    * usage:
    * usage:                  )
    * usage:
    * usage:            )
    * usage:      )


    * code example::-  
            if (isset($this->data['apiResponseStructure']) && isset($output['response'])){
               $output['response'] = data::limitStructure($output['response'], json_decode($this->data['apiResponseStructure']));
            }


    * note: anything that is missing from the structure wilL be removed from the returned data
    * note: 'String'/'Boolean'/'Number' are just any string values as long as it is set to something
    * note: with an array structure using only one array element in the input json will include repeating stucture in the output
    ***************************************/
	static public function limitStructure($data, $structure){

        if($data === NULL || $structure === NULL){
            return NULL;
        }

        if(is_string($structure)){
            $structure = json_decode($structure);
        }

        if (count((array)$structure) === 0){
            return $data;
        }
        if (is_array($structure)){
            $reoccuringStructure = true; //always use the first array element from a structure array element
        } else {
            $reoccuringStructure = false; 
        }

        if (is_object($structure)){
            $structure = (array)$structure;
        }           

        if (!is_array($data) && !is_object($data)){
            return $data;
        }

        foreach ($data as $key => $element) {

            if ($reoccuringStructure){
                $sKey = 0; //always use the first array element from a structure array element
            } else {
                $sKey = $key;
            }

            if (!isset($structure[$sKey])){
                
                switch (true){

                    case is_object($data):
                        if (isset($data->{$key}) || is_null($data->{$key})){
                            unset($data->{$key});
                        }
                    break;

                    case is_array($data):
                        if (isset($data[$key]) || is_null($data[$key])){
                            unset($data[$key]);
                        }
                    break;
                    
                    default:
                }
            } else {

                if (is_array($structure[$sKey]) && count($structure[$sKey]) === 0){
                    continue;
                }

                if (is_object($structure[$sKey]) && count((array)$structure[$sKey]) === 0){
                    continue;
                }

                switch (true){

                    case is_object($data):
                        $data->{$key} = self::limitStructure($element, $structure[$sKey]);
                    break;

                    case is_array($data):
                        $data[$key] = self::limitStructure($element, $structure[$sKey]);
                    break;                    
                }
            }
        }
        return $data;
    }

    static function createToken($data){
       return encryption::medHash(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    static function modified($passedToken, $newToken){
        if(isset($passedToken) && $passedToken)
        {
            if($passedToken == $newToken)
            {
                return false;
            }
        }
        return true;
    }


}