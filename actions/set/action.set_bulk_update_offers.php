<?php defined("isInSideApplication")?null:die('no access');

if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}

if(empty($this->data['offer_ids'])){
    throw new Exception("Missing offer Ids");
}

$offer_ids = json_decode($this->data['offer_ids']);

if(!empty($this->data['substitutions'])){
    $substitutions = json_decode($this->data['substitutions'] ?? '{}');
}

$toSave = [];

foreach($offer_ids as $offer_id){

    $offer = offer::fetch($offer_id);

    if(!empty($offer->pending_data)){
        throw new Exception("Cannot update offer #{$offer->id} as has unapproved pending changes.");
    }

    foreach ($substitutions as $subKey => $subValue){
        $offer->{$subKey} = $subValue;
    }


    unset($offer->diff);
    unset($offer->pending_data);

    $toSave[] = $offer;

    global $user;
    $log = new log([
        'component'    => 'offer',
        'component_id' => $offer_id,
        'details'      => 'offer updated',
        'user_id'       => $user->id
    ]);
    $log->save();

}


unset($offer);
$count = 0;
foreach ($toSave as $offer){
    $offer->save();
    $offer->approve();
    $count++;
}


return "Successfully updated {$count} offers";