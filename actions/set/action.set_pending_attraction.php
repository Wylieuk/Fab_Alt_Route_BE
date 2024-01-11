<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->dirtyData['attraction'])){
        throw new Exception("Missing parameter `attraction`");
}


$attraction = new attraction((array)json_decode($this->dirtyData['attraction']));


if(json_last_error() > 0){
    throw new Exception("Badly formed data JSON");
}

$orignalSubmittedAttraction = clone $attraction;

if($attraction->isDuplicated()){
    throw new Exception("Warning - attraction must have a unique `name`");
}

$attraction->setTable('attractions_pending');

//check the owner is logged in
if (!(user::hasAccess(array('admin')) || user::isSelf($attraction->vendor_id))) {
    throw new Exception('Insufficant Access Rights');
}


$images = [];
foreach ($attraction->images as $k => $v){
    $images[]      = $v;
}

unset($attraction->images);

$attraction->data = json_encode($attraction);

//find previous version of pending attraction
if(!empty($attraction->id)){
//   $previousVersion = attraction::fetch($attraction->id, '');
//   $differences     = array_functions::diff_recursive((array)$attraction, (array)$previousVersion);
//   $attraction->approved_version_id = $attraction->id;
//   $attraction->delete();
//   unset($attraction->id);


  $prevApprovedVersion = attraction::fetch($attraction->id, ''); 

  $prevPendingVersion = $prevApprovedVersion->pending_data;

  unset($prevApprovedVersion->diff);
  unset($prevApprovedVersion->pending_data);
  unset($prevApprovedVersion->timestamp);
  unset($prevApprovedVersion->vendor);
  unset($prevApprovedVersion->pending_id);
  unset($prevApprovedVersion->attraction_name);
  unset($prevApprovedVersion->live);
  unset($prevApprovedVersion->approved_version_id);


  foreach($orignalSubmittedAttraction->images as $k => $im){

    if(empty($orignalSubmittedAttraction->images->{$k}->data)){
        unset($orignalSubmittedAttraction->images->{$k}->meta);
    }

    unset($prevApprovedVersion->images[$k]['id']);
    unset($prevApprovedVersion->images[$k]['attraction_id']);
    unset($prevApprovedVersion->images[$k]['timestamp']);
  }

  $differences = array_map(fn($d) => current($d), array_functions::diff_recursive((array)$orignalSubmittedAttraction , (array)$prevApprovedVersion));


  if(empty($differences)){

    if(!empty($prevPendingVersion)){
        attraction::_delete($prevPendingVersion['id'], '_pending');
        return "Saved attraction {$attraction->id}";
    } else {
        return 'No changes made';
    }

  }

  $attraction->approved_version_id = $attraction->id;
  $attraction->delete();
  unset($attraction->id);

}
else{
    //create an empty entry
    $empty = new attraction(['vendor_id' => $attraction->vendor_id]);
    $attraction->approved_version_id = $empty->save();


    $differences = array_map(fn($d) => current($d), array_functions::diff_recursive((array)$orignalSubmittedAttraction , (array)$empty));
}


$attraction->id = $attraction->save();

$attraction->saveImages($images);

if (!(user::hasAccess(array('admin')))) {
    $attraction->sendAlert($differences ?? $attraction);
}

$this->response = "Saved attraction {$attraction->approved_version_id}";


global $user;
$log = new log([
    'component'    => 'attraction',
    'component_id' => $attraction->approved_version_id,
    'details'      => 'atraction updated',
    'user_id'       => $user->id
]);
$log->save();
