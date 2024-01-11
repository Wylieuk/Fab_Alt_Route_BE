<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['id'])){
    throw new Exception("Missing campaign Id");
}

if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}


return campaign::fetch($this->data['id']);