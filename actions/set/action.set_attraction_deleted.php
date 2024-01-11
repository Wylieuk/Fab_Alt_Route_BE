<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['attraction_id'])){
    throw new Exception("Missing attraction Id");
}

$attraction = attraction::fetch($this->data['attraction_id']);

if (!(user::hasAccess(array('admin')) || user::isSelf($attraction->vendor_id))) {
    throw new Exception('Insufficant Access Rights');
}


$attraction->delete();

$this->response = "attraction Id: {$this->data['attraction_id']} deleted";


global $user;
$log = new log([
    'component'    => 'attraction',
    'component_id' => $this->data['attraction_id'],
    'details'      => 'attraction deleted',
    'user_id'       => $user->id
]);
$log->save();
