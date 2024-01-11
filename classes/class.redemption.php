<?php defined("isInSideApplication")?null:die('no access');

    

class redemption extends base_item{

    protected $type              = 'redemptions';
    protected $table             = 'redemptions';
    protected $imageTableSuffix  = null;



    function save($withDataField = true, $withTimeStamp = false) {

        $this->beforeSave();

        $db    = new db;
        
        $query = $db->build_insert($this->table, (array)$this);

        $db->preparedQuery($query['statement'], $query['values']);

        $this->afterSave();

        return $db->insert_id();

    }

    static function fetch($offerId, $dateRange=[]){
        $__class__ = get_called_class();
        $item = new $__class__([]);
        $db = new db;
        return (int)current($db->preparedQuery("SELECT SUM(count) AS 'count'  FROM `{$item->table}` WHERE `timestamp` <= :end  AND `timestamp` >= :start AND `offer_id` = :offerId", ['offerId' => $offerId, 'start' =>  $dateRange['start'], 'end' => $dateRange['end']])->fetch_array() ?? [])['count'] ?? 0;
    }

    static function fetchCount($search){

        global $config;

        $items = [];

        $allowedSearch = [
            "id"            => "r.`id` = :id",
            "vendor_id"     => "u.`id` = :vendor_id",
            "offer_id"      => "r.`offer_id` = :offer_id",
            "datetime_from" => "r.`timestamp` >= :datetime_from",
            "datetime_to"   => "r.`timestamp` <= :datetime_to",
        ];

        //custom searchess
        switch (true){
            case ($search['offerType'] ?? null) == 'NOT FREE':
                $allowedSearch["offerType"] = "((t1.`offerType` != 'FREE' AND t1.`offerType` != :offerType) OR t1.`offerType` IS NULL)";
                break;
        }


        $searchSql = implode(' AND ', array_intersect_key($allowedSearch, $search));

        $searchSql = empty($searchSql) ? '' : "\nWHERE\n" . $searchSql;

        $searchParams = 
            array_intersect_key($search, $allowedSearch);

        $db = new db;

        return current($db->preparedQuery( 
            "SELECT 
                    sum(r.`count`) as 'count' 
                FROM `redemptions` r
                LEFT JOIN `offers` o ON o.`id` = r.`offer_id`
                LEFT JOIN `attractions` a ON a.`id` = o.`attraction_id`
                LEFT JOIN `{$config['coreTablePrefix']}users` AS u ON a.`vendor_id` = u.`id`
                {$searchSql}",
                $searchParams
            )->fetch_array() ?? [])['count'] ?? null;
            
    }





}