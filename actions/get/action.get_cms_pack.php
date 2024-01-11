<?php defined("isInSideApplication")?null:die('no access');

if(empty($this->data['offer_id'])){
    throw new Exception("Missing offer Id");
}

$offer = offer::fetch($this->data['offer_id']);
$offer->campaign_name =  campaign::fetch($offer->campaign_id)->{'db_id'};

foreach($offer->images as $imageName => $image){
    $offer->{$imageName} = json_encode($image['meta']);
}

$b64Images = $offer->images;

$ignoreList = [
    "id",
    "attraction_id",
    "campaign_id",  
    "timestamp",
    "vendor_id",
    "_table",  
    //"vendor",
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

foreach($offer as &$prop){
    if (is_array($prop)){
        $prop = implode(', ', $prop);
    }
}

if ($zip->open("{$filename}", ZipArchive::CREATE)!==TRUE) {
    throw new Exception("cannot open {$filename}");
}

// add promocodes
if(!empty($offer['promoCode'])){
    $zip->addFromString("{$offer['attraction_name']} promocodes.csv", csv::fromArray(array_fill(0, 5000, [$offer['promoCode']])));
}

//offer_databse.csv
$zip->addFromString("{$offer['attraction_name']} offer_databse.csv", csv::fromArray([array_keys($offer), array_values($offer)]));

//offer_human_readable.csv
$rows = [];
foreach ($offer as $k => $v){
    $rows[] = [$k, $v];
}
$zip->addFromString("{$offer['attraction_name']} offer_human_readable.csv", csv::fromArray($rows));
unset($rows);

$offerName =  str_replace(' ', '-', ucwords($offer['name']));




if(!empty($b64Images['image_main']) && !empty($b64Images['image_main']['data'])){
    //featured image
    $imf = new image_functions;
    $imf->resize([
        'imgBlob' => base64_decode($b64Images['image_main']['data']),
        'width' => 269,
        'height' => 203,
        'crop' => true,
        'caption' => '',
        'outputType' => 'jpg'
    ]);
    $zip->addFromString("{$offerName}-Featured.jpg", $imf->resizedImage);

    //detailed image
    $imf = new image_functions;
    $imf->resize([
        'imgBlob' => base64_decode($b64Images['image_main']['data']),
        'width' => 618,
        'height' => 350,
        'crop' => $b64Images['image_main']['meta']->crop ?? true,
        'caption' => $b64Images['image_main']['meta']->caption ?? '',
        'outputType' => 'jpg'
    ]);
    $zip->addFromString("{$offerName}-Detail.jpg", $imf->resizedImage);
}

unset($b64Images['image_main']);

// add images
foreach ($b64Images as $k => $b64Image){

  
    if(!empty($b64Image['data'])){

        $imf = new image_functions;
        //debug("{$offerName}-ADD-{$k}.jpg");
        $imf->resize([
            'imgBlob' => base64_decode($b64Image['data']),
            'width' => 618,
            'height' => 350,
            'crop' => $b64Image['meta']->crop ?? true,
            'caption' => $b64Image['meta']->caption ?? '',
            'outputType' => 'jpg'
        ]);
        
        $k = str_replace('image_', '', $k) - 1;

        $zip->addFromString("{$offerName}-ADD-{$k}.jpg", $imf->resizedImage);
    }
    
}


//add json
$zip->addFromString("{$offer['attraction_name']} attraction_database.json", json_encode($offer));

$zip->close();

headers::zipfile($filename, $offer['campaign_name'].'_'.$offer['attraction_name'].'_'.$offer['name'].'_'.'UMBRACO_PACK.zip');

echo(file_get_contents($filename));

unlink($filename);

exit;