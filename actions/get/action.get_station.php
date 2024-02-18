<?php defined("isInSideApplication")?null:die('no access');

if(!$this->data['crs'] ?? false){
    throw new Exception('CRS error');
}


return station::fetch($this->data['crs']);


