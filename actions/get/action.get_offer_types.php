<?php defined("isInSideApplication")?null:die('no access');

$db = new db;

$offer_types = [];

foreach($db->query("SELECT `type` FROM `offer_types`")->fetch_array() ?? [] as $row){
    $offer_types[] = $row['type'];
}

return $offer_types;