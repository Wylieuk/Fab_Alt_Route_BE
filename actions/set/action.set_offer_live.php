<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['offer_id'])){
    throw new Exception("Missing offer_id");
}


if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}

$existing_offer = offer::fetch($this->data['offer_id']);

if(empty($existing_offer)){
    throw new Exception('Offer not found');
}


$offer = new offer([
    'id' => $this->data['offer_id'],
    'live' => $this->data['status'] == 'online' ? 1 : 0
]);

$offer->save(withDataField: false);

global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => $this->data['offer_id'],
    'details'      => $this->data['status'] == 'online' ? 'offer set live online' : 'offer set live offline',
    'user_id'       => $user->id
]);
$log->save();

return "Set offer live status to {$this->data['status']}";