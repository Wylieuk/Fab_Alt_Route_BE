<?php
defined("isInSideApplication")?null:die('no access');

$progress = new progress;

    //$progress->set(100);

 // Send the JSON...
    ob_start('ob_gzhandler');
    headers::compression();
    headers::accessControlAsRefer();
    //headers::allowCredencials();
    headers::json();
	die($progress->get($this->data['progress_file_id']));