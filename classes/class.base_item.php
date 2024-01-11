<?php defined("isInSideApplication")?null:die('no access');



#[AllowDynamicProperties]
class base_item{
    
    protected $type              = 'default';
    protected $table             = 'default';
    protected $imageTableSuffix  = '_images';


    function __construct(array $props = null){
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

    function assign(array $props){
        foreach ($props as $k => $v){
            $this->{$k} = $v;
        }
    }

    function setTable(string $table){
        $this->table = $table;
    }

    function getType(){
        return $this->type;
    }


    function beforeSave(){

    }

    function save($withDataField = true, $withTimeStamp = false) {

        $db = new db;

        $this->beforeSave();
        
        if(!$withTimeStamp){
            unset($this->timestamp);
        }

        if($withDataField){
            $data = clone $this;

            $primaryFields = [
                'id',
                'vendor',
                'vendor_id',
                'timestamp',
                'images',
                'attraction_id',
                'campaign_id',
                'approved_version_id'
            ];

            foreach ($primaryFields as $field){
                unset($data->{$field});
            }

            $this->data = $this->data ?? json_encode($data); 
            
            if($this->data == '{}'){
                unset($this->data);
            }
        
        } 


        $query = $db->build_insert($this->table, (array)$this);

        $db->preparedQuery($query['statement'], $query['values']);

        $this->afterSave();

        return $db->insert_id(); 
    }

    function afterSave(){

    }

    function purge($dayAge=null){

        $db = new db;

        switch(true){

            case empty($dayAge):
                $db->query("TRUNCATE {$this->table}");
                break;

            case !empty($dayAge) && is_int($dayAge):
                $db->preparedQuery("DELETE FROM {$this->table} WHERE `timestamp` < NOW() - INTERVAL :dayAge DAY" , ['dayAge' => $dayAge]);
                break;
        }


    }

    function sendAlert($diffs){
        //default
        return null;
    }

    function saveImages(array $images = [] ){
        $db = new db;
        $toSave = [];
        $insertCount = 0;


        $db->preparedQuery("DELETE FROM `{$this->table}{$this->imageTableSuffix}` WHERE `{$this->type}_id` = :type_id", ['type_id' => $this->id]);

        foreach ($images as $image){

            $image = (object)$image;

            if(empty($image->data)){
                $toSave = (array)[
                    'data' => null,
                    'name' => $image->name,
                    'meta' => '{}',
                ];
            }
            else {
                $toSave = (array)[
                    'data' => image_functions::base64ToBlob(image_functions::base64FitToConstraints(
                        base64Blob: $image->data, 
                        maxWidth: 1920, 
                        maxHeight:1920,
                        quality: 70
                    )),
                    'name' => $image->name,
                    'meta' => json_encode($image->meta),
                ];

            }


            $toSave[$this->type.'_id'] = $this->id;

            $query = $db->build_insert($this->table.$this->imageTableSuffix , $toSave);
            $db->preparedQuery($query['statement'], $query['values']);
            $insertCount += $db->affected_rows();
        }

        return $insertCount == count($images);
    }

    function delete($id=null, $pending=''){
        $id = $id ?? $this->id;
        $db = new db;
        $db->preparedQuery("DELETE FROM `{$this->table}{$pending}` WHERE `id` = :id LIMIT 1", ['id' => $id]);
    }

    static function _delete($id=null, $pending=''){
        $__class__ = get_called_class();
        $item = new $__class__([]);
        $item->delete($id, $pending);
    }
    

    function approve(){

        $__class__ = get_called_class();

        $db = new db;

        $db->startTransaction();

        $toSave = new $__class__($this->pending_data);

        $toSave->id = $toSave->approved_version_id;

        $toSave->saveImages((array)$toSave->images);
        $toSave->save();

        $this->delete($this->pending_data['id'], '_pending');

        $db->commit();
    }

    function reject(){
        //delete pending item
        $this->delete($this->pending_data['id'], '_pending');

        //delete  null data parent item
        $db = new db;
        $db->preparedQuery("DELETE FROM {$this->table} WHERE `id` = :id AND `data` IS NULL", ['id' => $this->id]);
    }

    function get($id, $pending=''){
        // default
        return null;
    }

    function getImages($id, $pending){
        // default 
        return null;
    }

    static function getOwner($id){
        // default
        return null;
    }

    static function getAllPossibleProperties(){
        $__class__ = get_called_class();
        $item = new $__class__([]);

        $db = new db;

        $props = [];

        foreach ($db->query("SELECT * FROM `{$item->table}`")->fetch_array() ?? [] as $row){
            foreach(json_decode($row['data'] ?? '{}') as $dataKey => $dataRow){
                $props[] = $dataKey;
            }
            unset($row['data']);
            foreach($row as $key => $value){
                $props[] = $key;
            }
        }

        if($item->table == 'offers'){
            $props[] = 'redemptions';
            $props[] = 'redemptions/month';
        }

        $ignoreList = [
            "_table"
        ];

        return array_diff(array_unique($props), $ignoreList);

    }


    static function fetch($id, $pending=''){
        
    
        $__class__ = get_called_class();

        $item = new $__class__([]);

        $db = new db;

        $result = $item->get($id, $pending);

        if(empty($result) || !$result){
            return null;
        }

        $data = json_decode($result['data'] ?? '{}');

        unset($data->_table);

        unset($result['data']);

        foreach($data as $k=>$v){
            if(empty($result[$k])){
                $result[$k] = $v;
            }
        }

        if(!empty($result['region']) && is_string($result['region'])){
            $result['region'] = json_decode($result['region']);
        }

    
        $images = $item->getImages($id, $pending);
        

        $result['images'] = [];
        foreach($images ?? [] as $k=>$v){
            $v['meta'] = json_decode($v['meta'] ?? '{}');
            if(!empty($v['id'])){
               $result['images'][$k] = $v; 
            }
        }

        $result['images'] =  array_combine(array_map(fn($e) => $e['name'] ,$result['images']), array_values($result['images']));

        foreach(['region', 'category'] as &$array){
            if (!empty($result[$array]) && is_string($result[$array])){
                $result[$array] = [$result[$array]];
            }
        }

        //convert ints
        foreach(['vendor_id'] as $key){
            if(!empty($result[$key])){
                $result[$key] = (int)$result[$key];
            }
        }

        //convert bools
        foreach([] as $key){
            if(!empty($result[$key])){
                $result[$key] = !!$result[$key];
            }
        }

        $pending_data = [];

        if($result['_table'] != '_pending'){
            //fetch the pending data

            $pending_data = (array)json_decode(json_encode($__class__::fetch($result['pending_id'], '_pending')));

            if(!empty($pending_data) ){
                $pending_data['id'] = $result['pending_id'];

                //debug([$result, $pending_data]);
                $result['diff']     = array_functions::diff_recursive(
                                                                    array1: (array)$pending_data, 
                                                                    array2: (array)$result,
                                                                    ignoredkeys: [
                                                                            "id", 
                                                                            "attraction_id", 
                                                                            "approved_version_id", 
                                                                            "timestamp", 
                                                                            "pending_id" , 
                                                                            "_table",
                                                                            "offer_id"                                                                        
                                                                        ]
                                                                );
                $result['pending_data'] = $pending_data;
            } 
            else {
                $result['diff'] = null;
                $result['pending_data'] = null;
            }
            
            
        }   

        $item->assign($result);

        return $item;
        
    }

    static function simplify($item){

        //Use diff as pending by scrub orrignal value
        $pending_data   = array_map(fn($i) => current($i), array_intersect_key(($item->diff ?? []), ($item->pending_data ?? [])));

        foreach(($item->diff['images'] ?? []) as $k1 => $img1){
            $dif_images[$k1] = json_encode($img1);
        }

        foreach(($item->pending_data['images'] ?? []) as $k2 => $img2){
            $pending_images[$k2] = json_encode($img2);
        }


        $item->pending_data = $pending_data;

        $item->pending_data['images'] = array_intersect_key(($pending_images ?? []), ($dif_images ?? []));

        foreach (($item->pending_data['images'] ?? []) as $k=>$img){
            $item->pending_data['images'][$k]  = json_decode($img);

        }

        foreach (($item->diff ?? []) as $k => $v){
            $diff[$k] = true;
        }

        foreach (($item->diff['images'] ?? []) as $k => $v){
            $diffImages[$k] = true;
        }

        $item->diff = $diff ?? null;
        if(!empty($diffImages)){
            $item->diff['images'] = $diffImages;
        }

        if(empty($item->pending_data['images'])){
            unset($item->pending_data['images']);
        }

        if(empty($item->pending_data)){
            $item->pending_data = null;
        }

        unset($item->diff);

        return $item;
    }

}