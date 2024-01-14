<?php



class menu{

    public  $items = [];

    private $tables;

    function __construct(){
        global $config;

        $this->tables = (object)[
            'baseTable' => $config['coreTablePrefix'].'menu_items',
            'menuSections' => $config['coreTablePrefix'].'menu_sections',
        ];

        foreach($this->tables as &$table){
            $table = $config['appTablePrefix'] . $table;
        }
    }

    /*
    * t: Gets menu items for a specific user group
    *//**
    * @param int $userGroupId of the group you want to get menu items for
    * @return void
    ***************************************/
    public function getMenu($userGroupId){

        global $config;

        $db = new db;

        $query = "  SELECT * FROM `{$this->tables->baseTable}` b
                        left join `{$this->tables->menuSections}` as s ON `s`.`id` = b.`section_id`
                    WHERE 
                        JSON_CONTAINS(b.`user_groups`, :userGroupId)";
        

        if ($result = $db->preparedQuery($query, ['userGroupId' => $userGroupId])->fetch_array()){
            $this->items = $this->getStructure($result);
        }

    }


    private function getStructure($raw){

        $items = [];

        foreach ($raw as $menuItem){
            $items[$menuItem['section_name']][] = $menuItem;
        }

        return $items;

    }


}