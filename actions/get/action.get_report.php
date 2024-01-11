<?php defined("isInSideApplication")?null:die('no access');

if (!(user::hasAccess(array('admin', 'rdg')))) {
    throw new Exception('Insufficant Access Rights');
}


$type = $this->data['type'] ?? null;
    empty($type) ? throw new Exception('Missing param `type`'): null;
    !is_string($type) ? throw new Exception('baddly formed string `type`'): null;


$ids = json_decode($this->data['selected_ids'] ?? '[]');
    empty($ids) ? throw new Exception('Missing param `selected_ids`'): null;
    json_last_error() != JSON_ERROR_NONE ? throw new Exception('baddly formed json `selected`'): null;


$fields = json_decode($this->data['export_fields'] ?? '[]');
    empty($fields) ? throw new Exception('Missing param `export_fields`'): null;
    json_last_error() != JSON_ERROR_NONE ? throw new Exception('baddly formed json `export_fields`'): null;

$report  = new report(
    type: $type, 
    ids: $ids, 
    fields: $fields
);


$reportResultSet = $report->getResultSet();

if(empty($reportResultSet)){
    throw new Exception('Empty report!');
}

if (!empty($this->data['download']) && ($this->data['download'] || $this->data['download'] ==  'true')){
    array_unshift($reportResultSet, array_keys(current($reportResultSet)));
    $csv = csv::fromArray($reportResultSet);
    headers::csv('report.csv');
    die($csv);
}
else {
   return  $reportResultSet;
}