<?php
defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class db {
	private $dbhObject;
	
	/*
	* initalise the db object 
	***************************************/
	function __construct(string $server=null, string $database=null, string $username=null, string $password= null) {
		global $config;
		//global $connect;
		$this->database_name 		= $database ?? $config['database'];
		$this->dbhObject 			= $this->connect($server ?? $config['server'], $database ?? $config['database'], $username ?? $config['database_login'], $password ?? $config['database_password']);	
		$this->transactionStarted 	= false;
	}

	/*
	* connect once to the database per application instance.
	***************************************/
	private function connect(string $server, string $database, string $username, string $password){
		global $db__connect;
		if (!isset($db__connect[$database])) { //only ever connect once per application (must have <global $connect> prior to this)
			$dsn = 'mysql:host='. $server.';dbname='. $database.';charset=UTF8';
			try {
				$db__connect[$database] = new PDO($dsn, $username, $password);

				$tz = (new DateTime('now', new DateTimeZone('Europe/London')))->format('P');
				$db__connect[$database]->exec("SET time_zone='$tz';");
				//comment( 'connected to database: '. $this->database_name);
			}
			catch(Exception $e){
				trigger_error( $e->getMessage());
				exit;
			}
        }
        //$connect->query("SET group_concat_max_len = 2048");

		return $db__connect[$database];
	}

	/*
	* $db->preparedQuery('select * from `location_reference` where id=:id AND name=:name', array('id'=>9, 'name'=>'somename'));
	***************************************/	
	function preparedQuery(string $query_string, array $values=array(), bool $debug=false, bool $named=true){
        global $config;

        $timeStart = microtime(true);
	

		$namedValues = array();
		if ($named or strpos($query_string, ':') !== false){	
			//add ':' to array keys if needed;
			foreach($values as $key=>$value){
				if(strpos($key,':') === 0){
					$namedValues[$key] = ($value !== null ? html_entity_decode($value) : null); 
				}
				else{
					$namedValues[':'.$key] = ($value !== null ? html_entity_decode($value) : null);
				}
			}
			$values = $namedValues;
        }
        
		try {
			$statement	= $this->dbhObject->prepare($query_string);
		}
		catch (Exception $e) {
			if($this->transactionStarted){$this->rollBack();}
			trigger_error( $e->getMessage());
			exit;
        }
        
		if ($debug) {
            $boundStatement = $statement->queryString;
            foreach ($values as $k=>$v){
                $boundStatement = str_replace($k, '"'.$v.'"', $boundStatement);
            }
            
            debug($boundStatement, 'Boundstatement');
			debug($statement->queryString, 'Orignalquery');
            debug($values, "Params");

		}
		try {
			if(!$outcome = $statement->execute($values)){
                if($this->transactionStarted){$this->rollBack();}
                $boundStatement = $statement->queryString;
                foreach ($values as $k=>$v){
                    $boundStatement = str_replace($k, '"'.$v.'"', $boundStatement);
                }
                if ($config['show_errors'] && ini_get('display_errors') && ini_get('display_errors') !== 'off') {
                    debug($boundStatement, 'Boundstatement');
                    debug($statement->queryString, 'Orignalquery');
                    debug($values, "Params");

                }

                trigger_error($statement->errorInfo()[2]);//$statement->debugDumpParams() . '</br>' . $statement->errorInfo()[2] );
				exit;
            }
            if ($debug) {
                debug($outcome, 'Outcome');
            }
		}
		catch (Exception $e) {
            
			if ($this->transactionStarted) {$this->rollBack();}
			trigger_error($e->getMessage() ?? 'unknown error');
			exit;
        }
        if ($debug) {
            debug('run time '.  round((microtime(true) - $timeStart), 5). ' secs');
        }
        
        $this->queryResult = $statement;
        return $this;
    }
	/*
	* legacy query function
	! use preparedQuery when possible
	***************************************/
	function query($query_string, $strip_html = false, $search_size_limit = true, $excape = false){
		global $config;
		try {
			$queryResult = $this->dbhObject->query($query_string);
		}
		catch(Exception $e){
			trigger_error( $e->getMessage());
			exit;
		}

		//debug($query_string);
		if($queryResult){
			$this->queryResult = $queryResult;

            return $this;
			//return $queryResult;
		}
		else{
			trigger_error( $query_string.'</br>'. $this->dbhObject->errorInfo()[2]);
			return false;
		}
	}

	/*
	* $db->fetch_array() => returns array of rows from dd
	***************************************/
	function fetch_array($a='',$b='',$c=''){ //a,b,c there for backwards compatiblity only
		//comment($this->queryResult->rowCount());
		$rowNumber = 0;
		if ($this->queryResult->rowCount() > 0){
			while($rowNumber++ < $this->queryResult->rowCount()){
				$result_array[] = $this->queryResult->fetch(PDO::FETCH_ASSOC);
			}

			foreach($result_array as &$row){
				foreach ($row as &$value){
					if(is_numeric($value) && substr($value, 0, 1) > 0 && substr($value, 0, 1) <= 9){
						$value = 0 + $value;
					}
				}
			}
			return $result_array;
		}
		return null;
	}
	
	/*
	* $db->clean(variable) => returns cleaned string 
	***************************************/
	function clean($part_query_str, $strip_wildcards=true){
		$part_query_str 	= trim($part_query_str);
		$cleaned_str 		= $this->dbhObject->quote($part_query_str);
		return trim($cleaned_str, '\'');
	}
	
	/*
	* internally used function
	***************************************/
	private function add_quotes($part_query_str){
		$part_query_str = $this->clean($part_query_str);
		return '"'.trim($part_query_str).'"';
	}
	
	/*
	* internally used function
	***************************************/	
	private function add_ticks($part_query_str){
		return '`'.trim($part_query_str).'`';
	}
	
	/*
	* returns status of tables
	***************************************/
	function data_base_status(){
		global $config;
		$this->dbresource = $this->query("SHOW TABLE STATUS");
		return $this->fetch_array();
	}	
	
	/*
	* returns the number of rows affected by the last query
	***************************************/	
	function affected_rows(){
		global $config;
		return $this->queryResult->rowCount();
	}
	
	/*
	* returns the auto increment value for the last insert query
	***************************************/
	function insert_id(){
		return $this->dbhObject->lastInsertId();
	}
	
	/*
	* returns the next auto increment value for the table
	***************************************/
	function currentAutoIncrementValue($table){
		global $config;
		$query =  'SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "'.$config['database'].'" AND TABLE_NAME = "'.$table.'"';	
		$this->query($query);
		$result = $this->fetch_array();
		return $result[0]['AUTO_INCREMENT'];
	}
	
	/*
	* returns the error text for the last insert query
	***************************************/
	function error(){
		return $this->dbhObject->errorInfo()[2];
	}	
 
	/*
	* return the error number for the last insert query
	***************************************/
	function errno(){
		return $this->dbhObject->errorCode();
	}	
	
    /*
    *//**
    * @param table string table name
    * @param data array array of key value pairs to write to the databse row.
    * @param createPreparedStatement bool   true => returns $query array(['statement'],['values']) / false => returns $query(string)
    * @return array 'statement' 'values';
    ***************************************/
	function build_insert(string $table, array $data, $createPreparedStatement = true, $checkForeignKeyConstraints = false){
		$foreignKeyConstraints = false;
		if ($checkForeignKeyConstraints) {
			$foreignKeyConstraints = $this->findForeignKeyConstraints($table);

			//separtate out refernce key from fields
			$reference['keyValue'] 		= $data[$foreignKeyConstraints[0]['COLUMN_NAME']];
			$reference['keyName'] 		= $foreignKeyConstraints[0]['COLUMN_NAME'];
			$reference['tableName'] 	= $foreignKeyConstraints[0]['REFERENCED_TABLE_NAME'];
			//debug($reference);
			unset($data[$foreignKeyConstraints[0]['COLUMN_NAME']]);
		}
		$table 	= strtolower($table);
		$data 	= $this->delete_fields_not_in_table($table, $data);
		$fields = array_keys($data);
		$values = $data;

        // foreach ($values as &$value){
        //     if (!empty($value) && is_string($value)){
        //         $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5);
        //     }
        // }

		foreach ($fields as $key => $field) {
			$fields[$key] = $this->add_ticks($field);
		}
		if (!$createPreparedStatement) {
			foreach ($values as $key => $value) {
				$values[$key] = $this->add_quotes($value);
			}
		}
		$insert = 	'insert into ' . $table . ' (';
		$insert .= 	implode(', ', $fields);
		$insert .= ')values(';
		if ($createPreparedStatement) {
			$insert .= 	':' . implode(', :', array_keys($data));
		} else {
			$insert .= 	implode(', ', $values);
		}
		$insert .= ')';
		$insert .= ' ON DUPLICATE KEY UPDATE ';
		foreach ($values as $key => $value) {
			if ($createPreparedStatement) {
				$updates[] = '`' . trim($key) . '` = :' . trim($key);
			} else {
				$updates[] = '`' . trim($key) . '` = ' . $value;
			}
		}
		$updates[] = 'id=LAST_INSERT_ID(`' . $table . '`.`id`)'; //sets id in case of ON DUPLICATE KEY UPDATE
		$insert .= implode(', ', $updates);
		if ($createPreparedStatement) {
			return array('statement' => $insert, 'values' => $data);
		}
		return $insert;
	}
	
	/*
	* returns the largest value in column
	***************************************/
	function findMaxColumnValue($table, $column, $where = ''){
		$query = 'SELECT MAX('.$this->clean($column).') AS '.$column.' FROM '.$table.' '.$where;
		$this->query($query);
		$result = $this->fetch_array();
		return $result[0][$column];
	}
		
	/*
	* returns update query 
	***************************************/
	function build_update($table, $data, $where){
		$table = strtolower($table);
		$data = $this->delete_fields_not_in_table($table, $data);
		$update  = 'UPDATE `'.$table.'` SET ';
		foreach ($data as $fieldname => $value){
			$updates[] = $this->add_ticks($fieldname).' = '.$this->add_quotes($value);
		}
		$update .= implode(', ',$updates).' '.$where;
		return $update;
	}

	/*
	* return table exists true/false
	***************************************/
	function table_exists($table){
		$query = 'SHOW TABLES LIKE "'.$table.'"';
		if ($this->query($query)){$results = $this->fetch_array();
			return $results;
		}
		return false;
	}
	
	/*
	* return formatted time to suit database timestamp format yyyy/mm/dd h:m:s
	***************************************/
	static function database_time_format($timestamp){
		if (strpos($timestamp, '/') !== false){
			$bits = explode('/',$timestamp);
	
			//$date = $bits[1].'/'.$bits[0].'/'.$bits[2];
			$date = $bits[0].'-'.$bits[1].'-'.$bits[2];
			//echo $date.PHP_EOL;
			$timestamp = strtotime($date);
			//echo $timestamp.'**<br>';
		}
		if (is_numeric($timestamp)){
			return date("Y-m-d H:i:s", $timestamp);
		}
		return date("Y-m-d H:i:s", strtotime($timestamp));
	}
	
	/*
	* returns $config['date_format'] formatted timestamp
	***************************************/
	static function format_time($mysql_timestamp){
		global $config;
		$unix_timestamp = strtotime($mysql_timestamp);
		return date((string)$config['date_format'], $unix_timestamp);
	}


    /*
    * builds a concat string to add as a select item this is used to create a json string from results as SELECT ITEM
    * @params array ['id' => 'H.`id`','url' => 'H.`url`','text' => 'H.`text`'] 
    * return 'CONCAT("[", GROUP_CONCAT(CONCAT("{\"id\": \"", H.`id`, "\", \"url\": \"", H.`url`, "\", \"text\": \"", H.`text`, "\"}")), "]")'
    ***************************************/
    function jsonObjectArray(array $array, bool $distinct = true){

        if ($distinct){
            return 'CONCAT("[",GROUP_CONCAT(DISTINCT '.$this->jsonObject($array).'),"]")';
        } 
        else {
            return 'CONCAT("[",GROUP_CONCAT('.$this->jsonObject($array, '').'),"]")';
        }
    }

    /* 
    * @params 'I.`id`'
    * returns 'CONCAT("[", GROUP_CONCAT('I.`id`'), "]")'
    ***************************************/
    function jsonStringArray(string $arrayElement, bool $distinct = true){
        if ($distinct){ 
            return 'CONCAT("[", GROUP_CONCAT(DISTINCT ' . $arrayElement . '), "]")';
        } 
        else {
            return 'CONCAT("[", GROUP_CONCAT(' . $arrayElement . '), "]")';
        }

    }

    function jsonObject(array $array){

        foreach ($array as $k => $v){
            $v = (is_array($v) || is_object($v)) ? $this->jsonObject($v) : $v;
            $b[] = '"' . $k. '", ' . $v;
        }

        return 'JSON_OBJECT('.implode((','), $b).')';
    }

	/*
	* inernal function to find column names in a table
	***************************************/
	function get_column_names($table, $ignoreVirtualColumns=true){
		global $cache;
		if (isset($cache[$this->database_name][$table]['columns'])){
			return $cache[$this->database_name][$table]['columns'];
		}
		$query = "SHOW COLUMNS FROM $table";
		if (!$this->query($query)){  return false;}
		$columns = $this->fetch_array();
		foreach ($columns as $column){
			if(!$ignoreVirtualColumns || ($ignoreVirtualColumns && strPos( $column['Extra'], 'VIRTUAL') !== 0 && strPos( $column['Extra'], 'STORED') !== 0)){
				$column_names[] =  $column['Field'];
			}
		}
		$cache[$this->database_name][$table]['columns'] = $column_names;
		return $column_names;
	}
	
	/*
	* deletes array members that do not match table columns
	***************************************/
	private function delete_fields_not_in_table($table, $data){
		$db_columns = $this->get_column_names($table);
		$filtered_data = array_intersect_key($data, array_flip($db_columns));
		return $filtered_data;
	}

	/*
	* transaction functions
	* do what they say on the tin.
	***************************************/
	function startTransaction(){
		$this->transactionStarted = true;
		return $this->dbhObject->beginTransaction();
	}
	function commit(){
		$this->transactionStarted = false;
		return $this->dbhObject->commit();
	}
	function rollBack(){
		$this->transactionStarted = false;
		return $this->dbhObject->rollBack();
    }
    
    function lock($table, $type='WRITE'){
        $this->query('LOCK TABLE '.$table.' '.$type);
    }

    function unlock(){
        $this->query('UNLOCK TABLES');
    }



	/*
	* internal function that finds foreign keys in table
	***************************************/
	private function findForeignKeyConstraints($table){
		global $cache;
		if (isset($cache[$this->database_name][$table]['foreignKeyConstraints'])){
			return $cache[$this->database_name][$table]['foreignKeyConstraints'];
		}
		$query = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE   TABLE_NAME = "'.$table.'" AND TABLE_SCHEMA = "'.$this->database_name.'" AND REFERENCED_TABLE_NAME IS NOT NULL';
		$this->query($query);
		$results = $this->fetch_array();
		if (!is_array($results)){return false;}
			foreach($results as $row){
				$foreignKeyConstraint['COLUMN_NAME'] = $row['COLUMN_NAME'];
				$foreignKeyConstraint['REFERENCED_TABLE_NAME'] = $row['REFERENCED_TABLE_NAME'];
				$foreignKeyConstraints[] = $foreignKeyConstraint;
			}
		$cache[$this->database_name][$table]['foreignKeyConstraints'] = $foreignKeyConstraints;
		return $foreignKeyConstraints;
	}


    /*
    *//**
    * @param table table to inser into
    * @param data array of arrays/object containing key value pairs to insert into db
    * @return 
    ***************************************/
    function buildbulkInsert(string $table, array $data){

        $insertCount = 0;
        $params = [];

        $fields = array_keys($this->delete_fields_not_in_table($table, current((array)$data)));

        $baseSql = 'INSERT INTO `' . $table . '` ( `' . implode("`,\r\n`", $fields).'`) VALUES '. PHP_EOL;

        $params = [];
        $inserts = [];
        $_t = [];
        $_dupeKeys = [];

        foreach ($data as $k =>  $l){

            foreach ($l as $fieldKey => $fieldValue){
                $_t['param' . $k . '_' . $fieldKey] = $fieldValue;
            }

            $insertCount ++;
            $params = array_merge($_t, $params);
            $inserts[] = '(:' . implode(', :', array_keys($_t)) . ')';
            $_t = [];
            
        }

        foreach ($fields as $field){
            
           $_dupeKeys[] = '`'.$field.'` = values(`'.$field.'`)'; 
        }

        $sql = $baseSql . implode(', ' . PHP_EOL, $inserts) . ' ON DUPLICATE KEY UPDATE ' . PHP_EOL . implode(', ' . PHP_EOL, $_dupeKeys);

        return ['statement' => $sql, 'values' => $params];

    }


    /*
    *//**
    * @param array|object $searchParams Example: ['crs' => 'VIC']
    * @param array|object $allowedParamsMap Example: ['crs' => '`crs` = :crs', 'id' => '`id` = :id']
    * @param string $operator [AND|OR] defaults to AND
    * @return object ["sqlWheres", "sqlParams"]
    ***************************************/
    function buildWheres($searchParams, $allowedParamsMap, string $operator = 'AND'): object{


        $allowedParamsMap = array_filter($allowedParamsMap, function($p){return !empty($p);});

        return (object)[
            "sqlWheres" => implode(" {$operator} " , array_intersect_key((array)$allowedParamsMap, (array)$searchParams)), 
            "sqlParams" => array_intersect_key((array)$searchParams, (array)$allowedParamsMap)
        ];
    }
	
}

?>