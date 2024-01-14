<?php defined("isInSideApplication")?null:die('no access');

$search = (array)json_decode($this->data['search'] ?? '{}');

if (!user::hasAccess(array('admin'))) {
    global $user;
    $search['vendor_id'] = $user->id;
}

$users = user::getAll($search);


$ignore = [];

if (!user::hasAccess(array('admin'))) {
    $ignore = [
        'username',
        'enabled',
    ];
}

foreach ($users as &$user){
    foreach ($ignore as $ign){
        unset($user[$ign]);
    }
}
return $users;