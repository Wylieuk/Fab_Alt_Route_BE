<?php defined("isInSideApplication")?null:die('no access');

$auth = new authenticate;

if(empty($this->data['id']) || empty($this->data['_username']) || empty($this->data['_password'])){
    throw new Exception('Incomplete credicials passed');
}

if (!(user::hasAccess(array('admin')) || user::isSelf($this->data['id']))) {
    throw new Exception('Insufficant Access Rights');
}

$this->response = $auth->checkCredentials($this->data['_username'], $this->data['_password']);