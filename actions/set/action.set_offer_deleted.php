<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['offer_id'])){
    throw new Exception("Missing offer Id");
}

$offer = offer::fetch($this->data['offer_id']);

if (!(user::hasAccess(array('admin')) || user::isSelf($offer->vendor_id))) {
    throw new Exception('Insufficant Access Rights');
}


$offer->delete();

$this->response = "Offer Id: {$this->data['offer_id']} deleted";


global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => $this->data['offer_id'],
    'details'      => 'offer deleted',
    'user_id'       => $user->id
]);
$log->save();
