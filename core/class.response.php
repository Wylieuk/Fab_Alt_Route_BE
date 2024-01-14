<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class response {

    static function isModified($page_data, $output){

        $page_data = (array)$page_data;
        $output = (array)$output;

        if(isset($page_data['token']) && $page_data['token'] && !$output['error'])
        {
            if($output['token'] !== $page_data['token'])
            {
                return true;
            }  else {
                return false;
            }
        }
        return true;
    }

}