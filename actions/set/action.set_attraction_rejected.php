<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['attraction_id'])){
    throw new Exception("Missing attraction Id");
}

$attraction = attraction::fetch($this->data['attraction_id']);

if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}

if(empty($attraction) || empty($attraction->id)){
    throw new Exception("Attraction not found");
}

if(empty($attraction->pending_data)){
    throw new Exception("Pending attraction not found");
}


$attraction->reject();

$this->response = "Attraction Id: {$this->data['attraction_id']} rejected";


global $user;
$log = new log([
    'component'    => 'attraction',
    'component_id' => $this->data['attraction_id'],
    'details'      => 'attraction rejected',
    'user_id'       => $user->id
]);
$log->save();
