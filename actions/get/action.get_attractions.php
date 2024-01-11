<?php defined("isInSideApplication")?null:die('no access');

global $user;


$search = (array)json_decode($this->data['search'] ?? '[]');

if (!(user::hasAccess(array('admin', 'toc', 'rdg')))) {
    $search['vendor_id'] = $user->id;
}


$attractions = attraction::fetchAll($search);

if (user::hasAccess(array('toc'))) {
    $ignore = [
        'vendor'
    ];
}

$ignore = [];
if (user::hasAccess(array('rdg'))) {
    $ignore = [];
}
foreach ($attractions as &$attraction){
    foreach ($ignore as $ign){
        unset($attraction->{$ign});
    }
}

return $attractions;
