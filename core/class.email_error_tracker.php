<?php
defined("isInSideApplication")?null:die('no access');

/*
* !!: ADD THIS CLASS and 'email_error_tracker' DB table FOR ERROREMAILS
***************************************/
#[AllowDynamicProperties]
class email_error_tracker {

    function __construct(string $errstr, string $errfile, string $errline){
        $this->purge();
        global $config;

        $this->error_hash = encryption::lightHash($errstr . $errfile . $errline);
        $this->email_already_sent = ($config['limit_email_error_alerts'] > 0 && self::isErrorEmailAlreadySent($this->error_hash, $config['limit_email_error_alerts'])) ? 1 : 0;
        $this->error_count = 'This error has occured <strong>' . self::getErrorsSinceLastEmail($this->error_hash, $config['limit_email_error_alerts']) . '</strong> times in the last ' . $config['limit_email_error_alerts'] . ' minutes.';
    }

    /*
    * t: Saves a new error email to the 'email_error_tracker' table
    *//**
    * @return updated or new 'error email' id
    ***************************************/
    function save($email_sent) {
        global $config;
        $this->email_sent = $email_sent;
        $db = new db;
        $query = $db->build_insert($config['coreTablePrefix'].'email_error_tracker', (array)$this);
        $db->preparedQuery($query['statement'], $query['values']);
        return $db->insert_id();
    }


    /*
    * t: Purges all emails that are older than the set interval (used after the save() function above)
    *//**
    * @param int $interval delete all emails older than x minutes
    ***************************************/
    function purge() {
        global $config;

        $db = new db;
        $query = 'DELETE 
                  FROM `'.$config['coreTablePrefix'].'email_error_tracker`
                  WHERE `timestamp` < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :interval MINUTE)';

        $db->preparedQuery($query, ['interval' => $config['limit_email_error_alerts']]);
    }


    /*
    * t: Checks if an email has already been sent for the same error within the timeframe provided
    *//**
    * @param string $errorHash hashed version of the error (only used for comparison)
    * @param int $interval check if the same error has been emailed within the last x minutes
    * @return bool true if email has already been sent, false if not
    ***************************************/
    static function isErrorEmailAlreadySent($errorHash, $interval) {
        global $config;
        $db = new db;
        $query = 'SELECT * 
                  FROM `'.$config['coreTablePrefix'].'email_error_tracker`
                  WHERE `error_hash` = :errorHash AND
                        `timestamp` > DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :interval MINUTE) AND
                        `email_sent` = "1"';

        return count($db->preparedQuery($query, ['errorHash' => $errorHash, 'interval' => $interval])->fetch_array() ?? []) > 0;
    }

    /*
    * t: Gets the amount of duplicate errors that have been thrown within the timeframe provided
    *//**
    * @param string $errorHash hashed version of the error (only used for comparison)
    * @param int $interval check if the same error has been emailed within the last x minutes
    * @return int number of duplicate errors that have been thrown
    ***************************************/
    static function getErrorsSinceLastEmail($errorHash, $interval) {
        global $config;
        $db = new db;
        $query = 'SELECT * 
                  FROM `'.$config['coreTablePrefix'].'email_error_tracker`
                  WHERE `error_hash` = :errorHash AND
                        `timestamp` > DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL :interval MINUTE) AND 
                        `email_sent` IS NOT NULL';

        return count($db->preparedQuery($query, ['errorHash' => $errorHash, 'interval' => $interval])->fetch_array() ?? []) + 1;
    }
}