<?php defined("isInSideApplication")?null:die('no access');

class report{

    private $type;
    private $ids;
    private $fields;
    private $query;
    private $queryParams;
    private $result;


    function __construct(string $type, array $ids, object $fields){
        $this->type   = $type;
        $this->ids    = $ids;
        $this->fields = $fields;

        $this->buildDbCall();

    }

    function getResultSet(){
        return $this->result;
    }


    function buildDbCall(){

        global $config;

        $db = new db;

        $joins   = [];
        $wheres  = '';
        $columns = [];
        $table = '';


        // build joins
        switch($this->type){

            case 'user':
                $table = "{$config['coreTablePrefix']}users";
                $tableRef = "users";
                if(in_array('redemptions/month', $this->fields->offer)){
                    $monthNumberNow = date('n', time());
                    foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $monthNumber=>$monthName){
                        $monthNumber++;
                        $thisYear       = date('Y');
                        $lastYear       = date('Y', time() - ONEYEAR);
                        $year           = $monthNumber <= $monthNumberNow ? $thisYear : $lastYear;
                        $allowedColumns["offers.redemptions_{$monthName}{$year}"] = true;
                        $columns[] = "IFNULL((SELECT SUM(redemptions.`count`) AS 'count' FROM `redemptions` WHERE `offer_id` = `offers`.`id` AND MONTH(`timestamp`) = '{$monthNumber}' AND timestamp > DATE_SUB(NOW(),INTERVAL 1 YEAR)), 0) AS 'offers.redemptions_{$monthName}{$year}'";
                    }
                }

                if(in_array('redemptions', $this->fields->offer)){
                    $columns[] = "IFNULL((SELECT SUM(redemptions.`count`) AS 'count' FROM `redemptions` WHERE `offer_id` = `offers`.`id`), 0) AS 'offers.redemptions'";
                }
                $joins['attraction']    = "LEFT JOIN `attractions` ON `attractions`.`vendor_id` = `users`.id";
                $joins['offer']         = "LEFT JOIN `offers` ON `offers`.`attraction_id` = `attractions`.id";
                $joins['user_extended'] = "LEFT JOIN `{$config['coreTablePrefix']}users_extended` ON `{$config['coreTablePrefix']}users_extended`.user_id = `users`.`id`";
                break;

            case 'attraction': 
                $table = "attractions";
                $tableRef = "attractions";
                $columns[] = "`attractions`.`data` AS 'attractions.data'";
                if(in_array('redemptions/month', $this->fields->offer)){
                    $monthNumberNow = date('n', time());
                    foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $monthNumber=>$monthName){
                        $monthNumber++;
                        $thisYear       = date('Y');
                        $lastYear       = date('Y', time() - ONEYEAR);
                        $year           = $monthNumber <= $monthNumberNow ? $thisYear : $lastYear;
                        $allowedColumns["offers.redemptions_{$monthName}{$year}"] = true;
                        $columns[] = "IFNULL((SELECT SUM(redemptions.`count`) AS 'count' FROM `redemptions` WHERE `offer_id` = `offers`.`id` AND MONTH(`timestamp`) = '{$monthNumber}' AND timestamp > DATE_SUB(NOW(),INTERVAL 1 YEAR)), 0) AS 'offers.redemptions_{$monthName}{$year}'";
                    }
                }

                if(in_array('redemptions', $this->fields->offer)){
                    $columns[] = "IFNULL((SELECT SUM(redemptions.`count`) AS 'count' FROM `redemptions` WHERE `offer_id` = `offers`.`id`), 0) AS 'offers.redemptions'";
                }
                $joins['user']          = "LEFT JOIN `{$config['coreTablePrefix']}users` AS users ON `users`.id = `attractions`.`vendor_id`";
                $joins['offer']         = "LEFT JOIN `offers` ON `offers`.`attraction_id` = `attractions`.id";
                $joins['user_extended'] = "LEFT JOIN `{$config['coreTablePrefix']}users_extended` ON `{$config['coreTablePrefix']}users_extended`.user_id = `users`.`id`";
                break;

