<?php
defined("isInSideApplication")?null:die('no access');


$output['response'] = config::load();


headers::accessControlAsRefer();
headers::allowCredencials();
headers::json();

die(json_encode($output));
