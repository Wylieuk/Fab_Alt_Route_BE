<?php

foreach (['id', 'checksum'] as $requred){
    if(empty( $this->data[$requred])){
        throw new Exception('Missing params');
    }
}

$vendor = new vendor([
    'id' => $this->data['id'], 
    'checksum' => $this->data['checksum'],
    'enabled' => 1
]);

$id = $vendor->saveVendor();



$log = new log([
    'component'    => 'vendor',
    'component_id' => $id,
    'details'      => 'user activated',
    'user_id'       => $id
]);
$log->save();


$this->response = empty($id) ? false : true;