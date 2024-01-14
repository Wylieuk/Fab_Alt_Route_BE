<?php defined("isInSideApplication")?null:die('no access');

class import_handler{

    public  $result = null;
    public  $insertCount = 0;
    private $readerConfig;
    private $uploaderSettings;
    private $data;
    private $type;
    private $baseCrs;


    public function __construct(string $type, array $data, array $readerConfig, array $uploaderSettings=[]){


        $this->type             = $type;
        $this->readerConfig     = $readerConfig['sheets'][$type];
        $this->data             = $data;
        $this->uploaderSettings = $uploaderSettings;

        $allowTypes = array_keys($readerConfig['sheets']);

        if(!in_array($type, $allowTypes)){
            throw new Exception("Sheet name {$type} it not allowed");
        }

        $sheetConfig = $readerConfig['sheets'][$type];

        if($sheetConfig['titleRow']){
            $titleRow = array_shift($this->data);
            $this->baseCrs = substr($titleRow['title'], 0, 3) ?? '';
        }


        $type = str_replace(' ', '_', strToLower($type ?? '')).'_import_handler';

        
        $this->insertCount = $this->handleImport();

    }


    public function handleImport(){

        $insertCount = 0;

        if(empty($this->readerConfig['targetClass'])){
            throw new Exception("No import class set in config for sheet {$this->type}. Please check the config file ");
        }

        if(!class_exists($this->readerConfig['targetClass'])){
            throw new Exception("No import class for {$this->readerConfig['targetClass']} {$this->type}. Please check the config file ");
        }

        if(!empty($this->uploaderSettings['overwriteData']) && $this->uploaderSettings['overwriteData']){
            $_targetClass = new $this->readerConfig['targetClass']([]);
            $_targetClass->purgeAll($this->baseCrs);
        }

        foreach($this->data as $data){
            $targetClass = new $this->readerConfig['targetClass'](['from_crs' => $this->baseCrs,  ...$data]);
            $targetClass->save();
            $insertCount++;
            $targetClasss[] = $targetClass;
        }

        return $insertCount;

    }

}