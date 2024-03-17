<?php defined("isInSideApplication")?null:die('no access');



class data_file{

    private $tocs;
    private $data;
    public  $goodData = [];
    public  $badData = [];
    public  $errorCount = 0;
    private $progress;
    private $db;
    public  $rowsInserted = 0;
    private $fileSuffix;
    private $fileType;
    private $filename;
    private $uploaderSettings;
    public  $readerConfig;
    private $allowedFileConfigs;
    private $fileSignatures;
    // = [
    //     '2d38ba7f48eb0bab86d50ceb3d95e58c' => 'fabStyle',
    //     '3dec03035780775df0e774232542370e' => 'pmsRaw'
    // ];


    public function __construct($fileName, string $uploaderSettings, $fileConfigs){

        $this->allowedFileConfigs = $fileConfigs;


        $this->setAllowedFileSignatures();

       
        $this->filename         = $fileName;
        $this->uploaderSettings = json_decode($uploaderSettings);
        $this->progress         = new progress;
        $this->db               = new db;
        $this->rowsInserted     = 0;
        $this->fileSuffix       = strtolower(explode('.', $fileName)[array_key_last(explode('.', $fileName))]);

        $this->detectFileType(); //sets $this->fileType

        $this->loadConfig(); // set this->readerConfig

  
        $this->load();
        $this->validate();

        $this->postProcess();
        //$this->save();

        $this->progress->set('100', 'done', base64_encode($this->filename));
        
        foreach($this->badData as $sheet){
            $this->errorCount = $this->errorCount + count($sheet);
        }
    
    }

    private function setAllowedFileSignatures(){
        foreach ($this->allowedFileConfigs as $fileType => $allowedConfig){
            foreach($allowedConfig['sheets'] as $sheetName => $sheet){
                $allowedColumnHeaders[$sheetName] = array_values(array_map(fn($col) => $col['name'], $sheet['columns']));
            }
            $this->fileSignatures[md5(strtolower(json_encode($allowedColumnHeaders)))] = $fileType;
            $allowedColumnHeaders = [];
        }
    }


    private function load(){
        $this->progress->set('30', 'Reading file', base64_encode($this->filename));
        
        switch ($this->fileSuffix){

            case 'xls':
            case 'xlsx':
    
                // creat new spreadsheet reader with config
                $spr = new spreadsheet_reader($this->readerConfig);
                
                $this->data = $spr->load($this->filename, ucfirst($this->fileSuffix), $this->progress);

                break;
    
            case 'csv':
    
                $csv = array();
                $lines = file($this->filename , FILE_IGNORE_NEW_LINES);
                foreach ($lines as $key => $value){
                    $csv[$key] = str_getcsv($value);
                }
    
                if ($this->readerConfig['headerRow'] > 0){
                    unset($csv[$this->readerConfig['headerRow'] - 1]);
                }
    
                $this->data['sheet1'] = array_map(function($line){
                    $colsKeys = array_map(function($e){ return $e['name'];}, $this->readerConfig['columns']);
                    $_t = array_combine($colsKeys, $line);
                    $_t['expires'] = timestamp::db_format($_t['expires']);
                    return $_t;
                }, $csv);
    
    
                break;
    
            default:
                throw new Exception('File type not allowed');
       
        }

    }

    private function postProcess(){

        //this function relies on a translatedkey propery in the config to find the correct column name then runs the postProcess function in that column config.

        foreach($this->goodData as $heetName => &$sheet){
            foreach($sheet as &$row){
                foreach($row as $k => &$v){
                    $fnPostProcess = current(array_filter($this->readerConfig['sheets'][$heetName]['columns'], fn($col) =>  $k == ($col['translatedkey'] ?? null) ) ?? [])['postProcess'] ?? null;
                    if(!empty($fnPostProcess) && is_callable($fnPostProcess)){
                        $v = $fnPostProcess($v, $row, $this->goodData );
                    }
                }
            }
        }

    }

    private function validate(){



        $this->progress->set('60', 'Processing file', base64_encode($this->filename));

        
        foreach ($this->data as $sheetName => &$sheet){

            $badLines   = array_filter($sheet, fn($line) => count($line['error'] ?? []) > 0);
            $goodLines  = array_filter($sheet, fn($line) => count($line['error'] ?? []) == 0);
            $sheet      = array_merge($goodLines, $badLines);

            $this->badData[$sheetName]  = array_values(array_filter($sheet, function($l){return !empty($l['error']);}));
            $this->goodData[$sheetName] = array_values(array_filter($sheet, function($l){return empty($l['error']);}));

            $this->progress->set(78 , 'Finding tocs', base64_encode($this->filename));
    
        }

        unset($sheet);
        unset($line);


    
    }

    private function save(){

        $this->db->startTransaction();

        $this->progress->set('80', 'Commiting data', base64_encode($this->filename));


        if(count($this->goodData) > 0){

            $chunkSize        = 1000;
            $totalIterations  = count($this->goodData)/$chunkSize;
            $progressInverval = (98 - 80) / $totalIterations;
            $progressPercent  = 80;

            if (!empty($this->uploaderSettings->overwriteData)) {
                $this->db->preparedQuery('DELETE FROM `app_discounted_journeys` WHERE `toc_code` in(:'.implode(', :',array_keys($this->tocs)).')', $this->tocs);
            }

            foreach (array_chunk($this->goodData, $chunkSize) as $index => $chunk){

                $progressPercent = $progressPercent + $progressInverval;
            
                if($index % 5 == 0){
                    $this->progress->set($progressPercent , 'Commiting data', base64_encode($this->filename));
                }

                $query = $this->db->buildbulkInsert('app_discounted_journeys', $chunk);

                if ($this->db->preparedQuery($query['statement'], $query['values'])){
                    $this->rowsInserted += $this->db->affected_rows();
                }


            }
            $this->progress->set(99 , 'Commiting data', base64_encode($this->filename));
        }
        $this->db->commit();
    }

    private function detectFileType(){
        $this->progress->set('10', 'Detecting file type', base64_encode($this->filename));

        foreach($this->allowedFileConfigs as $allowedfileConfig){

            foreach($allowedfileConfig['sheets'] as $sheetname => $sheet){
                $spr = new spreadsheet_reader([]);
                $fileColHeaders[$sheetname] = array_values($spr->getHeaderRow($this->filename, $this->fileSuffix, $sheetname, $sheet['headerRow']));
            }

            $signature = md5(strtolower(json_encode($fileColHeaders)));
            if(!empty($this->fileSignatures[$signature])){
                $this->fileType = $this->fileSignatures[$signature];
                return;
            }

            $fileColHeaders = [];

        }

        
        if (empty($this->fileSignatures[$signature])){
            throw new Exception("File signature[{$signature}] not recognised, please check you have the correct sheet/column labels");
        }

    }



    private function loadConfig(){

        if(empty($this->allowedFileConfigs[$this->fileType])){
            throw new Exception("No config available for type {$this->fileType}");
        }

        $this->readerConfig = $this->allowedFileConfigs[$this->fileType];
    }

}