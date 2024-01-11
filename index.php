<?php

define("isInSideApplication", true);

header("Access-Control-Allow-Origin: null");
header("Strict-Transport-Security:max-age=31536000");


$commandlineParams = (function () {
	global $argv;
	if (isset($argv)) {
		foreach ($argv as $arg) {

			if (strpos($arg, '--') !== false) {
				$arg 	= trim($arg, '-');
                $key = explode('=', $arg)[0];
                $val = explode('=', $arg)[1];
                $_commandlineParams[$key] = $val;
			}

		}
	}
    return $_commandlineParams ?? [];
})();

$_REQUEST = array_merge($_REQUEST, $commandlineParams);



date_default_timezone_set('Europe/London');

if(ini_get('zlib.output_compression')){ 
	ini_set('zlib.output_compression', 'Off'); 
}

require_once('config/config.php');

$currentCookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $currentCookieParams["lifetime"],
    'path' => dirname($_SERVER['PHP_SELF']) . '/',
    'domain' => $currentCookieParams["domain"],
    'secure' => true, // $currentCookieParams["secure"],
    'httponly' => true,
    'samesite' => $config['sameSiteCookie']
]);
session_start();



$_SESSION['time_start'] = microtime(true);
//use browser cookie to control stack errors
if (isset($_SERVER['LOCAL_ADDR']) and !isset($_COOKIE['XDEBUG_TRACE'])){
	if ($_SERVER['LOCAL_ADDR'] == '192.168.10.205' && extension_loaded('xdebug') && function_exists('xdebug_disable')) {
		if (!isset($_COOKIE['XDEBUG_SESSION'])){
			xdebug_disable();
		}
	}
}

//xdebug_disable();



require_once('config/config.libs.php');
//auto load classes from class folder
spl_autoload_register(function ($class_name) {
    
	global $config;
	//echo $class_name.'<br>';
	if (is_file('core/class.'.$class_name.'.php')){
    	require_once ('core/class.'.$class_name . '.php');
	}

	if (is_file('classes/class.'.$class_name.'.php')){
    	require_once ('classes/class.'.$class_name . '.php');
    }
});
if (empty($_REQUEST['page'])){
    params_decrypt($_REQUEST);
    params_decrypt($_POST);
}

if(isset($_REQUEST['page']) && $_REQUEST['page'] != 'get_progress'){
    $progress = new progress;
    $progress->reset();
}

require_once('functions/function.include_files.php');

$event = new event;

$old_error_handler = set_error_handler("error_handler");
register_shutdown_function('fatalErrorShutdownHandler');

if (isset($_REQUEST['api'])){ $_REQUEST['page'] = $_REQUEST['api'];}
if (!isset($_REQUEST['page'])){ $_REQUEST['page'] = '';}


if($config['enableRootSmarty']){$smarty = new_smarty();}

$_SESSION['securityToken'] = encryption::medHash($config['siteaddress'] . session_id());
if ($config['site_requires_login'] && page::requiresAccess($_REQUEST['page'])){
	if (!$jwt_token = login_check()){

		if (!isset($_SESSION['request']['page']) and isset($_REQUEST['page'])){
			if ($_REQUEST['page'] != 'login' and $_REQUEST['page'] != ''){
				//set session[request'] to later use to redirect after login to original page
				$_SESSION['request'] = $_REQUEST;
				unset($_SESSION['request']['password']);
				unset($_SESSION['request']['username']);
				unset($_SESSION['request']['PHPSESSID']);
				//end---------------------------------------------
			}
		}
	//debug($jwt_token)	;
		//debug($_REQUEST);
		$login_atempted = '';
		if(isset($_REQUEST['username'])){
			$login_atempted = '&login=failed';	
		}
		if (@$_REQUEST['page'] != 'login'){

            headers::accessControlAsRefer();
            headers::allowCredencials();

			header("Content-Security-Policy: default-src 'self' 'unsafe-inline'");
			header("X-Frame-Options: deny");
			header("X-Content-Type-Options: nosniff");
			header("X-XSS-Protection: 1");
            //echo '<script>window.location = "index.php?page=login'.$login_atempted.'";</script>';
            header("HTTP/1.1 403 Forbidden");
            echo 'Not Authorised';
			exit;
		}
		
		//$_POST['page'] = 'login';
	}else{
		//assign session[request'] to use to redirect to original page once logged in 
		if(isset($_SESSION['request'])){
			$queryArray = $_SESSION['request'];
		}else{
			$queryArray = array();
		}
        if(!empty($queryArray)){
		    unset($queryArray['PHPSESSID']);
        }
		$_SESSION['request'] = false;
		//end---------------------------------------------	
	
		
		$JWT      = new JWT($config['JSON_WEB_TOKEN_KEY']);
		$userData = $JWT->read($jwt_token);
		$user     = new user;

		foreach($user->loggedOnUser($jwt_token) as $k => $v){
			$user->{$k} = $v;
		};
		unset($user->JWTData);

	}
}


