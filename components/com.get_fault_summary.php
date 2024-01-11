<?php
defined("isInSideApplication")?null:die('no access');

if (!isset($this->data['id'])) {
    throw new Exception('Fault id not set');
}

global $config;

$this->config = $config;


$fault = new fault;
$fm = new fault_mail;
$this->fault = $fault->get($this->data['id']);
$this->fault->darwin_connected = +$this->fault->darwin_connected ? 'Yes' : 'No';
$this->fault->enabled = +$this->fault->enabled ? 'Open' : 'Closed';
$this->fault->is_critical = +$this->fault->is_critical ? 'Yes' : 'No';
$this->fault->timestamp = date('d-m-Y H:i:s', strtotime($this->fault->timestamp));
$this->fault->mailbox = (object)$fm->mailbox('CIS', $this->data['id']);
$this->fault->mailbox->merged = array_merge($this->fault->mailbox->inbox, $this->fault->mailbox->outbox);
unset($this->fault->mailbox->inbox, $this->fault->mailbox->outbox);

foreach ($this->fault->mailbox->merged as &$m) {
    $m = (object)$m;

    $m->to= array_merge($m->to_address ?? [], $m->additional_addresses ?? []);
    $m->lastToIndex = count($m->to) - 1;
    $m->lastCcIndex = isset($m->cc_addresses) && count($m->cc_addresses) !== 0 ? count($m->cc_addresses) : -1;
    unset($m->to_address, $m->additional_addresses);

    if (!isset($m->from_address)) {
        $m->type = 'outbox';
    } else {
        $m->type = 'inbox';
    }

    $m->message = $fm->message('CIS', $this->data['id'], $m->type, $m->id)['body'] ?? '';
}

usort($this->fault->mailbox->merged, function ($a, $b) {
    return strtotime($b->timestamp) - strtotime($a->timestamp);
});

foreach ($this->fault->mailbox->merged as &$m) {
    $m->timestamp = date('d-m-Y H:i:s', strtotime($m->timestamp));
}


$logQuery = [];
$logQuery[] = (object)[
    'type' => 'cis_fault',
    'id' => $this->data['id']            
];

$this->log = log::readLog($logQuery);