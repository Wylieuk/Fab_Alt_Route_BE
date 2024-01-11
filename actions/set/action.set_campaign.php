<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['campaign'])){
    throw new Exception("Missing campaign Id");
}

if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}



$campaign = new campaign((array)json_decode($this->data['campaign']));

$campaign->date_from = timestamp::db_format($campaign->date_from);
$campaign->date_to   = timestamp::db_format($campaign->date_to);

if(json_last_error() > 0){
    throw new Exception("Badly formed data JSON");
}

;
global $user;
$log = new log([
    'component'    => 'campaign',
    'component_id' => $campaign->id ?? null,
    'details'      => 'campaign updated',
    'user_id'       => $user->id
]);
$log->save();


return "saved Campaign ID: {$campaign->save()}";