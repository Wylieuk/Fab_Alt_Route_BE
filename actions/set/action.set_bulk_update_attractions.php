<?php defined("isInSideApplication")?null:die('no access');

if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}

if(empty($this->data['attraction_ids'])){
    throw new Exception("Missing attraction Ids");
}

$attraction_ids = json_decode($this->data['attraction_ids']);

if(!empty($this->data['substitutions'])){
    $substitutions = json_decode($this->data['substitutions'] ?? '{}');
}

$toSave = [];

foreach($attraction_ids as $attraction_id){

    $attraction = attraction::fetch($attraction_id);

    if(!empty($attraction->pending_data)){
        throw new Exception("Cannot update attraction #{$attraction->id} as has unapproved pending changes.");
    }

    foreach ($substitutions as $subKey => $subValue){
        $attraction->{$subKey} = $subValue;
    }


    unset($attraction->diff);
    unset($attraction->pending_data);

    $toSave[] = $attraction;

    global $user;
    $log = new log([
        'component'    => 'attraction',
        'component_id' => $attraction_id,
        'details'      => 'aAttraction updated',
        'user_id'       => $user->id
    ]);
$log->save();

}

unset($attraction);
$count = 0;
foreach ($toSave as $attraction){
    $attraction->save();
    $attraction->approve();
    $count++;
}


return "Successfully updated {$count} attractions";