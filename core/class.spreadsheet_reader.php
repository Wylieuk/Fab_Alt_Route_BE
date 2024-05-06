<?php defined("isInSideApplication")?null:die('no access');
require 'vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\IOFactory;

#[AllowDynamicProperties]
class spreadsheet_reader{

    private $defaultConfig = [
        'dataOnly' => true,
        'ingnoreEmptyCells' => true,
        'rowChunkSize' => 10000, //max rows to read in each chunk
        'maxColumnsToRead' => 100,
        'headerRow' => 1,
        'startDataRow' => 1,
        'verifyHeaders' => true,
        'maxRowsToRead' => 100,
        'stopOnEmptyRow' => false,
        'columns' => [
            'A' => ['name' => 'header1', 'type' => 'string'], 
            'B' => ['name' => 'header1', 'type' => 'string'], 
            'C' => ['name' => 'header1', 'type' => 'string'],  
            'D' => ['name' => 'header1', 'type' => 'string'],  
            'E' => ['name' => 'header1', 'type' => 'string'], 
        ]
    ];

    private $maxRows;
    private $maxColumns;

    public function __construct($config){
        $this->config = (object)array_merge( $this->defaultConfig, $config);
    }

    public function getSheets($fileName, $type){
        $reader       = IOFactory::createReader(ucfirst($type));
        $data         = $reader->load($fileName);
        return $data->getSheetNames();
    }

