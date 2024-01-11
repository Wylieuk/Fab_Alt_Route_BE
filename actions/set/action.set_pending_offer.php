<?php defined("isInSideApplication")?null:die('no access');



//print_r($this->dirtyData['offer']);exit;

if(empty($this->dirtyData['offer'])){
        throw new Exception("Missing parameter `offer`");
}

$offer = new offer((array)json_decode($this->dirtyData['offer']));

$orignalSubmittedOffer = clone $offer;

if(json_last_error() > 0){
    throw new Exception("Badly formed data JSON");
}

if(empty($offer->attraction_id)){
    throw new Exception("Missing parameter `attraction_id`");
}

if($offer->isDuplicated()){
    throw new Exception("Warning - offer must have a unique `offer name` and `campaign`");
}


$offer->setTable('offers_pending');

//check the owner is logged in
if (!(user::hasAccess(array('admin')) || user::isSelf($offer->vendor_id))) {
    throw new Exception('Insufficant Access Rights');
}


$images = [];
foreach ($offer->images as $k => $v){
    $images[]      = $v;
}

unset($offer->images);
$offer->data = json_encode($offer);

//find previous version of pending offer
if(!empty($offer->id)){
  $prevApprovedVersion = offer::fetch($offer->id, ''); 

  $prevPendingVersion = $prevApprovedVersion->pending_data;

  unset($prevApprovedVersion->diff);
  unset($prevApprovedVersion->pending_data);
  unset($prevApprovedVersion->timestamp);
  unset($prevApprovedVersion->vendor);
  unset($prevApprovedVersion->pending_id);
  unset($prevApprovedVersion->attraction_name);
  unset($prevApprovedVersion->live);
  unset($prevApprovedVersion->approved_version_id);


  foreach($orignalSubmittedOffer->images as $k => $im){

    if(empty($orignalSubmittedOffer->images->{$k}->data)){
        unset($orignalSubmittedOffer->images->{$k}->meta);
    }

    unset($prevApprovedVersion->images[$k]['id']);
    unset($prevApprovedVersion->images[$k]['offer_id']);
    unset($prevApprovedVersion->images[$k]['timestamp']);
  }

  $differences = array_map(fn($d) => current($d), array_functions::diff_recursive((array)$orignalSubmittedOffer , (array)$prevApprovedVersion));


  if(empty($differences)){

    if(!empty($prevPendingVersion)){
        offer::_delete($prevPendingVersion['id'], '_pending');
        return "Saved offer {$offer->id}";
    } else {
        return 'No changes made';
    }

  }

  $offer->approved_version_id = $offer->id;
  $offer->delete();
  unset($offer->id);

}
else{
    //create an empty entry
    $empty = new offer(['attraction_id' => $offer->attraction_id]);
    $offer->approved_version_id = $empty->save();

    $differences = array_map(fn($d) => current($d), array_functions::diff_recursive((array)$orignalSubmittedOffer, (array)$empty));
}

$offer->id = $offer->save();
$offer->saveImages($images);

if (!(user::hasAccess(array('admin')))) {
    $offer->sendAlert($differences ?? $offer);
}


$this->response = "Saved offer {$offer->approved_version_id}";


global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => $offer->approved_version_id,
    'details'      => 'offer updated',
    'user_id'       => $user->id
]);
$log->save();