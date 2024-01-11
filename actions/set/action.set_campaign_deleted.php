<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['id'])){
    throw new Exception("Missing Id");
}

if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}



$campaign = new campaign(['id' => $this->data['id']]);

$campaign->delete();

global $user;
$log = new log([
    'component'    => 'campaign',
    'component_id' => $campaign->id ?? null,
    'details'      => 'campaign deleted',
    'user_id'       => $user->id
]);
$log->save();


return "Deleted Campaign ID: {$campaign->id}";