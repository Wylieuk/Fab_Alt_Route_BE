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
        'get_menu'                         => ['admin', 'vendor', 'toc', 'rdg'],
        'get_user'                         => ['admin', 'vendor', 'toc', 'rdg'],
        'set_update_user'                  => ['admin', 'vendor', 'toc', 'rdg'],
        'get_password_check'               => ['admin', 'vendor', 'toc', 'rdg'],
        'set_pending_attraction'           => ['admin', 'vendor'],
        'get_attractions'                  => ['admin', 'vendor', 'toc', 'rdg'],
        'get_attraction'                   => ['admin', 'vendor', 'toc', 'rdg'], 
        'set_attraction_approved'          => ['admin'],
        'set_attraction_rejected'          => ['admin'],    
        'set_pending_offer'                => ['admin', 'vendor'],
        'get_offers'                       => ['admin', 'vendor', 'toc', 'rdg', 'api_group'],        
        'get_offer'                        => ['admin', 'vendor', 'toc', 'rdg', 'api_group'], 
        'set_offer_approved'               => ['admin'],    
        'set_offer_rejected'               => ['admin'], 
        'get_users'                        => ['admin', 'vendor', 'toc', 'rdg'],
        'get_campaigns'                    => ['admin', 'vendor', 'toc', 'rdg'],
        'get_campaign'                     => ['admin', 'vendor', 'toc', 'rdg'],
        'set_campaign'                     => ['admin'],
        'get_pack'                         => ['admin', 'toc', 'rdg'],
        'set_offer_deleted'                => ['admin', 'vendor'],
        'set_attraction_deleted'           => ['admin', 'vendor'],
        'get_counties'                     => ['admin', 'vendor', 'toc', 'rdg'],
        'get_categories'                   => ['admin', 'vendor', 'toc', 'rdg'],
        'get_offer_types'                  => ['admin', 'vendor', 'toc', 'rdg'],
        'get_report_fields'                => ['admin', 'vendor', 'toc', 'rdg'],
        'set_bulk_copy_offers'             => ['admin', 'vendor'],
        'get_cms_pack'                     => ['admin', 'rdg'],
        'get_report'                       => ['admin', 'rdg'],
        'set_offer_live'                   => ['admin'],
        'get_logs'                         => ['admin', 'vendor', 'toc', 'rdg'],
        'get_dashboard'                    => ['admin', 'vendor', 'toc', 'rdg'],
        'set_redemptions'                  => ['admin', 'vendor'],
        'set_campaign_deleted'             => ['admin'],
        'job_migrate_users'                => ['admin'],
        'job_migrate_submissions'          => ['admin'],
        'job_retire_out_of_date_offers'    => ['api_group'],

    ];