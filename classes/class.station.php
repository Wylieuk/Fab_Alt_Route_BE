<?php defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class station extends base_item{

    protected $type             = 'station';
    protected $table            = 'stations';


    public function __construct($data = []){
        $this->assign($data);
    }

    
    public static function fetchAll(array $search = []){

        global $config;

        foreach($search as $k => &$v){
            if(empty($v)){
                unset($search[$k]);
            }
        }

        $db = new db;

        $__this__ = new (get_called_class())([]);

        $allowedSearch = [
            "has_staged_changes" => "t1.`has_staged_changes` = :has_staged_changes",
        ];

        $searchSql = 
            (!empty($search) ? "\nWHERE" : '') . implode("\n AND ", array_intersect_key($allowedSearch, $search));

        $searchParams = 
            array_intersect_key($search, $allowedSearch);

  
        $data = $db->preparedQuery(
                    "SELECT 
                        *
                    FROM `{$__this__->table}` t1
                    {$searchSql}"
                    , $searchParams
                    //, true
                )->fetch_array() ?? [];

        foreach($data as &$row){
            $row['staged'] = json_decode($row['staged']);
            $row['live'] = json_decode($row['live']);
            $items[] =  new (get_called_class())($row);
        }

        return $items ?? [];

    }






}