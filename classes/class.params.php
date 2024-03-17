<?php defined("isInSideApplication")?null:die('no access');

class params extends base_item{

    public function __construct($data){
        $this->assign($data);
    }

    public function purgeAll($target){
        //do nothing never purge
    }



}