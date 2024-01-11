<?php defined("isInSideApplication")?null:die('no access');

$db = new db;

$categories = [];

foreach($db->query("SELECT `name` FROM `categories`")->fetch_array() ?? [] as $row){
    $categories[] = $row['name'];
}

return $categories;