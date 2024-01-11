<?php

defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class action {
	
    function __construct($_page=false){
        $this->page = $_page;	
    }

    function create($_action, $_data, &$page){
        $this->cacheTtl = false;

        $type = explode('_', $_action)[0] ?? '';

        $actionFile = file_exists('actions/action.'.$_action.'.php') ? 'actions/action.'.$_action.'.php' : (file_exists('actions/'.$type.'/action.'.$_action.'.php') ? 'actions/'.$type.'/action.'.$_action.'.php' : null);
        
        unset($this->page);
        
        global $event;

        if (file_exists($actionFile ?? '')){

            $_cache = new cache('action.'.$_action, $_data, true, $page);	
            $_actionOutput = $_cache->fetch($page->data['flush_cache'] ?? false);
            
            if (!$_actionOutput)
            {//start non-cache content
                foreach ($_data as $key => $val){
                    $this->$key = $val;
                }
                //echo '<!--actions/action.'.$_data['action'].'_'.$_data['form_id'].'.php-->';
                $response = require($actionFile);

                if($response != '1'){
                    $this->response = $response;
                }

                $_action_result = new stdClass;
                $_action_result->$_action = $this;
                $_actionOutput = $_action_result;

                $_cache->save(json_encode($_actionOutput ,JSON_PRETTY_PRINT), $this->cacheTtl);

            }//end non-cached content

            else{
                return json_decode($_actionOutput)->{$_action};
                //return json_decode(json_encode($_actionOutput))->{$_action};	
            }
            return $this->toObject($_actionOutput)->{$_action};
            //return json_decode(json_encode($_actionOutput))->{$_action};
        } 
        
        throw new Exception('Action `'. $_action .'` not found.');

    }

    private function toObject($collection){

        foreach($collection as &$section){
            if($this->containsColletion($section)){
                $section = $this->toObject($section);
            } else {
                $section = json_decode(json_encode($section));
            }

        }

        return $collection;
    }


    private function containsColletion($item){
        if (is_array($item) || is_object($item)){
            foreach ($item as $property) {
                if (is_array($property) || is_object($property)){
                    return true;
                }
            }
        }

        return false;
    }

    private function isAssoc(array $arr){
        if (array() === $arr) {return false;}
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
?>