    public function getHeaderRow($fileName, $type, $sheet, $row=1){
        
        $reader       = IOFactory::createReader(ucfirst($type));
        $data         = $reader->load($fileName);
        $sheetNames   = $data->getSheetNames();

        if(!in_array($sheet, $sheetNames)){
            return [];
        }

        $fstSheet     = $data->getSheetByName($sheet);
        $maxCol       = $fstSheet->getHighestDataColumn();
        $columnLetter = 'A';
        $iterations   = 100;

        $maxCol++;
        while ($columnLetter != $maxCol && $iterations-- > 0){
            $data = $fstSheet->getCell($columnLetter . $row)->getValue();
            if(empty($data)){
                break;
            }
            $headers[$columnLetter++ . $row] = $data;
        }

        return $headers ?? [];
    }

    
    public function load($fileName, $type, progress $progress=null){

        $reader = IOFactory::createReader($type);

        if ($this->config->dataOnly){
            $reader->setReadDataOnly(true);
        }

        if ($this->config->ingnoreEmptyCells){
            $reader->setReadEmptyCells(false);
        }


        $chunkFilter = new chunkReadFilter();
        $reader->setReadFilter($chunkFilter);


        $data = [];
        $currentRow = $this->config->startDataRow;

        round($chunksSize = 50 / ($this->config->maxRowsToRead / $this->config->rowChunkSize));
        $progressPercent = 10;

        //read the file in chunks to save memory
        for ($startRow = $this->config->startDataRow; $startRow <= $this->config->maxRowsToRead; $startRow += $this->config->rowChunkSize) {

            
            $progress->set($progressPercent, 'Reading file..', base64_encode($fileName));

            $progressPercent = $progressPercent + ((($chunksSize/$this->config->maxRowsToRead) * $startRow )/0.5);

            $chunkFilter->setRows($startRow, $this->config->rowChunkSize);
            $this->spreadsheet = $reader->load($fileName);
        

            foreach ($this->spreadsheet->getSheetNames() as $sheetName){
                $currentRow       = 1;

                if(empty($this->config->sheets[$sheetName])){
                    throw new Exception("`$sheetName` missing from config");
                }

                $sheetConfig      = (object)$this->config->sheets[$sheetName];

                $data[$sheetName] = [];

                if($sheetConfig->readTitleRow){
                    $data[$sheetName][] = ['title' =>  implode(' ', $this->getHeaderRow($fileName, $type, $sheetName, $sheetConfig->titleRow))];
                }

                $sheet = $this->spreadsheet->getSheetByName($sheetName);
                $this->maxRows = $sheet->getHighestDataRow();
                $this->maxColumns = $sheet->getHighestDataColumn();

                if ($sheet->getHighestDataColumn() != current(array_keys(array_reverse($sheetConfig->columns)))){
                    throw new Exception("Sheet: {$sheetName} | Column data count not as expected, expecting max column ".current(array_keys(array_reverse($sheetConfig->columns)))." found {$sheet->getHighestDataColumn()}");
                };

                while ($currentRow <= $this->maxRows && $currentRow <= $sheetConfig->maxRowsToRead ){


                    //verify headers
                    if ($currentRow == $sheetConfig->headerRow){


                        foreach (array_keys($sheetConfig->columns) as $columnLetter){
                            if ($sheetConfig->verifyHeaders){    

                                if(!isset($sheetConfig->columns[$columnLetter])){
                                    throw new Exception("Sheet: {$sheetName} | Column heading: {$columnLetter}:{$sheetConfig->headerRow} `{$sheet->getCell($columnLetter . $sheetConfig->headerRow)->getValue()}` is invalid");
                                }
                                if(strtolower($sheet->getCell($columnLetter . $sheetConfig->headerRow)->getValue() ?? '') !== strtolower($sheetConfig->columns[$columnLetter]['name'] ?? '')){

                                    throw new Exception("Sheet: {$sheetName} | Column heading: {$columnLetter}:{$sheetConfig->headerRow} `{$sheet->getCell($columnLetter . $sheetConfig->headerRow)->getValue()}` is invalid or missing");
                                }
                            }
                        }

                    }

                    //start reading rows
                    if ($currentRow >= $sheetConfig->startDataRow){

                    //foreach (range('A', $this->maxColumns) as $columnLetter){

                        foreach (array_keys($sheetConfig->columns) as $columnLetter){

                            $value = $sheet->getCell( $columnLetter . $currentRow )->getValue() ?? '';

                            if (is_string($value)) {
                                $value = trim(trim($value, '£'));
                            }

                            $key = $sheetConfig->columns[$columnLetter]['translatedkey'] ?? str_replace(' ', '_', strtolower($sheetConfig->columns[$columnLetter]['name'] ?? $columnLetter ?? ''));
                            
                            if (!$sheetConfig->columns[$columnLetter]['valid']($value, $sheet, $currentRow)) {
                                $data[$sheetName][$currentRow]['error'][] = $sheetConfig->columns[$columnLetter]['name'] ?? $columnLetter;
                                $data[$sheetName][$currentRow][$key] = $value;
                            } else {

                                $value = $sheetConfig->columns[$columnLetter]['preProcess']($value, $sheet, $currentRow);

                                switch ($sheetConfig->columns[$columnLetter]['type'] ?? null){

                                    case 'date';

                                        if(strpos(timestamp::db_format($value), '1970-01-01') === false){
                                            $data[$sheetName][$currentRow][$key] = timestamp::db_format($value);
                                        } else {
                                            $data[$sheetName][$currentRow][$key] = timestamp::db_format(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
                                        }

                                    break;

                                    case 'url';
                                        $data[$sheetName][$currentRow][$key] = urlencode($value);
                                    break;

                                    default;
                                        $data[$sheetName][$currentRow][$key] = $value;
                                    break;
                                }
                                
                            }
        
                        }
                        
                        if ($sheetConfig->stopOnEmptyRow){

                            $t = $data[$sheetName][$currentRow];
                            unset($t['error']);

                            if (count(array_filter($t, function($r) use($data){return !empty($r);})) < 1){
                                unset($data[$sheetName][$currentRow]);
                                break;
                            }

                        }
                    }

                    

                    $currentRow++;

                }
                
                $data[$sheetName] = array_values($data[$sheetName]);

            }

        }

        return $data;

    }

}

class chunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    private $startRow = 0;
    private $endRow   = 0;

    /**  Set the list of rows that we want to read  */
    public function setRows($startRow, $chunkSize) {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $chunkSize;
    }

    public function readCell($columnAddress, $row, $worksheetName = '') {
        //  Only read the heading row, and the configured rows
        if (($row == 1) || ($row >= $this->startRow && $row < $this->endRow)) {
            return true;
        }
        return false;
    }
}
