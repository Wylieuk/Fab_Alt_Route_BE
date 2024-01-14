<?php defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class array_functions{ 

    static function diff_recursive(array $array1, array $array2, array $ignoredkeys = [], ){
        $result = [];

        $array1 = json_decode(json_encode($array1), JSON_OBJECT_AS_ARRAY);
        $array2 = json_decode(json_encode($array2), JSON_OBJECT_AS_ARRAY);
        
        foreach ($array1 as $a1k=>$a1v){

            if(in_array($a1k, $ignoredkeys)){
                continue; 
            }

            if(empty($array1[$a1k]) && empty($array2[$a1k])){
                continue;
            }

            if(empty($array2[$a1k])){
                $result[$a1k] = [$a1v, null];
                continue;
            }

            if(is_array($array1[$a1k] ?? null) && is_array($array2[$a1k] ?? null)){
                $result[$a1k] = self::diff_recursive(($array1[$a1k] ?? null), ($array2[$a1k] ?? null), $ignoredkeys);
                if(empty($result[$a1k])){
                    unset($result[$a1k]);
                }
                continue;
            }

            if(($array1[$a1k] != $array2[$a1k])){
                $result[$a1k] = [$array1[$a1k], $array2[$a1k]];
                continue;
            }
        }
            
        foreach ($array2 as $a2k=>$a2v){

            if(in_array($a2k, $ignoredkeys)){
                continue; 
            }

            if(empty($array2[$a2k]) && empty($array1[$a2k])){
                continue;
            }

            if(empty($array1[$a2k])){
                $result[$a2k] = [null, $a2v];
                continue;
            }

            if(is_array($array1[$a2k] ?? null) && is_array($array2[$a2k] ?? null)){
                $result[$a2k] = self::diff_recursive(($array1[$a2k] ?? null), ($array2[$a2k] ?? null), $ignoredkeys);
                if(empty($result[$a2k])){
                    unset($result[$a2k]);
                }
                continue;
            }

            if(($array2[$a2k] != $array1[$a2k])){
                $result[$a2k] = [$array1[$a2k], $array2[$a2k]];
                continue;
            }
            
        }
        

        return $result;
    }


    static function array_intersect_key_recursive(&$arr1, &$arr2) {
        if (!is_array($arr1) || !is_array($arr2)) {
    //      return $arr1 == $arr2; // Original line
            return (string) $arr1 == (string) $arr2;
        }
        $commonkeys = array_intersect_key($arr1, $arr2);
        debug($commonkeys);
        $ret = array();
        foreach ($commonkeys as $key) {
            $ret[$key] = self::array_intersect_key_recursive($arr1[$key], $arr2[$key]);
        }
        return $ret;
    }


}