<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['offer_id'])){
    throw new Exception("Missing offer Id");
}

$offer = offer::fetch($this->data['offer_id']);

if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}

if(empty($offer) || empty($offer->id)){
    throw new Exception("Offer not found");
}

if(empty($offer->pending_data)){
    throw new Exception("Pending offer not found");
}


$offer->reject();

$this->response = "Offer Id: {$this->data['offer_id']} rejected";


global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => $this->data['offer_id'],
    'details'      => 'oOffer rejected',
    'user_id'       => $user->id
]);
$log->save();