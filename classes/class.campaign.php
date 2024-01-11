<?php defined("isInSideApplication")?null:die('no access');

class campaign extends base_item{

    protected $type              = 'campaign';
    protected $table             = 'campaigns';
    protected $imageTableSuffix  = null;


    static function getAll($active=null){

        $__class__ = get_called_class();

        $wheres = '';

        if (!empty($active)){
            $wheres = "WHERE `date_to` > CURDATE()";
        }

        $campaigns = [];

        $db = new db;
        foreach(($db->preparedQuery("SELECT * FROM `campaigns` {$wheres}", [])->fetch_array() ?? []) as $row){
            $campaigns[] = new $__class__($row);
        }

        return $campaigns;


    }

    static function fetch($id, $_unused=''){

        $db = new db;
        return new campaign(current($db->preparedQuery("SELECT * FROM `campaigns` WHERE `id` = :id", ['id' => $id])->fetch_array() ?? []));

    }


}