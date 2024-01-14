<?php defined("isInSideApplication")?null:die('no access');



#[AllowDynamicProperties]
class base_item{
    
    protected $type  = 'default';
    protected $table = 'default';


    public function __construct(array $props = null){
        if($props !== null){
            foreach ($props as $k => $v){
                if($v !== null) {
                    $this->{$k} = $v;
                }
            }
        } else {
            throw new Exception('No data passed to object constructor');
        }
    }

    public function assign(array $props){
        foreach ($props as $k => $v){
            $this->{$k} = $v;
        }
    }

    protected function setTable(string $table){
        $this->table = $table;
    }

    public function getType(){
        return $this->type;
    }


    public function beforeSave(){

    }

    public function save($withDataField = true, $withTimeStamp = false) {

        $db = new db;

        $this->beforeSave();
        
        if(!$withTimeStamp){
            unset($this->timestamp);
        }


        $query = $db->build_insert($this->table, (array)$this);

        $db->preparedQuery($query['statement'], $query['values']);

        $this->afterSave();

        return $db->insert_id(); 
    }

    public function afterSave(){

    }


    public function delete($id=null, $pending=''){
        $id = $id ?? $this->id;
        $db = new db;
        $db->preparedQuery("DELETE FROM `{$this->table}` WHERE `id` = :id LIMIT 1", ['id' => $id]);
    }

    public static function _delete($id=null, $pending=''){
        $__class__ = get_called_class();
        $item = new $__class__([]);
        $item->delete($id, $pending);
    }

    public function purgeAll($target){
 
    }
    
    

}