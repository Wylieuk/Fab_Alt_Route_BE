<?php 
defined("isInSideApplication")?null:die('no access');

set_time_limit(60*60);
ini_set('memory_limit', '10G');

$rowsInserted = 0;

$errorCount = 0;
$badData = [];
files::deleteDirFilesOlderThan('uploads', time() - (24 * 60 * 60 * 1));
$fileList = json_decode(encryption::cryptJsDecryptWrapper($this->data['fileList'], VARIABLE_CYPHER_KEY));

$fileConfigs = include_once('libs/ref/file_configs.php'); //NOSONAR

$importCount = 0;

foreach($fileList as $fileName){

    
    $dataFile = new data_file($fileName, $this->data['uploaderSettings'], $fileConfigs);


    $errorCount   = $errorCount + $dataFile->errorCount;

    foreach($dataFile->badData as $sheetname => $sheet){
        foreach($sheet as $error){
            $badData[$sheetname] = $error;
        }
    }

    $fileData = [];
    if(!empty($dataFile->goodData)){
        //loop over sheets
        foreach($dataFile->goodData as $sheetname => $data){
            $ih = new import_handler($sheetname, $data, $dataFile->readerConfig, (array)json_decode($this->data['uploaderSettings'] ?? '[]'));
            $sheetData = $ih->importedData ?? [];
            $fileData[mb_strtolower($sheetname)] = $sheetData;
            $rowsInserted = $rowsInserted + count($sheetData);
        }
    }

    $station = new station(['crs' => strtoupper($ih->baseCrs), 'staged' => json_encode($fileData)]);

    $station->save();

    $importCount++;


    if (file_exists($fileName)){
        //unlink($fileName);
    }

    cache::purgeAll();

}


$this->response = [
    'status' => $errorCount > 0 ? 'Imported ' . $importCount. ' of ' . count($fileList) . ' file, with ' . $errorCount . ' error(s)' : 'Successfully imported file(s)', 
    'summary' => array_slice($badData, 0, 1000), 
    'errorCount' => $errorCount,
    'memoryUseMb' => round((memory_get_peak_usage(true)/1024/1024), 2),
    'timeSecs' => script_run_time()
];
