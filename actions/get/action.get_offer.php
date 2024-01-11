<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['id'])){
    throw new Exception("Missing offer Id");
}


$offer = offer::fetch($this->data['id']);

$offer = offer::simplify($offer);

$offer->redemptions = redemption::fetch(
    $offer->id, 
    [
        'start' => date('Y-m-d H:i:s', time() - ONEYEAR), 
        'end' => date('Y-m-d H:i:s')
    ]
);

$this->response = $offer;


global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => $this->data['id'],
    'details'      => 'offer viewed',
    'user_id'       => $user->id
]);
$log->save();