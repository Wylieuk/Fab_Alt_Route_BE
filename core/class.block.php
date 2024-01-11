<?php

class block {

    static function allExceptIps(array $ips) {

        // Are we running from the command line?
        if(defined('STDIN') || (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0))
        {
            return true;
        }

        if(!is_array($ips)){
            return true;
        }

        foreach($ips as $ip){
            if(strpos( $ip, '/' ) !== false){
                foreach (self::getEachIpInRange($ip) as $ip2){
                    $allowedIps[] = $ip2; 
                }
            } else {
                $allowedIps[] = $ip; 
            }
        }

        if(in_array($_SERVER['REMOTE_ADDR'], $allowedIps) ){
            return true;
        }

        return false;

    }


    static function Ips(array $allowedIps) {
        if(!is_array($allowedIps)){
            return true;
        }

        if(in_array($_SERVER['REMOTE_ADDR'], $allowedIps) ){
            return false;
        }

        return true;
    }

    static function blockedIps(array $blocked) {
        if(!is_array($blocked)){
            return true;
        }

        if(in_array($_SERVER['REMOTE_ADDR'], $blocked) ){
            return true;
        }

        return false;
    }

    static function getIpRange(  $cidr) {

        list($ip, $mask) = explode('/', $cidr);
    
        $maskBinStr =str_repeat("1", $mask ) . str_repeat("0", 32-$mask );      //net mask binary string
        $inverseMaskBinStr = str_repeat("0", $mask ) . str_repeat("1",  32-$mask ); //inverse mask
    
        $ipLong = ip2long( $ip );
        $ipMaskLong = bindec( $maskBinStr );
        $inverseIpMaskLong = bindec( $inverseMaskBinStr );
        $netWork = $ipLong & $ipMaskLong; 
    
        $start = $netWork+1;//ignore network ID(eg: 192.168.1.0)
    
        $end = ($netWork | $inverseIpMaskLong) -1 ; //ignore brocast IP(eg: 192.168.1.255)
        return array('firstIP' => $start, 'lastIP' => $end );
    }
    
    static function getEachIpInRange ( $cidr) {
        $ips = array();
        $range = self::getIpRange($cidr);
        for ($ip = $range['firstIP']; $ip <= $range['lastIP']; $ip++) {
            $ips[] = long2ip($ip);
        }
        return $ips;
    }

    static function logFailedLogin($data){
        global $config;
        if (!$config['enableBruteForceProtection']){
            return false;
        }
        $db = new db;
        $query = $db->build_insert("{$config['coreTablePrefix']}failed_logins", $data);
        $db->preparedQuery($query['statement'], $query['values']);
        if (self::failedLoginsExceeded($data)){
            self::blockIp($data);
        }
    }

    static function failedLoginsExceeded($data){
        global $config;
        if (!$config['enableBruteForceProtection']){
            return false;
        }
        $db = new db;
        $db->query ('DELETE FROM `'.$config['coreTablePrefix'].'failed_logins` WHERE `timestamp` < FROM_UNIXTIME("'.(time() - $config['bruteForceTime']).'")');
        $db->preparedQuery('SELECT COUNT(*) FROM `'.$config['coreTablePrefix'].'failed_logins` WHERE `ip` = :ip', ['ip' => $data['ip']]);
        return ($db->fetch_array()[0]['COUNT(*)']) >= $config['bruteForceCount'];
    }

    static function blockIp($data){
        global $config;
        if (!$config['enableBruteForceProtection']){
            return false;
        }
        $db = new db;
        $query = $db->build_insert("{$config['coreTablePrefix']}blocked_ips", $data);
        $db->preparedQuery($query['statement'], $query['values']);
    }

    static function isBruteForcedBlocked($data){
        global $config;
        if (!$config['enableBruteForceProtection']){
            return false;
        }
        $db = new db;
        $db->query ('DELETE FROM `'.$config['coreTablePrefix'].'blocked_ips` WHERE `timestamp` < FROM_UNIXTIME("'.(time() - $config['bruteforceLockoutTime']).'")');
        $db->preparedQuery('SELECT * FROM `'.$config['coreTablePrefix'].'blocked_ips` WHERE `ip` = :ip', ['ip' => $data['ip']]);
        if($result = $db->fetch_array()){
            return self::blockedIps([$result[0]['ip']]);
        }
    }

}