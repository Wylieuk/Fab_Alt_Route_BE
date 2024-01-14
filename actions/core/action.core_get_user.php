<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['id'])){
    throw new Exception("No User Id");
}

if (!(user::hasAccess(array('admin')) || user::isSelf($this->data['id']))) {
    throw new Exception('Insufficant Access Rights');
}

$userData = (object)user::getUserDetailsById($this->data['id'])[0];

// if(!in_array(user_group::getGroupName($userData->group_id), ['toc', 'vendor'])){
//     throw new Exception("This end point is only for vendors and TOCs");
// }

switch (user_group::getGroupName($userData->group_id)){


    case 'vendor':
        $user = vendor::get($this->data['id']);
    break;

    case 'toc':
        $user = toc::get($this->data['id']);
    break;

    case 'admin':
        $user = user::getUserDetailsById($this->data['id']);
    break;

    case 'rdg':
        $user = user::getUserDetailsById($this->data['id']);
    break;
    
    default:
        throw new Exception("Unknown type");
}


$this->response = $user;