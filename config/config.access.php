<?php
defined("isInSideApplication")?null:die('no access');

//$config['requires_login']['check_credencials'] = false;


$config['requires_login']['home'] = false;
$config['requires_login']['get_login_token'] = false;
$config['requires_login']['get_progress'] = false;
$config['requires_login']['env'] = false;
$config['requires_login']['reset_password'] = false;
$config['requires_login']['logout'] = false;
$config['requires_login']['log_error'] = true;
$config['requires_login']['log_viewer'] = true;

$config['requires_login']['api'] = true;
$config['requires_login']['api_public'] = false;
$config['requires_login']['html_public'] = false;

$config['requires_login']['self_activate_user'] = false;
$config['requires_login']['cron_jobs'] = false;
$config['requires_login']['html'] = false;
$config['requires_login']['pdf'] = false;
$config['requires_login']['migration'] = false;


// locations
$config['group_access'] = 
    [
        'core_get_menu'                         => ['admin', 'user'],
        'core_get_user'                         => ['admin', 'user'],
        'core_set_update_user'                  => ['admin', 'user'],
        'core_get_password_check'               => ['admin', 'user'],
        'core_get_users'                        => ['admin', 'user'],
        'core_get_logs'                         => ['admin', 'user'],


        'set_station_alt_routes'                => ['admin', 'user'],
        'set_new_user'                          => ['admin'],

        'get_dashboard'                         => ['admin', 'user'],
        
    ];