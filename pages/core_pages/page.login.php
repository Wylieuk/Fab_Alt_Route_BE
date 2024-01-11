<?php
defined("isInSideApplication")?null:die('no access');

$this->smarty = $smarty = new_smarty();

$user 	= false;

loadScripts($this);



$this->addHtmlHeader();
$this->addHardComponent ('login');
$this->addHtmlFooter();

?>