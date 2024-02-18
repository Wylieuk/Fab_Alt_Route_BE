<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['id'])){
    throw new Exception("No User Id");
}

if (!(user::hasAccess(array('admin')) || user::isSelf($this->data['id']))) {
    throw new Exception('Insufficant Access Rights');
}

$userData = (object)user::getUserDetailsById($this->data['id'])[0];


switch (user_group::getGroupName($userData->group_id)){


    case 'admin':
        $user = user::getUserDetailsById($this->data['id']);
    break;

    case 'manager':
        $user = user::getUserDetailsById($this->data['id']);
    break;
    
    default:
        throw new Exception("Unknown type");
}


$this->response = current($user ?? []);