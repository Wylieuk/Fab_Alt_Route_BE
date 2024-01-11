<?php
defined("isInSideApplication")?null:die('no access');
class csv{
	
	#converts array to CSV string
	static function fromArray(array $array, string $delimiter = ",", string $enclosure = '"', string $escape_char = "\\"){
		$csv = '';
	    $buffer = fopen('php://temp', 'r+');
		foreach ($array as $fields){
   			fputcsv($buffer, $fields, $delimiter, $enclosure, $escape_char);
		}
    	rewind($buffer);
		while (!feof($buffer)){
    		$csv .= fgets($buffer);
		}
    	fclose($buffer);
    	return $csv;
	}

}