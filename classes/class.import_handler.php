<?php defined("isInSideApplication")?null:die('no access');

class import_handler{

    public  $result = null;
    public  $insertCount = 0;
    private $readerConfig;
    private $uploaderSettings;
    private $data;
    private $type;
    public $baseCrs;
    public $baseName;
    public $importedData;


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
            $titleRow = explode("-", array_shift($this->data)['title'] ?? '');
            $this->baseCrs  = trim($titleRow[0] ?? '');
            $this->baseName = trim($titleRow[1] ?? '');
            
        }

        if(empty($this->baseCrs) || strlen($this->baseCrs) != 3){
            throw new Exception("Error importing file, missing main CRS code, first line in the TAB must start with the CRS code");
        }

        $type = str_replace(' ', '_', strToLower($type ?? '')).'_import_handler';

        $this->handleImport();

    }


    public function handleImport(){

        $this->importedData = [];

        if(empty($this->readerConfig['targetClass'])){
            throw new Exception("No import class set in config for sheet {$this->type}. Please check the config file ");
        }

        if(!class_exists($this->readerConfig['targetClass'])){
            throw new Exception("No import class for {$this->readerConfig['targetClass']} {$this->type}. Please check the config file ");
        }

        // if(!empty($this->uploaderSettings['overwriteData']) && $this->uploaderSettings['overwriteData']){
        //     $_targetClass = new $this->readerConfig['targetClass']([]);
        // }

        
        foreach($this->data as $data){
            $this->importedData[] = new $this->readerConfig['targetClass'](['from_crs' => $this->baseCrs,  ...$data]);
        }



        if(isset($this->readerConfig['postProcess']) && is_callable($this->readerConfig['postProcess'])){
            $this->importedData = $this->readerConfig['postProcess']($this->importedData);
        }

    }

}