session_write_close(); // close the session to stop this script blocking other scripts

//dump cookies
if(isset($_REQUEST['page'])){
	$_GET['page'] = $_REQUEST['page'];
}

$_REQUEST = array_merge($_GET, $_POST, $commandlineParams);

//redirect to original page once logged in 
if (!isset($_REQUEST['page']) and isset($queryArray['page'])){
	echo '<script>window.location = "index.php?',build_query($queryArray).'";</script>';
	exit;	
}
//end---------------------------------------------




$breadcrumb = new breadcrumb;
//echo '123123';

if (!isset($_REQUEST['page']) and !isset($_REQUEST['overlay']) or $_REQUEST['page'] == ''){ $_REQUEST['page'] = $default['page'] ;}

	if (isset($_REQUEST['page'])){

		if (file_exists('pages/page.'.$_REQUEST['page'].'.php') || file_exists('pages/core_pages/page.'.$_REQUEST['page'].'.php')){
			$page	= new page($_REQUEST['page']);
			//include('pages/page.'.$_REQUEST['page'].'.php');
		}else{		
			$page	= new page('Page not found');
			//$page = loadScripts($page);
			$page->addHtmlHeader();
			$page->addHtml ( '<div class="warning"><h2>Oops..</h2> <h4>ERROR: 404 - Wierdly, the page you are looking for does not exist.</h4></div>' );
			$page->addHtml ( '<a href="index.php">Return to home page</a>');
			$page->addHtmlfooter();
		}
	}
	elseif (isset($_REQUEST['overlay'])){
		if (file_exists('overlays/overlay.'.$_REQUEST['overlay'].'.php')){
			$page 	= new page($_REQUEST['overlay']);
			$overlay = &$page;
			include('overlays/overlay.'.$_REQUEST['overlay'].'.php');
		}else{		
			if ($config['requires_login']){
				$page	= new page('Page not found', $user);
			}else{
				$page	= new page('Page not found');
			}
			//$page = loadScripts($page);
			$page->addHtmlHeader();
			$page->addHtml ( '<div class="warning"><h2>Oops..</h2> <h4>ERROR: 404 - Wierdly, the page you are looking for does not exist.</h4></div>' );
			$page->addHtml ( '<a href="index.php">Return to home page</a>');
			$page->addHtmlfooter();
		}
		
	}

if (isset($_REQUEST['ajax']) or isset($_REQUEST['feed']) or isset($_REQUEST['api'])){$page->runtimeInfo = false;}

if ($page->runtimeInfo){
	//$page->time_start = $time_start;
	$page->addHtml ( '<div class="footer technical">');
	$page->addHtml (  "<div>Script Runtime - ".(round(script_run_time()/60))." minutes (".script_run_time()." seconds)</div>");
	$page->addHtml (  '<div>Memory use '. round ( (memory_get_peak_usage(true)/1024/1024), 2 ).' MBs<div>');
	$page->addHtml (  '</div>' );	
}


echo $page->outputHTML();





function loadScripts(&$page)
{
	global $smarty;

	$page->addCssFile('templates/css/common.css');

	$page->addCssFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');

	if (PHP_SAPI !== 'cli') {

		$page->addScriptFile('https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js');
		$page->addScriptFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');

		//$page->addScriptFile('libs/js/common.js');

		if (file_exists('libs/js/' . $page->page_name . '.js')) {
			$page->addScriptFile('libs/js/' . $page->page_name . '.js');
		}
	}
	if (file_exists($page->smarty->getTemplateDir(0) . '/css/page.' . $page->page_name . '.css')) {
		$page->addCssFile('templates/css/page.' . $page->page_name . '.css');
	}

	return $page;
}

function params_decrypt(&$params){

    if (!is_array($params)){ return; }
    $enc = new encryption;

    foreach($params as $key => &$param){
        if (($t = $enc->cryptJsDecrypt($param, VARIABLE_CYPHER_KEY)) && ($k = $enc->cryptJsDecrypt($key, VARIABLE_CYPHER_KEY))) {
            unset($params[$key]);
            $params[$k] = $t;
            continue;
        }
    }
}