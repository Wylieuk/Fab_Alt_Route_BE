<?php defined("isInSideApplication")?null:die('no access');

global $user;


$search = (array)json_decode($this->data['search'] ?? '[]') ?? [];
$search['datetime_from'] = $search['datetime_from'] ?? timestamp::db_format(time() - ONEMONTH);
$search['datetime_to']   = $search['datetime_to'] ?? timestamp::db_format(time());

if (!user::hasAccess(array('admin'))) {
    $search['dashboard'] = true;
}

return array_values(array_filter(
    log::fetchAll( search: $search ?? []), 
    fn($log) => user::hasAccess(array('admin', 'rdg')) || $log->component->type::getOwner($log->component->id) == $user->id
));