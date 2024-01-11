<?php defined("isInSideApplication")?null:die('no access');

ini_set('memory_limit', '1G');

$db_old = new db('localhost', 'rils_old_fulldata', 'root', '36223');

$db = new db;

$newUsers = user::loadAllUsers();


$migrationItems = (object)[
    'nonCisFaults' => (object)[
        'from' => (object)[
            'faultTable' => 'nocis_fault_log',
            'notesTable' => 'nocis_fault_log_notes',
            'emailTable' => 'nocis_fault_log_emails',
        ],
        'to' => (object)[
            'faultTable' => 'app_non_cis_faults',
            'inbox' => 'app_non_cis_faults_inbox',
            'outbox' => 'app_non_cis_faults_outbox',
            'auditLog' => 'app_audit_log',
            'logType'    => 'noncis_fault'
        ]
    ],
    'cisFaults' => (object)[
        'from' => (object)[
            'faultTable' => 'fault_log',
            'notesTable' => 'fault_log_notes',
            'emailTable' => 'fault_log_emails',
        ],
        'to' => (object)[
            'faultTable' => 'app_cis_faults',
            'inbox' => 'app_cis_faults_inbox',
            'outbox' => 'app_cis_faults_outbox',
            'auditLog' => 'app_audit_log',
            'logType'    => 'cis_fault'
        ]
    ],
];

$counts = [
    'success' => 0,
    'failure' => 0
];


foreach ($migrationItems as $faultType => $migrationItem){
    //migrate fault

    //faults
    foreach ($db_old->query("SELECT * FROM `{$migrationItem->from->faultTable}` ORDER BY `{$migrationItem->from->faultTable}`.`id` ASC")->fetch_array() ?? [] as $oldFault){

        $oldFault = (object)$oldFault;

        //debug([$oldFault, $faultType, $migrationItem]);

        switch ($faultType){

            case 'nonCisFaults':

                $newfault = [
                    'id' => (int)$oldFault->id,
                    'system_id' => (int)$oldFault->fault_system,
                    'fault_type_id' => (int)$oldFault->fault_type,
                    'fault_details' => $oldFault->fault_details,
                    'supplier_reference' => $oldFault->supplier_ref,
                    'enabled' => $oldFault->cleared_timestamp == 0 ? 1 : 0,
                    'timestamp' => timestamp::db_format($oldFault->timestamp),
                    'source' => 'rils1'
                ];
                break;

            case 'cisFaults':

                $newfault = [
                    'id' => (int)$oldFault->id,
                    'location_id' => (int)$oldFault->fault_location,
                    'fault_type_id' => (int)$oldFault->fault_type,
                    'fault_details' => $oldFault->fault_details,
                    'darwin_connected' => $oldFault->darwin,
                    'worldline_reference' => $oldFault->atos_ref,
                    'hackon_reference' => $oldFault->hackon_ref,
                    'enabled' => $oldFault->cleared_timestamp == 0 ? 1 : 0,
                    'timestamp' => timestamp::db_format($oldFault->timestamp),
                    'source' => 'rils1'
                ];
                break;
        }

        write($newfault, $migrationItem->to->faultTable, $counts);


        $newAuditLog = [
            'user_id' => getUserid($newUsers, $oldFault->creator), 
            'action_type' => 'set',
            'item_type' => $migrationItem->to->logType, 
            'item_id' => (int)$oldFault->id,
            'attempted_action' => 'create',
            'result' => 'success',
            'result_target' => (int)$oldFault->id,
            'log_action' => 'create->true',
            'note' => "Fault created by {$oldFault->creator}",
            'timestamp' =>  timestamp::db_format($oldFault->timestamp),
            'source' => 'rils1'
        ];

        write($newAuditLog, $migrationItem->to->auditLog, $counts);
    }

    //debug($newNonCisfaults);
    unset($newfault);


    //get array of enabled fault id's from new table
    $enabledFaultIds = array_values(array_map(function($f){
        return (int)$f['id'];
    }
    , $db->query("SELECT `id` FROM `{$migrationItem->to->faultTable}` WHERE `enabled` = '1' ORDER BY `{$migrationItem->to->faultTable}`.`id` ASC")->fetch_array() ?? []));


    //logs
    foreach ($db_old->query("SELECT * FROM `{$migrationItem->from->notesTable}` ORDER BY `{$migrationItem->from->notesTable}`.`id` ASC")->fetch_array() ?? [] as $oldFaultNotes){

        $oldFaultNotes = (object)$oldFaultNotes;

        $newAuditLog = [
            'user_id' => getUserid($newUsers, $oldFaultNotes->username), 
            'action_type' => 'set',
            'item_type' => $migrationItem->to->logType, 
            'item_id' => (int)$oldFaultNotes->log_ref,
            'attempted_action' => 'create',
            'result' => 'success',
            'result_target' => (int)$oldFaultNotes->log_ref,
            'log_action' => 'create->true',
            'note' => "{$oldFaultNotes->username}: $oldFaultNotes->note",
            'timestamp' =>  timestamp::db_format($oldFaultNotes->stamp),
            'source' => 'rils1'
        ];

        write($newAuditLog, $migrationItem->to->auditLog, $counts);


    }

    unset($newAuditLog);

    //mail
    foreach ($db_old->query("SELECT * FROM `{$migrationItem->from->emailTable}` ORDER BY `{$migrationItem->from->emailTable}`.`id` ASC")->fetch_array() ?? [] as $oldFaultEmail){

        $oldFaultEmail = (object)$oldFaultEmail;

        $target = striPos($oldFaultEmail->email_addr, 'fault_') === 0 ? 'outbox': 'inbox';

        if ((int)$oldFaultEmail->log_ref < 1){
            continue;
        }
        
        switch ($target){

            case 'inbox':
                $mail = [
                    'fault_id' => (int)$oldFaultEmail->log_ref,
                    'uid' => null,
                    'mailbox_name'=> null,
                    'subject'=> $oldFaultEmail->subject,
                    'body' => gzencode(htmlentities($oldFaultEmail->email)),
                    'from_address'=> $oldFaultEmail->email_addr,
                    'date_sent' => timestamp::db_format($oldFaultEmail->stamp),
                    'is_read' => $oldFaultEmail->acknowledged,
                    'attachments' => getAttachements((int)$oldFaultEmail->log_ref, $enabledFaultIds, $oldFaultEmail->attachments),
                    'source' => 'rils1'
                ];

                write($mail, $migrationItem->to->inbox, $counts);

            break;

            case 'outbox':
                $mail = [
                    'fault_id' => (int)$oldFaultEmail->log_ref,
                    'subject'=> $oldFaultEmail->subject,
                    'body' => gzencode(htmlentities($oldFaultEmail->email)),
                    'to_address'=> $oldFaultEmail->email_to,
                    'date_sent' => timestamp::db_format($oldFaultEmail->stamp),
                    'is_sent' => $oldFaultEmail->acknowledged,
                    'source' => 'rils1'
                ];

                write($mail, $migrationItem->to->outbox, $counts);

            break;

            default:
                $mail = null;
                throw new Exception('target invalid');

        }


        unset($mail);

    }
}

