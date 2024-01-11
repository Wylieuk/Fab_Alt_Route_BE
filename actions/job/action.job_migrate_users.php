<?php defined("isInSideApplication")?null:die('no access');


if (!(user::hasAccess(array('admin')))) {
    throw new Exception('Insufficant Access Rights');
}

$old_db    = new db(server: 'localhost', database: 'old_toolkit', username: 'root', password: '36223');
$db_target = new db(server: 'localhost', database: 'awesome_2for1_migration_target', username: 'root', password: '36223');

/*
* t: get usergroups[]
***************************************/
$user_groups = [];
foreach(
    $old_db->preparedQuery(
                "SELECT 
                `id`,
                `group_name`
                FROM `user_groups`"
            )
            ->fetch_array() ?? []
            as $row
){
    $user_groups[] = [
        'id' => $row['id'],
        'group_name' => $row['group_name']
    ];
}

$query = $db_target->buildbulkInsert('_core_user_groups', $user_groups);
$db_target->preparedQuery($query['statement'], $query['values']);


/*
* t: get users[]
* t: get users_extended[]
***************************************/
$users = [];
$users_extended = [];
foreach(
    $old_db->preparedQuery(
                "SELECT 
                    `id`,
                    `username`,
                    `email`,
                    `name`,
                    `job_title`,
                    `password`,
                    `phone_number`,
                    0 AS 'enable_2fa',
                    `group_id`,
                    `enabled`,
                    `last_login`,
                    ".$old_db->jsonObject([
                        'address' => '`address`',
                        'post_code' => '`postcode`'
                    ])." AS users_extended_data
                FROM `users`"
            )
            ->fetch_array() ?? []
            as $row
){
    
    $users_extended[] = [
        'user_id' => $row['id'],
        'data' => $row['users_extended_data'],
    ];

    unset($row['users_extended_data']);

    $users[$row['id']] = $row;
}




$query = $db_target->buildbulkInsert('_core_users', $users);
$db_target->preparedQuery($query['statement'], $query['values']);

$query = $db_target->buildbulkInsert('users_extended', $users_extended);
$db_target->preparedQuery($query['statement'], $query['values']);




die('OK');