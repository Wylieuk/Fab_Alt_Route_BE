<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class progress{

    function __construct($progressId=null, $purge=true){
        global $page;
        if (isset($_REQUEST['requestId']) && ctype_alnum($_REQUEST['requestId'])){
            $this->requestId = $_REQUEST['requestId'];
        } else if (isset($_REQUEST['requestId']) && !ctype_alnum($_REQUEST['requestId'])){
            trigger_error('Only alphanumeric requestIds allowed');
        }
        else {
            $this->requestId = ''; 
        }

        $this->purge = $purge;

        $this->progressId = $progressId ?? session_id();

        $this->tempFolder = "temp/";
        $this->progressFile = $this->tempFolder . $this->progressId . $this->requestId . "_progress.json";
        $this->purgeOldFiles();
    }

    function __destruct() {
        if($this->purge && $this->exists()){
            unlink($this->progressFile);
        }
    }

    function exists(){
        return is_file($this->progressFile);
    }

    function get($progressFileId=false){

        $this->progressId = $progressFileId ?? session_id();

        if ($progressFileId){
            $this->progressFile = $this->tempFolder . $this->progressId . "_progress.json";
        }
        if(file_exists($this->progressFile)) {
            $progress = file_get_contents($this->progressFile);
            return $progress;
        }
        else {
            return json_encode((object)["progress"=>0, "process" => 'Working', 'sessionId' => $this->progressId]);
        }     
    }

    function set($percent, $process='', $file=false){
        if (!is_numeric($percent)){
            return false;
        }
        if ($file){
            return file_put_contents($this->tempFolder.$file, json_encode((object)["progress"=>$percent, "process"=>$process , 'sessionId' => $this->progressId]));
        } else {
            return file_put_contents($this->progressFile, json_encode((object)["progress"=>$percent, "process"=>$process, 'sessionId' => $this->progressId]));
        }
        
    }

    function reset(){
        if( $this->exists()){
            $this->set(100, 'Working');
        }
    }

    function purgeOldFiles(){
        $purgeAge = 60*60;
        if (file_exists($this->tempFolder)) {
            foreach (new DirectoryIterator($this->tempFolder) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if ($fileInfo->isFile() && time() - $fileInfo->getCTime() >= $purgeAge) {
                    unlink($fileInfo->getRealPath());
                }
            }
        }
    }

    // cli progres bar
    // usage echo progressBar(50, 30)."\r"
    static function cliProgressBar(int $baseWidth, float $percent, string $label = '') :string{

        if (PHP_SAPI !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
            return '';
        }
        $minPercent = $percent >= 1 ? $percent: 1;

        $fullBarPortion = str_repeat("\033[96m#", max(0, ceil(($baseWidth/100)*$minPercent)));
        $emptybarPortion = str_repeat("\033[36m.", max(0, ($baseWidth - ceil(($baseWidth/100)*$minPercent))));
        $barEndLeft = "\033[96m[";
        $barEndRight = "\033[96m]";

        $label = str_pad($label, 50, ' ');

        return "\r".$barEndLeft.$fullBarPortion.$emptybarPortion.$barEndRight.' '.str_pad(number_format($percent, 2), 6, ' ', STR_PAD_LEFT)."% {$label}"."\033[39m";

    }

}