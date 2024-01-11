<?php
defined("isInSideApplication")?null:die('no access');

$smarty->setTemplateDir( $config['documentroot']."/templates");
$smarty->setCompileDir( $config['documentroot']."/cache/smarty/templates_c");
$smarty->setCacheDir( $config['documentroot']."/cache/smarty");
$smarty->compile_check  = true; //disable for production

$smarty->assign('lang',$lang);

//$smarty->error_reporting = 0;//E_ALL & ~E_NOTICE & ~E_WARNING;

?>