            case 'offer': 
                $table = "offers";
                $tableRef = "offers";
                $allowedColumns['offers.redemptions'] = true;
                $columns[] = "`offers`.`data` AS 'offers.data'";
                if(in_array('redemptions/month', $this->fields->offer)){
                    $monthNumberNow = date('n', time());
                    foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $monthNumber=>$monthName){
                        $monthNumber++;
                        $thisYear       = date('Y');
                        $lastYear       = date('Y', time() - ONEYEAR);
                        $year           = $monthNumber <= $monthNumberNow ? $thisYear : $lastYear;
                        $allowedColumns["offers.redemptions_{$monthName}{$year}"] = true;
                        $columns[] = "IFNULL((SELECT SUM(redemptions.`count`) AS 'count' FROM `redemptions` WHERE `offer_id` = `offers`.`id` AND MONTH(`timestamp`) = '{$monthNumber}' AND timestamp > DATE_SUB(NOW(),INTERVAL 1 YEAR)), 0) AS 'offers.redemptions_{$monthName}{$year}'";
                    }
                }

                if(in_array('redemptions', $this->fields->offer)){
                    $columns[] = "IFNULL((SELECT SUM(redemptions.`count`) AS 'count' FROM `redemptions` WHERE `offer_id` = `offers`.`id`), 0) AS 'offers.redemptions'";
                }
                $joins['attraction']    = "LEFT JOIN `attractions` ON `attractions`.`id` = `offers`.attraction_id";
                $joins['user']          = "LEFT JOIN `{$config['coreTablePrefix']}users` AS users ON `users`.id = `attractions`.`vendor_id`";
                $joins['user_extended'] = "LEFT JOIN `{$config['coreTablePrefix']}users_extended` ON `{$config['coreTablePrefix']}users_extended`.user_id = `users`.`id`";
                break;

            default:
                throw new Exception('`$type` Unknown');
        }
        $joins = implode("\n", $joins);

        $availableColumns['user']          = $db->get_column_names("{$config['coreTablePrefix']}users", false);
        $availableColumns['attraction']    = $db->get_column_names('attractions', false);
        $availableColumns['offer']         = $db->get_column_names('offers', false);
        $availableColumns['user_extended'] = $db->get_column_names('users_extended', false);


        //build columns
        $columns[] = "(SELECT `data` FROM `{$config['coreTablePrefix']}users_extended` WHERE `user_id` = `users`.id LIMIT 1) AS 'users.data'";
        $columns[] = "`attractions`.`data` AS 'attractions.data'";
        $columns[] = "`offers`.`data` AS 'offers.data'";

        foreach($this->fields as $section => $fields){
            foreach ($fields as $field){
                if(in_array($field, $availableColumns[$section])){
                    $columns[] = "`{$section}s`.`{$field}` AS '{$section}s.{$field}'";
                }

                $allowedColumns[$section.'s.'.$field] = true;
            }
        }

        

        $columns = implode(",\n", $columns);


        $this->queryParams = array_combine(array_map(fn($e) => "_{$e}_", $this->ids), $this->ids);
        $where = "`{$tableRef}`.`id` in(:".implode(', :',array_keys($this->queryParams)).")";

        $this->query = "
            SELECT DISTINCT
            {$columns}
            FROM `{$table}` {$tableRef}
            {$joins}
            WHERE
            {$where}
        ";

        //build the data unwrapping the data columns
        $results = [];
        $_result = [];


        foreach($db->preparedQuery($this->query, $this->queryParams)->fetch_array() ?? [] as $row){


            foreach($row as $colName=>$colValue){

                if(in_array($colName, ['offers.data', 'attractions.data'])){
                    continue;
                }

                if(empty($results[$colName])){
                    $_result[$colName] = $colValue;
                }
            }

            foreach(json_decode($row['users.data'] ?? '{}') as $dataRowkey=>$dataRowValue){
                $_result['users.'.$dataRowkey] = !is_array($dataRowValue) ? $dataRowValue : implode(', ', $dataRowValue);
            }

            foreach(json_decode($row['offers.data'] ?? '{}') as $dataRowkey=>$dataRowValue){
                $_result['offers.'.$dataRowkey] = !is_array($dataRowValue) ? $dataRowValue : implode(', ', $dataRowValue);
            }
            
            foreach(json_decode($row['attractions.data']?? '{}') as $dataRowkey=>$dataRowValue){
                $_result['attractions.'.$dataRowkey] = !is_array($dataRowValue) ? $dataRowValue : implode(', ', $dataRowValue);
            }

            if(isset($_result['offers.redemptions'])){
                $_result['offers.redemptions'] = (int)$_result['offers.redemptions'];
                
            }

            $_result = array_intersect_key($_result, $allowedColumns);
            
            ksort($_result, SORT_REGULAR);

            $results[] = $_result;

            $_result = [];
        }


        

        //make sure all rows have same columns
        //find all columns used
        foreach($results as $row){
            foreach($row as $colName=>$colValue){
                $allcolumns[] = $colName;
            }
        }

        $allcolumns = array_unique($allcolumns);

        foreach($results as $row){

            $sortedRow = [];

            foreach ($allcolumns as $column){

                if(!isset($row[$column])){
                    $row[$column] = null;
                }
                if(str_starts_with($column, 'users')){
                    $sortedRow['users'][$column] = $row[$column];
                }
                if(str_starts_with($column, 'attractions')){
                    $sortedRow['attractions'][$column] = $row[$column];
                }
                if(str_starts_with($column, 'offers')){
                    $sortedRow['offers'][$column] = $row[$column];
                }
            }

            $this->result[] = array_merge(($sortedRow['users'] ?? []) + ($sortedRow['attractions'] ?? []) + ($sortedRow['offers'] ?? []));
           
        }

        $this->result = array_map("unserialize", array_unique(array_map("serialize", $this->result)));        

    }

}