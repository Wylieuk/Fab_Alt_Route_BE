<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['offer_id'])){
    throw new Exception("Missing offer Id");
}

$offer = offer::fetch($this->data['offer_id']);

$offer->campaign_name =  campaign::fetch($offer->campaign_id)->{'db_id'};


$b64Images = $offer->images;

$ignoreList =[
    "id",
    "attraction_id",
    "campaign_id",  
    "timestamp",
    "vendor_id",
    "_table",  
    "vendor",
    "pending_id",
    //"attraction_name",
    "approved_version_id",
    // "name",
    // "offerType" ,
    // "startDate",
    // "endDate",
    // "offerTimes",
    // "exclusions",
    // "address",
    // "postcode",
    // "closestStation",
    // "directions",
    // "copyForLeaflet",
    // "visitorContactNumber"  078671,
    // "url",
    // "allowOnlineBooking",
    // "onlineBookingUrl",
    // "genericPromoCode",
    // "promoCode",
    // "accessibilityUrl",
    "diff",
    "pending_data",
    "images",
 
];


foreach ($ignoreList as $ignore){
    unset($offer->{$ignore});
}


$offer = json_decode(json_encode($offer), JSON_OBJECT_AS_ARRAY);

//debug($offer);

if(!is_dir('temp')){
    throw New Exception("temp does not exist");
}

$zip      = new ZipArchive();
$filename = "temp/".session_id().".zip";
if(file_exists($filename)){
    unlink($filename);
}


if ($zip->open("{$filename}", ZipArchive::CREATE)!==TRUE) {
    throw new Exception("cannot open {$filename}");
}

foreach($offer as &$prop){
    if (is_array($prop)){
        $prop = implode(', ', $prop);
    }
}


//offer_databse.csv
$zip->addFromString("offer_databse.csv", csv::fromArray([array_keys($offer), array_values($offer)]));

//offer_human_readable.csv
$rows = [];
foreach ($offer as $k => $v){
    $rows[] = [$k, $v];
}
$zip->addFromString("offer_human_readable.csv", csv::fromArray($rows));
unset($rows);

// add images
foreach ($b64Images as $k => $b64Image){
    if(!empty($b64Image['data'])){
        $imagick = new Imagick();
        //debug($b64Image);exit;
        $imagick->readImageBlob(base64_decode($b64Image['data']));
        $imagick->setFormat('jpeg');
        $zip->addFromString("img_{$k}.jpg", $imagick->getImageBlob());
    }
}

//add json
$zip->addFromString("attraction_database.json", json_encode($offer));

$zip->close();

headers::zipfile($filename, $offer['campaign_name'].'_'.$offer['attraction_name'].'_'.$offer['name'].'_'.'TOC_PACK.zip');

echo(file_get_contents($filename));

unlink($filename);


global $user;
$log = new log([
    'component'    => 'offer',
    'component_id' => $this->data['offer_id'],
    'details'      => 'pack downloaded',
    'user_id'       => $user->id
]);
$log->save();


exit;