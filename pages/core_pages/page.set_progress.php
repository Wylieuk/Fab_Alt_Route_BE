<?php

defined("isInSideApplication")?null:die('no access');

    $this->data['percent'] = isset($this->data['percent'])?$this->data['percent']:false;
    $this->data['progress_file'] = isset($this->data['progress_file'])?$this->data['progress_file']:false;
    $this->data['process'] = isset($this->data['process'])?$this->data['process']:'';

    if($this->data['percent'] && $this->data['progress_file']){
        $progress = new progress;
        if ($progress->set($this->data['percent'], $this->data['process'], $this->data['progress_file'])){
            headers::json();
            die(json_encode(['response' => 'success']));
        }
    }


    headers::json();
	die(json_encode(['response' => 'failed']));