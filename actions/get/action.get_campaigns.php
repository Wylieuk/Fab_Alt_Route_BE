<?php defined("isInSideApplication")?null:die('no access');

return campaign::getAll($this->data['active'] ?? null);