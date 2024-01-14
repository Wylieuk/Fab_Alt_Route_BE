<?php defined("isInSideApplication")?null:die('no access');

class log extends base_item{

    protected $type             = 'log';
    protected $table            = 'logs';
    protected $imageTableSuffix = '';

    function afterSave(){
        global $config;
        $this->purge($config['activity_log_retention_days']);
    }

    function beforeSave(){
        $this->ip_address = $_SERVER['REMOTE_ADDR'];
    }

    static function fetchAll(array $search){

        //$search['distinct'] = 1;

        global $config;

        $items = [];

        $limit = !empty($search['limit']) ? "\n LIMIT {$search['limit']}" : "";

        
        
        if(!empty($search['datetime_from'])){
            $search['datetime_from'] = timestamp::db_format($search['datetime_from']);
        }
        if(!empty($search['datetime_to'])){
            $search['datetime_to'] = timestamp::db_format($search['datetime_to']);
        }


        $allowedSearch = [
            "dashboard"     => "t1.`component` NOT IN ('user' , 'vendor') AND :dashboard = :dashboard ",
            "user_id"       => "(a1.`vendor_id` = :user_id OR a2.`vendor_id` = :user_id)",
            "component"     => "t1.`component` = :component",
            "not_component" => "t1.`component` != :not_component",
            "component_id"  => "t1.`component_id` = :component_id",
            "datetime_from" => "t1.`timestamp` >= :datetime_from",
            "datetime_to"   => "t1.`timestamp` <= :datetime_to",
            "details"       => "t1.`details` LIKE CONCAT('%',:details,'%')",
            "not_details"   => "t1.`details` NOT LIKE CONCAT('%',:not_details,'%')",
            "distinct"      => "t1.`id` = ( SELECT 
                                                `id` 
                                            FROM `logs` t2 
                                            WHERE 
                                                    `t1`.`component` = t2.`component`
                                                AND `t1`.`component_id` = t2.`component_id`
                                                AND `t1`.`details` = t2.`details`
                                                AND :distinct = :distinct
                                           ORDER BY t2.timestamp DESC
                                           LIMIT 1
                                        )"
            
        ];

        //custom searchess
        switch (true){
            case ($search['offerType'] ?? null) == 'NOT FREE':
                $allowedSearch["offerType"] = "((t1.`offerType` != 'FREE' AND t1.`offerType` != :offerType) OR t1.`offerType` IS NULL)";
                break;
        }


        $searchSql = implode("\n AND ", array_intersect_key($allowedSearch, $search));

        $searchSql = empty($searchSql) ? '' : "\nWHERE\n" . $searchSql;

        $searchParams = 
            array_intersect_key($search, $allowedSearch);

        $db = new db;

        $data = $db->preparedQuery(

            "SELECT 
                t1.id,
                t1.`details`,
                t1.`ip_address`,
                t1.`timestamp`,
                ".$db->jsonObject([
                            'id' => 'u1.`id`',
                            'name' => 'u1.`name`',
                            'username' => 'u1.`username`'
                        ])." 
                        AS 'user',
                CASE 
                WHEN a1.`id` IS NOT NULL
                    THEN ".$db->jsonObject([
                        'type' => 't1.`component`',
                        'id' => 'a1.`id`',
                        'vendor_id' => 'a1.`vendor_id`',
                        'name' => 'a1.`name`'
                    ])."
                WHEN o1.`id` IS NOT NULL
                    THEN ".$db->jsonObject([
                        'type' => 't1.`component`',
                        'id' => 'o1.`id`',
                        'campaign_id' => 'o1.`campaign_id`',
                        'attraction_id' => 'o1.`attraction_id`',
                        'vendor_id' => 'a2.`vendor_id`',
                        'name' => 'o1.`name`'
                    ])."
                WHEN c1.`id` IS NOT NULL
                    THEN ".$db->jsonObject([
                        'type' => 't1.`component`',
                        'id' => 'c1.`id`',
                        'name' => 'c1.`name`'
                    ])."
                WHEN u2.`id` IS NOT NULL
                    THEN ".$db->jsonObject([
                        'type' => 't1.`component`',
                        'group_id' => 'u1.`group_id`',
                        'id' => 'u2.`id`',
                        'name' => 'u2.`name`'
                    ])."
                ELSE ".$db->jsonObject([
                    'type' => 't1.`component`',
                    'id' => 't1.`component_id`',
                    'name' => '"deleted"'
                ])."
                END AS 'component'

            FROM `{$config['coreTablePrefix']}logs` t1
                LEFT JOIN `{$config['coreTablePrefix']}users` AS u1 ON t1.`user_id` = u1.`id`
                LEFT JOIN `attractions` AS a1 ON t1.`component` = 'attraction' AND a1.`id` = t1.`component_id`
                LEFT JOIN `offers` AS o1 ON t1.`component` = 'offer' AND o1.`id` = t1.`component_id`
                LEFT JOIN `attractions` AS a2 ON o1.`attraction_id` = a2.`id`
                LEFT JOIN `campaigns` AS c1 ON t1.`component` = 'campaign' AND c1.`id` = t1.`component_id`
                LEFT JOIN `{$config['coreTablePrefix']}users` AS u2 ON t1.`component_id` = u2.`id`
                {$searchSql}
            ORDER BY t1.`timestamp` DESC
                {$limit}
            ", $searchParams)->fetch_array() ?? [];


        $results = [];

        

        foreach($data as $row){

            $row['user'] = json_decode($row['user'] ?? 'NULL') ?? NULL;
            $row['component'] = json_decode($row['component'] ?? 'NULL') ?? NULL;

            $log = new log([]);
            $log->assign($row);
            $results[] = $log;

        }

        //debug($results);
        return $results;


    }
}