echo "Successfull items {$counts['success']}<br/>";
echo "Failed items {$counts['failure']}<br/>";



function getUserid($users, $username){
    return current(array_filter($users, function($u) use($username){return strtolower($u['username']) === strtolower($username);} ?? []))['id'] ?? null;
}

function write($item, $table, &$counts) {
    $db = new db;
    $query = $db->build_insert($table, (array)$item);
    $db->preparedQuery($query['statement'], $query['values'])->affected_rows() > 0 ? $counts['success']++ : $counts['failure']++ ;
}

function getAttachements(int $fault_id, array $enabledFaultIds, string $attachments){

    if (empty($attachments) || !in_array($fault_id, $enabledFaultIds)){
        return null;
    }

    if (empty($attachments = array_filter(explode(',', $attachments), function($a){return !empty($a);}))) {
        return null;
    }

    $a = null;

    $attachmentName = end($attachments);

    //foreach ($attachments as $attachmentName){

        $_t = explode('.', $attachmentName);
        $suffix = end($_t);

        if(!in_array($suffix, ['bmp', 'png', 'txt', 'jpg', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'log', 'csv', 'xml'])){
            return null;
        }

        $mimeType = null;

        $a = [
            'data' => getAttachmentData($attachmentName, $mimeType, $suffix),
            'mimeType'  => $mimeType,           
            'filename'  => $attachmentName,   
        ];
    

    if(!empty($a['data'])){
        return gzencode(json_encode([$a]));
    } else {
        return null;
    }
}

function getAttachmentData(&$attachmentName, &$mimeType, $suffix){

    global $config;

    $folder =  'old_attachements'.DIRECTORY_SEPARATOR;

    try{
        if($attachmentContent = file_get_contents($folder . $attachmentName))
        {

            if (in_array($suffix, ['bmp', 'png', 'jpg', 'gif'])) { //allowed image files
            
                $imagick = new Imagick();
                try{
                    //size down images;
                    if ($isImage = $imagick->readImageBlob($attachmentContent)){

                        // do not store tiny images;
                        if ($imagick->getImageWidth() < 35 || $imagick->getImageHeight() < 35) {
                            return null;
                        }       

                        if($imagick->getImageWidth() > $config['maxAttachmentImageWidth'] || $imagick->getImageHeight() > $config['maxAttachmentImageHeight']){
                            $imagick->adaptiveResizeImage($config['maxAttachmentImageWidth'], $config['maxAttachmentImageHeight'], true);
                            $imagick->setImageFormat('jpg');
                            $imagick->setImageCompressionQuality(70);
                            $attachmentContent = $imagick->getImagesBlob();
                            $attachmentName = str_ireplace(['.jpg', '.png', '.gif', '.bmp'], '_', $attachmentName).'.jpg';
                        }

                        $mimeType = $imagick->getImageMimeType();
                        
                    }
                }
                catch(Exception $e){
                    //not an image
                }
                unset($imagick);

            }

        }
    } catch (Exception $e) {
        throw new Exception("{$attachmentName}: {$e->getMessage()} L:{$e->getLine()}");
    }

    if (empty($mimeType)){
        $buffer = $attachmentContent;
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mimeType = $finfo->buffer($buffer);
    }

    return base64_encode($attachmentContent);
}