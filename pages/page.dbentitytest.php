<?php

$e = new dbentity("users");

echo $e;

$e->username = "kai";
$e->email = "kai@gohegan.uk";
$e->jobTitle = "Developer";
$e->phoneNumber = "test";
$e->groupId = 1;
$e->enabled = false;

$e->save();

echo $e;