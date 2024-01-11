<?php defined("isInSideApplication")?null:die('no access');


$this->runtimeInfo = false;

$this->smarty = $smarty = new_smarty();

if(!$response = $this->setAction($this->data['action'], ['data' => $this->data, 'dirtyData' => $this->dirtyData])->response){
    die("There has been an error activating this account");
}

loadScripts($this);

$this->addHtmlHeader();
$this->addHardComponent ('activate_user_self', (array)$this->data);
$this->addHtmlFooter();