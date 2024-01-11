<?php defined("isInSideApplication")?null:die('no access');



#[AllowDynamicProperties]
class attraction extends base_item{

    protected $type             = 'attraction';
    protected $table            = 'attractions';
    protected $imageTableSuffix = '_images';

    function get($id, $pending=''){
        global $config;
        $db = new db;

        return current($db->preparedQuery(
            "SELECT 
                t1.*,
                '{$pending}' as '_table',
                (SELECT `name` FROM `{$config['coreTablePrefix']}users` u1 WHERE t1.`vendor_id` = u1.`id` LIMIT 1) as 'vendor',
                (SELECT JSON_UNQUOTE(JSON_EXTRACT(ue1.`data`, '$.company_name')) FROM `{$config['coreTablePrefix']}users_extended` ue1 WHERE t1.`vendor_id` = ue1.`user_id` LIMIT 1) as 'company',
                (SELECT `id` FROM `{$this->table}_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1) as 'pending_id'
            FROM `{$this->table}{$pending}` t1
            WHERE 
                t1.`id` = :id
            ", ['id' => $id])->fetch_array() ?? []);
    }

    function getImages($id, $pending){
        $db = new db;
        $result = $db->preparedQuery("SELECT * FROM `{$this->table}{$pending}{$this->imageTableSuffix}` WHERE `attraction_id` = :attraction_id", ['attraction_id' => $id])->fetch_array() ?? [];

        foreach($result as &$row){
            $row['data'] = image_functions::blobToBase64($row['data'] ?? '');
        }

        return $result;
    } 

    function isDuplicated(){
        $db = new db;

        return $db->preparedQuery(
            "
             SELECT * FROM `{$this->table}_pending` 
             WHERE 
                 `vendor_id` = :vendor_id 
             AND `name` = :name 
             AND `approved_version_id` != :id
            ",
            [
                "vendor_id" => (!empty($this->vendor_id) ? $this->vendor_id : -1),
                "name" => ($this->name ?? null),
                "id" => (!empty($this->id) ? $this->id : -1),
            ]
        )
        ->fetch_array() ?? [];

    }

    function sendAlert($diffs){

        global $config;

        foreach(['vendor_id', 'data', 'approved_version_id'] as $ignore){
            unset($diffs[$ignore]);
        }

        $email = new email('set_pending_attraction'); //will look in template for email.emailtype.tpl
        $email->assignBodyVars('attraction', $this);
        $email->assignBodyVars('config', $config);
        $email->assignBodyVars('diffs', $diffs);
        $email->assignBodyVars('attractionUrl', $_SERVER['FRONTEND_ROOT'] . '#vendor/' . $this->vendor_id . '/attractions/' . $this->approved_version_id);
        $email->setAddress('setFrom', $config['from_email'], 'Promo Toolkit');
        $email->setAddress('addAddress', $config['email_alert_target']);
        $email->setAttribute('Subject', 'Promo Toolkit new/updated attraction');
        $email->send();

    }

    static function getOwner($id){
        $db = new db;
        return current($db->preparedQuery("SELECT `vendor_id` FROM `attractions` WHERE `id` = :id", ['id' => $id])->fetch_array() ?? [])['vendor_id'] ?? -1;
    }


    static function fetchAll(array $search){

        foreach($search as $k => &$v){
            if(empty($v)){
                unset($search[$k]);
            }
        }

        global $config;

        $db = new db;

        $__class__ = get_called_class();

        $allowedSearch = [
            "live"            => "t1.`id` = (SELECT `attraction_id` FROM `offers` WHERE `attraction_id` = t1.`id` AND `live` = :live LIMIT 1)",
            "id"              => "t1.`id` = :id",
            "vendor_id"       => "t1.`vendor_id` = :vendor_id",
            "vendor"          => "u1.name LIKE CONCAT('%', :vendor, '%')",
            "name"            => "t1.name LIKE CONCAT('%', :name, '%')",
            "campaign_id"     => "JSON_CONTAINS(t1.`data`, :campaign_id, '$.campaign_id')",
            "pending"         => "IFNULL((SELECT 'true' FROM `attractions_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1), 'false') = :pending",
            "category"        => "JSON_EXTRACT(t1.`data`, '$.category') LIKE CONCAT('%', :category, '%')",
            //"region"          => "t1.region LIKE CONCAT('%', :region, '%')",
            "region"             => (function() use($search, $db, &$searchesToIgnore){
                $sql = [];
                if(!empty($search['region']) && is_array($search['region'])){
                    foreach($search['region'] as $region){
                        $sql[] = "t1.`region` LIKE CONCAT('%', '{$db->clean($region)}', '%')";
                    }
                    $searchesToIgnore[] = 'region';
                    return '(' .  implode(" OR ", $sql) . ')';
                } 
                else {
                    return "t1.`region` LIKE CONCAT('%', :region, '%')";
                }
            })(),
            "postcode"        => "t1.`postcode` LIKE CONCAT(:postcode, '%')",
            "closestStation"  => "JSON_EXTRACT(t1.`data`, '$.closestStation') LIKE CONCAT('%', :closestStation, '%')",
        ];

        $searchSql = 
             implode("\n AND ", array_intersect_key($allowedSearch, $search));

        $searchParams = 
            array_intersect_key($search, $allowedSearch);

        

        //fix up JSON SEARCHES
        foreach($searchParams as $k => &$v){
            if(strPos($allowedSearch[$k], 'JSON_CONTAINS') !== false){
               $v = '"'.$v.'"'; 
            }
        }

        //fix up BOOLEANS
        foreach($searchParams as $k => &$v){
            if(in_array($k, ['pending'])){
               $v = (($v ?? false) ? 'true' : 'false'); 
            }
        }

        foreach (($searchesToIgnore ?? []) as $ignore){
            unset($searchParams[$ignore]); 
        }


        $_t = new $__class__([]);

        //if(!empty($searchParams)){
            $data = $db->preparedQuery(

                "SELECT 
                    t1.`id`, 
                    t1.`vendor_id`,
                    u1.`name`as 'vendor', 
                    ue1.`data`as 'vendor_extended', 
                    IFNULL(t1.`data`, (SELECT `data` FROM `{$_t->table}_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1)) as 'data',
                    t1.`timestamp`, 
                    IFNULL((SELECT '1' FROM `{$_t->table}_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1), '0') as 'pending',
                    (SELECT count(`id`) FROM `offers` WHERE `attraction_id` = t1.`id` AND live = '1') AS 'offer_count'
                FROM `{$_t->table}` t1
                LEFT JOIN `{$config['coreTablePrefix']}users` u1 ON t1.`vendor_id` = u1.`id`
                LEFT JOIN `{$config['coreTablePrefix']}users_extended` ue1 ON t1.`vendor_id` = ue1.`user_id`
                ".(!empty($searchSql) ? " WHERE" : "")."
                    {$searchSql}
                UNION ALL
                SELECT 
                    t1.`id`, 
                    t1.`vendor_id`,
                    u1.`name`as 'vendor', 
                    ue1.`data`as 'vendor_extended', 
                    t1.`data`,
                    t1.`timestamp`, 
                    '1' as 'pending',
                    (SELECT count(`id`) FROM `offers` WHERE `attraction_id` = t1.`id`) AS 'offer_count'
                FROM `{$_t->table}_pending` t1
                LEFT JOIN `{$config['coreTablePrefix']}users` u1 ON t1.`vendor_id` = u1.`id`
                LEFT JOIN `{$config['coreTablePrefix']}users_extended` ue1 ON t1.`vendor_id` = ue1.`user_id`
                WHERE 
                        t1.`approved_version_id` IS NULL
                        ".(!empty($searchSql) ? " AND " : "") ."
                        {$searchSql}"
                , $searchParams)->fetch_array() ?? [];

        // }else{

        //     $data = $db->preparedQuery(
        //         "SELECT 
        //             t1.`id`, 
        //             t1.`vendor_id`,
        //             u1.`name`as 'vendor', 
        //             IFNULL(t1.`data`, (SELECT `data` FROM `{$_t->table}_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1)) as data,
        //             t1.`timestamp`, 
        //             IFNULL((SELECT '1' FROM `{$_t->table}_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1), '0') as 'pending',
        //             (SELECT count(`id`) FROM `offers` WHERE `attraction_id` = t1.`id`) AS 'offer_count'
        //         FROM `{$_t->table}` t1
        //         LEFT JOIN `{$config['coreTablePrefix']}users` u1 ON t1.`vendor_id` = u1.`id`
        //         UNION ALL
        //         SELECT 
        //             t1.`id`, 
        //             t1.`vendor_id`,
        //             u1.`name`as 'vendor', 
        //             t1.`data`,
        //             t1.`timestamp`, 
        //             '1' as 'pending',
        //             (SELECT count(`id`) FROM `offers` WHERE `attraction_id` = t1.`id`) AS 'offer_count'
        //         FROM `{$_t->table}_pending` t1
        //         LEFT JOIN `{$config['coreTablePrefix']}users` u1 ON t1.`vendor_id` = u1.`id`
        //         WHERE 
        //                t1.`approved_version_id` IS NULL
        //         ", [])->fetch_array() ?? [];

        // }

    //debug($data); exit;
        

        if(empty($data)){
            return [];
        }

        foreach($data as $row){

            $row['vendor_extended'] = json_decode( $row['vendor_extended'] ?? '{}');

            $data = json_decode($row['data'] ?? '[]');
            unset($row['data']);

            foreach($data as $k=>$v){
                $row[$k] = $v;
            }

            //convert bools
            foreach(['pending'] as $key){
                $row[$key] = !!$row[$key];
            }
            
            $items[] = new $__class__($row);
        }

        return $items;
    }

}