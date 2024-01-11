<?php
defined("isInSideApplication")?null:die('no access');

global $config;


$this->error       = $this->data['error'] ?? false;
$this->username    = $this->data['user']->username ?? null;
$this->token       = $this->data['token'] ?? null;
$this->status      = $this->data['status'] ?? false;
$this->referrer    = $this->data['referrer'] ?? '';
$this->CspNonce    = $config['CspNonce'];
$this->siteaddress = $config['siteaddress']


?>