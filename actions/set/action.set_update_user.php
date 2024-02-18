<?php defined("isInSideApplication")?null:die('no access');




if(empty($this->data['userData'])){
    throw new Exception('missing params `userData`');
}


$data = json_decode($this->data['userData']);

if(empty($data->id)){
    throw new Exception('missing user_id');
}

if (!(user::hasAccess(array('admin')) || user::isSelf($data->id))) {
    throw new Exception('Insufficant Access Rights');
}

$existingUser = (object)user::getUserDetailsById($data->id)[0] ?? null;

$data->group_id = $existingUser->group_id;

switch (user_group::getGroupName($data->group_id ?? null)){

    case 'manager':
        $id = manager::update((object)$data);
    break;

    default:
    throw new Exception("Unsupported user type `{$type}`");

}


$this->response = "User Id {$id} Updated";

