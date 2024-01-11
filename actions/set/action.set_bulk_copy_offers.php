<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['offer_ids'])){
    throw new Exception("Missing offer Ids");
}

$offer_ids = json_decode($this->data['offer_ids']);

if(!empty($this->data['substitutions'])){
    $substitutions = json_decode($this->data['substitutions'] ?? '{}');
}

$toSave = [];

$count = 0;

foreach($offer_ids as $offer_id){

    $offer = offer::fetch( $offer_id);


    if (!(user::hasAccess(array('admin')) || user::isSelf($offer->vendor_id))) {
        throw new Exception('Insufficant Access Rights');
    }

    foreach ($substitutions as $subKey => $subValue){
        $offer->{$subKey} = $subValue;
    }


    

    //init pending offer if there is data;
    if(!empty($offer->pending_data)){
        unset($offer->pending_data['id']);
        $pending_offer = new offer($offer->pending_data);
    }
    

    //strip out the ID and force a new one.
    unset($offer->id);

    //strip diff and pending from main offer.
    unset($offer->diff);
    unset($offer->pending_data);

    $offer->live = 0;
    $offer->id = $offer->save();
    $offer->saveImages($offer->images);


    if(!empty($pending_offer)){
        $pending_offer->setTable('offers_pending');
        $pending_offer->approved_version_id = $offer->id;

        $pending_offer->id = $pending_offer->save();

        $pending_offer->saveImages((array)($pending_offer->images ?? []));
    }

    unset($pending_offer);
    unset($offer);

    $count++;

}



global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => null,
    'details'      => 'multiple offers duplicated',
    'user_id'       => $user->id
]);
$log->save();


return "Successfully duplicated {$count} offers";