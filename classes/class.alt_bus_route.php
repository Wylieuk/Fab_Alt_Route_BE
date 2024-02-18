<?php defined("isInSideApplication")?null:die('no access');

class alt_bus_route extends base_item{

    protected $type             = 'alt_bus_route';
    protected $table            = 'alt_bus_routes';

    public function __construct($data){
        $this->assign($data);       
    }


    public function purgeAll($target){

        $db = new db;
        $db->preparedQuery("DELETE FROM `{$this->table}` WHERE `from_crs` = :target", ['target' => $target]);

    }





}