<?php
defined("isInSideApplication")?null:die('no access');

set_time_limit(60*60);

headers::accessControlAsRefer();
headers::allowCredencials();
headers::set('X-sessionId', session_id());
headers::expose(array('X-sessionId'));



$output = (object)[];
$output->error = false;

$allowedTypes = [
    'xlsx',
    'xls',
    'csv'
];


if (count($_FILES) > 0){

    foreach($_FILES as $file){

        $fileStringSegments = explode('.', $file['name']);
        $type = strtolower(end($fileStringSegments));

        if (!in_array(strToLower($type), $allowedTypes)){
            $output->error = 'Illegal file type';
            break;
        }

        $target_path = $config['documentroot'] . 'uploads'.DIRECTORY_SEPARATOR . session_id(). str_replace(' ', '_', basename($file['name']));
            
        if ($file['error']) {
            $output->error =  'There was a problem uploading file '.$file['name'];
        }
            
        if(move_uploaded_file($file['tmp_name'], $target_path)) {
            $output->response[] = $target_path;
        } else{
            $output->error = 'Unknown error with API';
        }
    }
}



// Send the JSON...
 
//headers::compression();
headers::json();
die(json_encode($output));