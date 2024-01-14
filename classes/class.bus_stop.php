<?php defined("isInSideApplication")?null:die('no access');

class bus_stop extends base_item{

    protected $type             = 'bus_stop';
    protected $table            = 'bus_stops';

    public function __construct($data){
        $this->assign($data);
    }



    public function purgeAll($target){
        //do nothing never purge
    }



}