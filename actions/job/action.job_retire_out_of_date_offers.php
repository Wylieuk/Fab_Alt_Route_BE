<?php defined("isInSideApplication")?null:die('no access');

$db = new db;

$query = "UPDATE `offers` SET `live` = '0' WHERE `end_date` < CURDATE() AND `live` = '1'";

$db->query($query);

return "Successfully set offline {$db->affected_rows()} offers";