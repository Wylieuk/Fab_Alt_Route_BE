<?php
defined("isInSideApplication")?null:die('no access');

//$users['username'] = 'Password';
// $users['clint'] = 'Password';
// $users['test'] = 'test';

$apiKey[] = encryption::lightHash(hash('sha1', $config['siteaddress'] . date('Hmdy')));

// API token for Trainset Desktop upload
$apiKey[] = hash("sha256", utf8_encode("trainset-desktop-".date("Y-m-d")));


$apiKey[] = 'DOG!!_asd8678T454ASD!asdj';
//$apiKey[] = 'qwerwqerwqer';
