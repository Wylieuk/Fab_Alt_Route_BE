<?php defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class station extends base_item{

    protected $type             = 'station';
    protected $table            = 'stations';


    public function __construct($data = []){
        $this->assign($data);       
    }

    public static function fetch(string $crs){

     
        $db = new db;

        $__this__ = new (get_called_class())([]);
    
        $data = $db->preparedQuery(
            "SELECT 
                *
            FROM `{$__this__->table}` t1
            WHERE `crs` = :crs
            LIMIT 1
            ",
            ["crs" => $crs]
        )->fetch_array() ?? [];
    
        if(empty($data)){
            return null;
        }

        $data = $data[0];


        $data['station_name'] = ucwords(strtolower($data['station_name'] ?? ''), '-( ') ;
        $data['staged'] = json_decode($data['staged']);
        $data['live']   = json_decode($data['live']);

        return new (get_called_class())($data);
    }

    public static function setLive($station){
        $station = new (get_called_class())((array)$station);
        $station->live = $station->staged;
        return $station->save();
    }

    public static function setApproved($station){
        $station = new (get_called_class())((array)$station);
        $station->approved = 1;
        return $station->save();
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
                    {$searchSql}
                    ORDER BY `station_name`"
                    , $searchParams
                    //, true
                )->fetch_array() ?? [];

        foreach($data as &$row){

            $row['station_name'] = ucwords(strtolower($row['station_name'] ?? ''), '-( ') ;

            $row['staged'] = json_decode($row['staged']);
            $row['live'] = json_decode($row['live']);
            $items[] =  new (get_called_class())($row);
        }

        return $items ?? [];

    }






}