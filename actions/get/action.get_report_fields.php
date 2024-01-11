<?php defined("isInSideApplication")?null:die('no access');

$fields = [];
$fields['user']         = array_values(vendor::getAllPossibleProperties());
$fields['attraction']   = array_values(attraction::getAllPossibleProperties());
$fields['offer']        = array_values(offer::getAllPossibleProperties());


sort($fields['user']);
sort($fields['attraction']);        
sort($fields['offer']);

//debug($fields);exit;

$ignore['user'] = [];
$ignore['attraction'] = [];
$ignore['offer'] = [];

if (user::hasAccess(array('rdg'))) {
    $ignore['user'] = [
       'id',
       'group_id',
       'enabled',
       'username'
    ];
    $ignore['attraction'] = [
        'id',
        'vendor_id',
    ];
    $ignore['offer'] = [
        'id',
        'attraction_id',
        'campaign_id',
        'live'
    ]; 
}

foreach ($ignore as $type => $ignArray){
    $fields[$type] = array_values(array_filter($fields[$type], fn($field) => !in_array($field, $ignArray)));
}


return $fields;