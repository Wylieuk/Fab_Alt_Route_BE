<?php
defined("isInSideApplication")?null:die('no access');

/*
* t: Sets up incident data to be used within com.get_incident_summart.tpl
***************************************/  

if (!isset($this->data['id'])) {
    throw new Exception('Incident id not set');
}

$incident = new incident;
$incident->get($this->data['id']);

$created_dateTime = new DateTime($incident->created_by->timestamp);

foreach($incident->components as $component) {

    $component->timers = (object)[];

    if ($component->getfirstEnabledTimetamp()) {
        $fet_dateTime = new DateTime($component->getfirstEnabledTimetamp());
        $diff = $fet_dateTime->diff($created_dateTime);

        $component->timers->time_to_enable = (object)[
            'heading' => 'Enabled after',
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i
        ];
    } else {
        $component->timers->time_to_enable = null;
    }

    

    if ($component->getfirstRefreshedTimetamp()) {

        $component->timers->time_to_complete = (object)[
            'timestamp' => date('H:i d/m/Y', strtotime($component->getfirstRefreshedTimetamp()))
        ];

    } else {
        $component->timers->time_to_complete = null;
    }

    if ($component->getLastDisabledTimestamp()) {

        $ldt_dateTime = new DateTime($component->getLastDisabledTimestamp());
        $diff = $ldt_dateTime->diff(new DateTime($component->getfirstEnabledTimetamp() ?? ''));

        $component->timers->time_to_clear = (object)[
            'heading' => 'Cleared after',
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i
        ];
    } else {
        $component->timers->time_to_clear = null;
    }

    if ($component->getUpdateCount()){
        $component->updateCount;
    }

    
}

$this->incident = $incident;
