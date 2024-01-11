<?php defined("isInSideApplication")?null:die('no access');



#[AllowDynamicProperties]
class offer extends base_item{

    protected $type             = 'offer';
    protected $table            = 'offers';
    protected $imageTableSuffix = '_images';

    function get($id, $pending=''){
        global $config;
        $db = new db;
        $result = current($db->preparedQuery(
            "SELECT 
                t1.*,
                t2.`vendor_id` as 'vendor_id',
                t1.`campaign_id` as 'campaign_id',
                '{$pending}' as '_table',
                (SELECT `name` FROM `{$config['coreTablePrefix']}users` u1 WHERE t2.`vendor_id` = u1.`id` LIMIT 1) as 'vendor',
                (SELECT JSON_UNQUOTE(JSON_EXTRACT(ue1.`data`, '$.company_name')) FROM `{$config['coreTablePrefix']}users_extended` ue1 WHERE t2.`vendor_id` = ue1.`user_id` LIMIT 1) as 'company',
                (SELECT `id` FROM `{$this->table}_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1) as 'pending_id',
                (SELECT `data` FROM `attractions` t3 WHERE t3.id = t1.`attraction_id` LIMIT 1) as 'attraction_data'
            FROM `{$this->table}{$pending}` t1
            LEFT JOIN `attractions` t2 ON t2.`id` = t1.`attraction_id`
            WHERE 
                t1.`id` = :id
            ", ['id' => $id])->fetch_array() ?? []);

            if($result && !empty($result['attraction_data'])){
                $attraction_data = json_decode($result['attraction_data']);
                
                $result['attraction_name'] = $attraction_data->name;

                unset($result['attraction_data']);
            }


            //fix bools
            if($result){
                foreach(['allowOnlineBooking', 'genericPromoCode'] as $key){
                    if(in_array($result[$key], ['1','0'])){
                        $result[$key] = !!$result[$key];
                    }
                    if(in_array($result[$key], ['true', 'false'])){
                        $result[$key] = $result[$key] == 'true' ? true : false;
                    }
                }
            }
            
            return $result;
    }

    function isDuplicated(){
        $db = new db;

        return !empty($db->preparedQuery(
            "SELECT `id` FROM `{$this->table}` WHERE `campaign_id` = :campaign_id AND `name` = :name AND `id` != :id",
            [
                "campaign_id" => (!empty($this->campaign_id) ? $this->campaign_id : -1),
                "name" => ($this->name ?? null),
                "id" => (!empty($this->id) ? $this->id : -1),
            ]
        )
        ->fetch_array() ?? []);

    }

    function sendAlert($diffs){

        global $config;


        foreach(['vendor_id', 'data', 'approved_version_id'] as $ignore){
            unset($diffs[$ignore]);
        }

        
        foreach($diffs as &$diff){
            if (is_string($diff) &&  timestamp::isTimestamp($diff)){
                $diff = trim(timestamp::db_format($diff, 'd-m-Y'));
            }
        }
        

        $email = new email('set_pending_offer'); //will look in template for email.emailtype.tpl
        $email->assignBodyVars('offer', $this);
        $email->assignBodyVars('config', $config);
        $email->assignBodyVars('diffs', $diffs);
        $email->assignBodyVars('offerUrl', $_SERVER['FRONTEND_ROOT'] . '#vendor/' . $this->vendor_id . '/attractions/' . $this->attraction_id . '/offers/' . $this->approved_version_id);
        $email->setAddress('setFrom', $config['from_email'], 'Promo Toolkit');
        $email->setAddress('addAddress', $config['email_alert_target']);
        $email->setAttribute('Subject', 'Promo Toolkit new/updated submission/offer');
        $email->send();

    }

    static function getOwner($id){
        $db = new db;
        return current($db->preparedQuery(
            "SELECT 
                a.`vendor_id` 
            FROM 
                `offers` o
            LEFT JOIN `attractions` a ON a.`id` = o.`attraction_id`
            WHERE 
            o.`id` = :id"
        , ['id' => $id])
        ->fetch_array() ?? [])['vendor_id'] ?? -1;
    }


    function getImages($id, $pending){
        $db = new db;
        $result = $db->preparedQuery("SELECT * FROM `{$this->table}{$pending}{$this->imageTableSuffix}` WHERE `offer_id` = :offer_id", ['offer_id' => $id])->fetch_array() ?? [];

        foreach($result as &$row){
            $row['data'] = image_functions::blobToBase64($row['data'] ?? '');
        }

        return $result;
    }  

    static function fetchAll(array $search){

        if (isset($search['pending'])){
            $search['pending'] = (int)$search['pending'];
        }

        $limit = '';
        if(isset($search['limit'])){
            $limit = "\n LIMIT {$search['limit']}";
        }

        $order = '';
        if(isset($search['limit'])){
            $order = "\n ORDER BY t1.`{$search['order']}` DESC";
        }

        $db = new db;

        global $config;

        $items = [];

        $__class__ = get_called_class();

        $searchesToIgnore = [];

        $allowedSearch = [
            "id"                 => "t1.`id` = :id",
            "live"               => "t1.`live` = :live",
            "attraction_id"      => "t1.`attraction_id` = :attraction_id",
            "vendor_id"          => "a1.`vendor_id` = :vendor_id",
            "vendor"             => "u1.`name` LIKE CONCAT('%', :vendor, '%')",
            "name"               => "t1.name LIKE CONCAT('%', :name, '%')",
            "pending"            => "IFNULL((SELECT '1' FROM `offers_pending` t2 WHERE t1.id = `approved_version_id` LIMIT 1), '0') = :pending" ,
            "attraction_name"    => "a1.`name` LIKE CONCAT('%', :attraction_name, '%')" ,
            "offerType"          => "t1.`offerType` = :offerType",
            "campaign_id"        => "t1.`campaign_id` = :campaign_id",
            "category"           => "JSON_EXTRACT(t1.`data`, '$.category') LIKE CONCAT('%', :category, '%')",
            "postcode"           => "t1.postcode LIKE CONCAT(:postcode, '%')",                                                                     // campaign - campaign id
            "region"             => (function() use($search, $db, &$searchesToIgnore){
                                        $sql = [];
                                        if(!empty($search['region']) && is_array($search['region'])){
                                            foreach($search['region'] as $region){
                                                $sql[] = "JSON_EXTRACT(t1.`data`, '$.region') LIKE CONCAT('%', '{$db->clean($region)}', '%')";
                                            }
                                            $searchesToIgnore[] = 'region';
                                            return '(' .  implode(" OR ", $sql) . ')';
                                        } 
                                        else {
                                            return "JSON_EXTRACT(t1.`data`, '$.region') LIKE CONCAT('%', :region, '%')";
                                        }
                                    })(),
            "postcode"           => "t1.postcode LIKE CONCAT(:postcode, '%')",
            "closestStation"     => "JSON_EXTRACT(t1.`data`, '$.closestStation') LIKE CONCAT('%', :closestStation, '%')",
            "allowOnlineBooking" => "t1.allowOnlineBooking = :allowOnlineBooking",
            "genericPromoCode"   => "t1.genericPromoCode = :genericPromoCode",
        ];

        //unset($search['region']);

        //custom searchess
        switch (true){
            case ($search['offerType'] ?? null) == 'NOT FREE':
                $allowedSearch["offerType"] = "((t1.`offerType` != 'FREE' AND t1.`offerType` != :offerType) OR t1.`offerType` IS NULL)";
                break;
        }


        $searchSql = 
             implode(' AND ', array_intersect_key($allowedSearch, $search));

        $searchParams = 
            array_intersect_key($search, $allowedSearch);

        $searchSql = !empty($searchSql) ? "\n WHERE " . $searchSql : '';


        //fix up JSON SEARCHES
        foreach($searchParams as $k => &$v){
            if(strPos($allowedSearch[$k], 'JSON_CONTAINS') !== false){
               $v = '"'.$v.'"'; 
            }
        }

        //fix up BOOLEANS
        foreach($searchParams as $k => &$v){
            if(in_array($k, ['allowOnlineBooking', 'genericPromoCode'])){
                if(is_bool($v)){
                    $v = (($v ?? false) ? 'true' : 'false'); 
                }
            }
        }

        $_t = new $__class__([]);

        foreach ($searchesToIgnore as $ignore){
            unset($searchParams[$ignore]); 
        }

        //debug([$searchesToIgnore, $searchSql, $searchParams]);
       
     
        $results = $db->preparedQuery(

        "SELECT 
            t1.`id`,
            t1.`live` as 'live',
            IFNULL(op1.`campaign_id`, t1.`campaign_id`) as 'campaign_id',
            u1.`id` as 'vendor_id',
            u1.name as 'vendor',
            ue1.`data`as 'vendor_extended',
            a1.id as 'attraction_id',
            IFNULL(op1.`data`, t1.`data`) as 'offer_data',
            IFNULL(ap1.`data`, a1.`data`) as 'attraction_data',
            IFNULL(op1.`timestamp`, t1.`timestamp`) AS 'timestamp', 
            IF(op1.`id` IS NULL, 0, 1) as 'pending'
        FROM `{$_t->table}` t1
            LEFT JOIN `attractions` AS a1 ON t1.`attraction_id` = a1.`id`
            LEFT JOIN `attractions_pending` AS ap1 ON ap1.`approved_version_id` = a1.`id`
            LEFT JOIN `{$config['coreTablePrefix']}users` AS u1 ON a1.`vendor_id` = u1.`id`
            LEFT JOIN `offers_pending` op1 ON  op1.`approved_version_id` = t1.`id`
            LEFT JOIN `{$config['coreTablePrefix']}users_extended` ue1 ON a1.`vendor_id` = ue1.`user_id`
        {$searchSql}
        {$order}
        {$limit}
        ", $searchParams)->fetch_array() ?? [];

        

        if(empty($results)){
            return [];
        }

        foreach($results as $row){

            $row['vendor_extended'] = json_decode( $row['vendor_extended'] ?? '{}');

            $data = [
                'offer' => json_decode($row['offer_data'] ?? '[]'),
                'attraction' => json_decode($row['attraction_data'] ?? '[]')
            ];
            

            unset($row['offer_data']);
            unset($row['attraction_data']);

            foreach($data as $dk=>$dv){
                foreach($dv as $k=>$v){
                    $row[$dk.'_'.$k] = $v;
                }
            }
            //convert bools
            foreach(['pending', 'offer_allowOnlineBooking', 'offer_genericPromoCode'] as $key){
                
                if(in_array($row[$key], [1,0])){
                    $row[$key] = !!$row[$key];
                }
                if(in_array($row[$key], ['true', 'false'])){
                    $row[$key] = $row[$key] == 'true' ? true : false;
                }
            }

            $ignore = [
                //"offer_name",
                //"offer_offerType",
                //"offer_startDate",
                //"offer_endDate",
                "offer_offerTimes",
                "offer_exclusions",
                //"offer_address",
                //"offer_postcode	",
                "offer_closestStation",
                "offer_directions",
                "offer_copyForLeaflet",
                "offer_copyForWeb",
                "offer_visitorContactNumber",
                "offer_url",
                "offer_allowOnlineBooking",
                "offer_onlineBookingUrl",
                "offer_genericPromoCode",
                "offer_promoCode",
                "offer_accessibilityUrl",
                "offer_vendor_id",
                "offer_attraction_id",
                //"attraction_campaign",
                //"attraction_name",
                "attraction_category",
                "attraction_address",
                "attraction_postcode",
                "attraction_closestStation",
                "attraction_visitorContactNumber",
                "attraction_vendor_id",
            ];

            foreach($ignore as $ig){
                unset($row[$ig]);
            }
            
            $items[] = new $__class__($row);
        }

        return $items ?? [];
    }


}