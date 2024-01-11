<?php defined("isInSideApplication")?null:die('no access');

global $user;

$search = (array)json_decode($this->data['search'] ?? '[]');

if (!(user::hasAccess(array('admin', 'api_group', 'toc', 'rdg')))) {
    $search['vendor_id'] = $user->id;
}

if (user::hasAccess(array('toc', 'rdg'))) {
    $search['live'] = '1';
}



$offers = offer::fetchAll($search);


$ignore = [];

if (user::hasAccess(array('toc'))) {
    $ignore = [
        'vendor', 
        'pending',
        'live'
    ];
}

if (user::hasAccess(array('rdg'))) {
    $ignore = [
        'pending',
        'live'
    ];
}
foreach ($offers as &$offer){
    foreach ($ignore as $ign){
        unset($offer->{$ign});
    }
}


return $offers;