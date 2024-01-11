<?php

global $config;

//debug($this);

$this->config = $config;

$this->data['referrer'] = current(explode('#' ,base64_decode($this->data['referrer'])) ?? []) ?? '';