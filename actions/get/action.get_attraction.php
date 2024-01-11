<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['id'])){
    throw new Exception("Missing attraction Id");
}

$attraction = attraction::fetch($this->data['id']);

$attraction = attraction::simplify($attraction);


$ignore = [];
if (user::hasAccess(array('toc'))) {
    $ignore = [
        'vendor'
    ];
}

if (user::hasAccess(array('rdg'))) {
    $ignore = [];
}

foreach ($ignore as $ign){
    unset($attraction->{$ign});
}

$this->response = $attraction;


global $user;
$log = new log([
    'component'    => 'attraction',
    'component_id' => $this->data['id'],
    'details'      => 'attraction viewed',
    'user_id'       => $user->id
]);
$log->save();

