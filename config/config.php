<?php
defined("isInSideApplication")?null:die('no access');

ini_set('display_errors', 'on');

$config = [];

$config['env']                      = 'production'; //show extended error data in json error property

$config['default_twitter_author']   = 'se_railway';

$config['purgeMailAfterDays']       = 1; //days

$config['toc_code']                 = 'SE';


/*
* Section: ERROR REPORTING LEVEL
***************************************/
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);


/*
* Section: CONSTANTS
***************************************/
define("ONEYEAR", 1*24*60*60*365);
define("ONEMONTH", 1*24*60*60*30);
define("ONEWEEK", 1*24*60*60*7);
define("ONEDAY", 1*24*60*60);
define("ONEHOUR", 1*60*60);
define("ONEMINUTE", 1*60);
define("FIXED_CYPHER_KEY" , 'This_Persephone3_5123ASDASDUTR(*)VVV');
define("VARIABLE_CYPHER_KEY", base64_encode(date('FY'))); // key that can come from anywhere
define("VARIABLE_SERVER_CYPHER_KEY", 'drm_'.base64_encode(date('WFmY'))); // key that originates from the server
define("TOMORROW_2AM", date("Y-m-d", strtotime('tomorrow')). ' 02:00:00');
define("TODAY_2AM", date("Y-m-d", time()). ' 02:00:00');
define("MAX_DATE", '2035-01-01 12:00:00');


$config['feConfigFile'] = 'config/fe_config.cnf';

/*
* Section: mailbox
**************************************/


/*
* Section: END POINTS
***************************************/


/*
* Section: JWT CONFIG
***************************************/
$config['JSON_WEB_TOKEN_KEY'] 					= '@_h3llfir3_1885_SkuLL1060_!!->Brut3F0rceTh15'; // jwt key
$config['JSON_WEB_TOKEN_STORAGE'] 			    = 'COOKIE'; //where to store the jwt client side
$config['JSON_WEB_TOKEN_EXPIRATION']            = 60*60*12; //3600seconds =  1 hour then log out


/*
* Section: CROSS SITE SECURITY
***************************************/
$config['sameSiteCookie']                      = 'none';
$config['allow_cors']                          = true; //enable when used as a back back end (server to server api) or in dev, set to false in production
$config['CspNonce']                            = bin2hex(random_bytes(16));

/*
* Section: COOKIE BRUTE FORCE
***************************************/
$config['enableBruteForceProtection']           = true;
$config['bruteForceCount']                      = '5'; // how many login atttempts to trigger a lockout
$config['bruteForceTime']                       = '30'; // time after which login count is reset
$config['bruteforceLockoutTime']                = '120'; // time user locked out if triggered


/*
* Section: GENERAL AUTHENTICATION
***************************************/
$config['site_requires_login']			        = true; //enable authentications
$config['auth_type']                            = 'db';
$config['validatePasswordStrength']             = true;


/*
* Section: 2 FACTOR AUTHENTICATION
***************************************/
$config['2faEnabled']                           = true;
$config['2faChannel']                           = 'email'; // email/sms

//twilio settings (sms)
$config['2faBaseUrl']                           = 'https://verify.twilio.com/v2/';
$config['2faTwilioServiceId']                   = 'VAe62ec2ffb50c3a58e7b27357bc2c4cb5';
$config['2faAccountSid']                        = 'AC096ec2d6f9e303871e2cf162c6ee2e27';
$config['2faAuthToken']                         = 'cbc84e01d0e84f588c3cd613c3cf72e7';
// qG_8pWvHoBHlKcyv1hEqastTcm3HpDAH33XgTFri

/*
* Section: IP CONTROL
***************************************/
$config['restrictLoginToIps']                   = false; //enable white list access
$config['useIpAddressBinding']                  = false; // log user out if IP changes during session

/*
* Section: IP WHITELIST
***************************************/
$config['blockAllExceptIps'][]                  = '192.168.10.161'; //whitelisted ips


/*
* Section: WEB ROOT
***************************************/
//echo PHP_SAPI;
if (PHP_SAPI !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
    $config['documentroot']                     = $_SERVER['DOCUMENT_ROOT'] . 
                                                    substr(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), 0, 
                                                    strrpos(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '/')).'/';

    $config['siteaddress']                      = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' .$_SERVER['HTTP_HOST']. 
                                                    substr(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), 0, 
                                                    strrpos(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '/'));
    
    $config['origin']                           = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' .$_SERVER['HTTP_HOST'];

}
else{
    $config['documentroot'] = '/';
     $config['siteaddress'] = '';
}


/*
* Section: DATABASE
***************************************/
$config['server']						        = 'localhost';
$config['database']								= "alt_routes";
$config['database_login']					    = "root";
$config['database_password']				    = "36223";
$config['search_size_limit'] 				    = 20000;
$config['coreTablePrefix']                      = '_core_';
$config['appTablePrefix']                       = '';


/*
* Section: LOGs
***************************************/
$config['error_log']			 		        = 'logs/error_log.log';
$config['error_log_frontend']                   = 'logs/error_log_frontend.log';
$config['error_log_count']					    = 30;
$config['error_log_stacktrace_depth']           = 2;
$config['logHandledErrors']                     = false;
$config['write_log']               				= true;
$config['show_log']								= false;
$config['csp_log']                              = true;
$config['csp_log_file']                         = '../logs/csp-violations.log';
$config['csp_log_file_size']                    = 500;
$config['authentication_log']                   = 'logs/authenticate.log';
$config['user_access_log']                      = true;
$config['user_access_log_retention']            = 30;//days


/*
* Section: FOLDERS
***************************************/
$config['image_folder']						    = $config['siteaddress'].'/assets/images/';

/*
* Section: FORMATS
***************************************/
$config['date_format']						    = 'd\/m\/y H:i';
$config['friendly_date']						= 'D jS F Y';


/*
* Section: CACHE
***************************************/
$config['cache']						        = false;
$config['cache_folder']						    = 'cache';
$config['cache_lifetime']						= 180; //secs
$config['compressCache']                        = true;


/*
* Section: EMAIL
***************************************/
$config['enableOutgoingEmail']                  = true;
$config['debugEmail']                           = false;
$config['email_errors']                         = false;
$config['email_fatal_errors']                   = false;
$config['email_errors_target']                  = 'clint@net-key.co.uk';
$config['email_alert_target']                   = 'clint@net-key.co.uk';

$config['from_email']                           = 'no_reply@fab_alt_routes.co.uk';
$config['enableRootSmarty']                     = false;
// $config['email_frontend_url']                 = 'https://www.promotoolkit.co.uk/#/';

/*
* Section: INCIDENT COMPONENTS
***************************************/

$config['refresh_closed_components_hours']      = 6;

/*
* Section: ERROR HANDLING
***************************************/
$config['show_errors'] 							= true; //also needs display errors enabled
$config['debug_errors'] 						= true;
$config['write_debug_errors']     			    = true;
$config['stopOnError']							= true;
$config['limit_email_error_alerts']             = 5; //1 email for same error per x minutes, set to 0 to email ALL errors


/*
*
* Section: DEBUG
***************************************/
$config['curl_debug'] 							= false;
$config['debug_db_query']					    = false;
$config['debug_only_slow_queries']		        = false;
$config['logUserAccessToDB']                    = true;
$config['debug']						        = true;
$config['show_comments']                        = true;
$config['debug_event']                          = false;


/*
* Section: APPLICATION SPECIFIC
***************************************/
$config['activity_log_retention_days']          =  365;