<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class timestamp{
	
	static function is_same($submitted_timestamp, $stored_timestamp){
		if ($submitted_timestamp == $stored_timestamp){
			return true;
		}
		return false;		
	}
	
	
	static function  timeToSeconds($time){
		
//debug($time,'$time');
		$time_bits = explode (':',$time);
		if (!isset($time_bits[2])){$time_bits[2] =0;}
		
		
		//debug(($time_bits[0]*60*60) + ($time_bits[1]*60) +  $time_bits[2],$time);
		
		return( ($time_bits[0]*60*60) + ($time_bits[1]*60) +  $time_bits[2]  );
	}

	static function secondsToTime($seconds) {
		return date('H:i', strtoTime(date('Y-m-d')) + $seconds);
	}

	static function isTimestamp(string $ts){
		if(strpos($ts, '-') !== false && strpos($ts, 'T') !== false && strpos($ts, 'Z') === (strlen($ts) - 1)){
			return true;
		}
		return false;
	}
	
	static function db_format($date, $format='Y-m-d H:i:s'){

        if($date === null || empty($date)){
            return null;
        }
		
		//$date = str_replace('/', '-', $date)

        if (is_numeric($date)){
            return date ($format, $date);
        }

		$date = str_replace(['T', 'Z'] , [' ', ''], $date);

		$date = current(explode('.', $date) ?? []);

		$date = trim($date, " \"'");

        if(date ('Y',strtotime($date)) != '1970'){
            return date ($format, strtotime($date));
        }

        if ($date != ''){
            $date = explode('/',$date);
            if(strpos(($date[2] ?? ''),':')){
                $date = substr('20'.$date[2], 0, -6) .'/' .$date[1] .'/'. $date[0] .'/ '.	substr($date[2], -5);	
            }
            else{
                $date = ($date[2] ?? '') .'/' .($date[1] ?? '') .'/'. ($date[0] ?? '');
            }
            
            if(date ('Y',strtotime($date)) != '1970'){
				return date ($format, strtotime($date));
			}
        }
        return $date;
	}
	
	static function findTime($timestamp){
		$bits = explode(' ', $timestamp);
		return array_pop($bits);
	}
	
	static function findHour($timestamp){
		return date('H',strtotime($timestamp));
	}
	
	static function get_DATE_ONLY($date_time){
		return current(explode(' ', $date_time));
	}
	
	static function getDayNumber($date){
		return(date('j',strtotime($date)));
	}
	
	static function getDayofWeek($date){
		return(date('D',strtotime($date)));
	}
	
	static function set_epoc_to_blank($timestamp){
			if (stripos($timestamp, '01/01/70') !== false)	{
				$timestamp =  '';	
			}
			//debug($timestamp);
			return $timestamp;
	}
	
	static function friendlydate($date){
		global $config;
		if (is_numeric($date)){
			return date($config['friendly_date'], $date);
		}
		return date($config['friendly_date'], strtotime($date));
	}
	
	static function dateOnly($timestamp){
		return date('Y-m-d',strtotime($timestamp));
	}
	
	static function unixTimestamp($timestamp){
		global $config;
		$unix_timestamp = strtotime (self::db_format($timestamp));
		return $unix_timestamp;
	}

	static function convertToHoursMins($time, $format = '%02d:%02d') {
		if ($time < 1) {
			return;
		}
		$hours = floor($time / 60);
		$minutes = ($time % 60);
		// if less than one minute, set it to a minute
		if ($minutes === 0) {
			$minutes = 1;
		}
		return sprintf($format, $hours, $minutes);
	}
}

?>