<?php defined("isInSideApplication")?null:die('no access');

class rrbs extends base_item{

    protected $type             = 'rrbs';
    protected $table            = 'rrbs';

    public function __construct($data){
        $this->assign($data);
    }


    public function purgeAll($target){

        $db = new db;
        $db->preparedQuery("DELETE FROM `{$this->table}` WHERE `from_crs` = :target", ['target' => $target]);

    }





}