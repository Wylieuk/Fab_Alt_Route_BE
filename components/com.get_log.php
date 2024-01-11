<?php
defined("isInSideApplication")?null:die('no access');


/*
* t: Sets up data to create the log in com.get_log.tpl
***************************************/  

$columnMap = [
    'get_incident_summary' => 
        (object)[
            'item_type' => 'incident',
            'result_target' => $this->data['id'] ?? null
        ]
];

$columnMapTarget = $columnMap[$this->data['action']];

switch ($columnMapTarget->item_type) {
    case 'incident':
        $this->title = 'Incident Log';
        $incident = new incident;
        $this->incident = $incident->get($columnMapTarget->result_target);

        $logQuery = [];
        $logQuery[] = (object)[
            'type' => $columnMapTarget->item_type,
            'id' => $columnMapTarget->result_target            
        ];

        foreach ($this->incident->components as $component) {
            $logQuery[] = (object)[
                'type' => 'incident_component',
                'id' => $component->id
            ];
        }
    break;

    default: 
        throw new Exception('log type not found');
}

$this->log = log::readLog($logQuery);