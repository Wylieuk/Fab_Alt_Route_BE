<?php defined("isInSideApplication")?null:die('no access');

if(!$this->data['station'] ?? false){
    throw new Exception('Station error');
}

$station = json_decode($this->data['station']);

return station::setApproved($station);