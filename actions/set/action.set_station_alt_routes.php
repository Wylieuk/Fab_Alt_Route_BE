<?php 
defined("isInSideApplication")?null:die('no access');

set_time_limit(60*60);
ini_set('memory_limit', '10G');

$rowsInserted = 0;

$errorCount = 0;
$badData = [];
files::deleteDirFilesOlderThan('uploads', time() - (24 * 60 * 60 * 1));
$fileList = json_decode(encryption::cryptJsDecryptWrapper($this->data['fileList'], VARIABLE_CYPHER_KEY));

include_once('libs/ref/file_configs.php'); //NOSONAR


foreach($fileList as $fileName){

    
    $dataFile = new data_file($fileName, $this->data['uploaderSettings'], $fileConfigs);


    $errorCount   = $errorCount + $dataFile->errorCount;

    foreach($dataFile->badData as $sheetname => $sheet){
        foreach($sheet as $error){
            $badData[$sheetname] = $error;
        }
    }

    if(!empty($dataFile->goodData)){
        foreach($dataFile->goodData as $sheetname => $data){
            $import_handler = new import_handler($sheetname, $data, $dataFile->readerConfig, (array)json_decode($this->data['uploaderSettings'] ?? '[]'));
            $rowsInserted = $rowsInserted + $import_handler->insertCount;
        }
    }


    if (file_exists($fileName)){
        //unlink($fileName);
    }

    cache::purgeAll();

}


$this->response = [
    'status' => $errorCount > 0 ? 'Imported ' . $rowsInserted . ' of ' . ($rowsInserted + $errorCount) . ' rows, with ' . $errorCount . ' error(s)' : 'Successfully imported ' . $rowsInserted . ' rows', 
    'summary' => array_slice($badData, 0, 1000), 
    'errorCount' => $errorCount,
    'memoryUseMb' => round((memory_get_peak_usage(true)/1024/1024), 2),
    'timeSecs' => script_run_time()
];
