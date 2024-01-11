<?php
defined("isInSideApplication")?null:die('no access');

/*
* t: Gets menu items based on 'user group id'
***************************************/

global $user;

$m = new menu;

if ($user !== null){
    $m->getMenu($user->group_id);
}



$this->response = $m->items;