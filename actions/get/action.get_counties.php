<?php defined("isInSideApplication")?null:die('no access');

$db = new db;

$counties = [];

foreach($db->query("SELECT `name` FROM `counties`")->fetch_array() ?? [] as $row){
    $counties[] = $row['name'];
}

return $counties;