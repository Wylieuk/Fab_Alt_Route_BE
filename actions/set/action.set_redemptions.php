<?php defined("isInSideApplication")?null:die('no access');



//debug($this->data);

if(empty($this->data['redemptions'])){
    throw new Exception("Missing parameter `redemptions`");
}

if(empty($this->data['date'])){
    throw new Exception("Missing parameter `date`");
}

$date = timestamp::db_format($this->data['date']);


if(empty($date)){
    throw new Exception("Badly formed of missing `date`");
}

$db = new db;

foreach ((json_decode($this->data['redemptions']) ?? []) as $_redemption ){

    if($_redemption->count < 1){
        continue;
    }

    $vendor_id = current($db->preparedQuery(
            "SELECT 
                a.`vendor_id` as `vendor_id`
            FROM `offers` o 
            LEFT JOIN `attractions` a on a.`id` = o.`attraction_id`
                WHERE 
                o.`id` = :id", 
            ['id' => $_redemption->offer_id])
            ->fetch_array())['vendor_id'] ?? 0;

    //check the owner is logged in
    if (!(user::hasAccess(array('admin')) || user::isSelf($vendor_id))) {
        throw new Exception('Insufficant Access Rights');
    }

    if(json_last_error() > 0){
        throw new Exception("Badly formed `redemptions data JSON`");
    }
    
    $redemption = new redemption([
        'offer_id' => $_redemption->offer_id,
        'count' => $_redemption->count,
        'timestamp' => $date
    ]);
    $redemption->save(false);
    $redemptions[] = $redemption;
}

$redemption->purge(365*5);



$count = count($redemptions);

global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => $_redemption->offer_id,
    'details'      => "redemptions added [$count]",
    'user_id'       => $user->id
]);
$log->save();

return "saved {$count} redemptions";