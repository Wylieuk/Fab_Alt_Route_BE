<?php defined("isInSideApplication")?null:die('no access');

global $config;

set_time_limit(60*60*5);

$processAttractons = true;
$processOffers     = true;
// if (!(user::hasAccess(array('admin')))) {
//     throw new Exception('Insufficant Access Rights');
// }

$old_db    = new db(server: 'localhost', database: 'old_toolkit', username: 'root', password: '36223');
$db_target = new db(server: 'localhost', database: 'awesome_2for1_migration_target', username: 'root', password: '36223');


 foreach(($db_target->preparedQuery("SELECT `db_id`, `id` FROM `campaigns`")->fetch_array() ?? []) as $row){
    $campaigns[$row['db_id']] = $row['id'];
 }





// * t: get submissions[]

$attractions = [];
foreach(
    $old_db->preparedQuery(
                "SELECT 
                    s.`id`,
                    s.`user_id` as 'vendor_id',
                    s.`db_id`,
                    s.`live`,
                    s.`timestamp`,
                    sp.`field_name`,
                    sp.`field_value`
                FROM `submission` s
                LEFT JOIN `submission_props` AS sp ON sp.`submission_id` = s.`id`
                ORDER BY s.`timestamp` ASC"
            )
            ->fetch_array() ?? []
            as $row
){
    $rows[$row['id']]['id']                       = $row['id'];
    $rows[$row['id']]['vendor_id']                = $row['vendor_id'];
    $rows[$row['id']]['live']                     = $row['live'];
    $rows[$row['id']]['db_id']                    = $row['db_id'];
    $rows[$row['id']]['timestamp']                = $row['timestamp'];
    if($row['field_name'] == 'category'){
        $rows[$row['id']]['data'][$row['field_name']][] = $row['field_value']; 
    }
    else{
        $rows[$row['id']]['data'][$row['field_name']] = $row['field_value']; 
    }

}

//group by vendor 

// * t: attractions
foreach($rows as $key => $row){

    if(empty($row['data']['campaign_name']) || empty($row['vendor_id']) || empty($row['data']['attraction_name'])){
        continue;
    }


    $attractions[$row['vendor_id']][$key] = [
        "id"                   => $row['id'],
        "vendor_id"            => $row['vendor_id'],
        "timestamp"            => $row['timestamp'] ?? null,
        "data"                 => json_encode([
            "name"                 => $row['data']['attraction_name'] ?? null,
            "region"               => [],
            "postcode"             => $row['data']['attraction_postcode'] ?? null,
            "category"             => $row['data']['category'] ?? null,
            "address"              => $row['data']['attraction_address'] ?? null,
            "closestStation"       => $row['data']['closest_station'] ?? null,
            "visitorContactNumber" => $row['data']['visitor_contact'] ?? null,
        ])
    ];

    $image_path = "/var/www/dev.fabcomms.co.uk/awesome_2for1/api/assets/attraction_images/submissions/" . $row['data']['db_id'] . '/';

    foreach([
        'image_1',
        'image_2',
        'image_3',
        'image_4',
    ] as $image){
        if (file_exists($image_path. ($row['data'][$image] ?? '__')) && is_file($image_path. ($row['data'][$image] ?? '__'))){
            $attractions[$row['vendor_id']][$key]['images'][$image] = $image_path . $row['data'][$image];
        }
    }

}


// * t: offers
foreach($rows as $key => $row){


    if(empty($row['data']['campaign_name']) || empty($row['vendor_id']) || empty($row['data']['attraction_name'])){
        continue;
    }

    if(empty($campaigns[$row['data']['campaign_name']])){
        continue;
    }



    $offers[$row['vendor_id']][$key] = [
        "vendor_id"            => $row['vendor_id'],
        "attraction_name"      => $row['data']['attraction_name'],
        "name"                 => !empty($row['data']['event_name']) ? $row['data']['event_name'] : $row['data']['attraction_name'],
        "attraction_id"        => $row['id'],
        "campaign_id"          => $campaigns[$row['data']['campaign_name']] ?? null,
        "live"                 => $row['live'] ?? null,
        "timestamp"            => $row['timestamp'] ?? null,
        "data"                 => json_encode([

            "genericPromoCode"     => !empty($row['data']['advanced_booking_promo_code']) ? true : false,
            "allowOnlineBooking"   => !empty($row['data']['advanced_booking_promo_code']) ? true : false,
            "region"               => [],
            "offerType"            => '2FOR1',
            "attraction_name"      => $row['data']['attraction_name'] ?? null,
            "name"                 => !empty($row['data']['event_name']) ? $row['data']['event_name'] : $row['data']['attraction_name'],
            "umbracoId"            => null,
            "category"             => $row['data']['category'] ?? [],
            "startDate"            => timestamp::db_format($row['data']['event_start_date'] ?? null),
            "endDate"              => timestamp::db_format($row['data']['event_end_date'] ?? null),
            "offerTimes"           => $row['data']['offer_dates_and_times'] ?? null,
            "exclusions"           => $row['data']['closure_dates'] ?? null,
            "address"              => $row['data']['attraction_address'] ?? null,
            "postcode"             => $row['data']['attraction_postcode'] ?? null,
            "closestStation"       => $row['data']['closest_station'] ?? null,
            "visitorContactNumber" => $row['data']['visitor_contact'] ?? null,
            "price"                => $row['data']['2for1_adult_admission'] ?? null,
            "directions"           => $row['data']['directions_for_visitors'] ?? null,
            "copyForLeaflet"       => $row['data']['descriptive_website_copy'] ?? null,
            "copyForWeb"           => $row['data']['descriptive_website_copy'] ?? null,
            "url"                  => $row['data']['web_address'] ?? null,
            "allowBritRail"        => $row['data']['available_to_BR_ticket_holders'] ?? null,
            "allowHeathrowExpress" => $row['data']['available_to_HE_ticket_holders'] ?? null,
            "onlineBookingUrl"     => $row['data']['advanced_booking_website'] ?? null,
            "promoCode"            => $row['data']['advanced_booking_promo_code'] ?? null,
            "accessibilityUrl"     => $row['data']['accessibility_access'] ?? null

        ])
    ];

    $image_path = "/var/www/dev.fabcomms.co.ukawesome_2for1/api/assets/attraction_images/submissions/" . $row['data']['db_id'] . '/';


    foreach([
        'image_1',
        'image_2',
        'image_3',
        'image_4',
        'image_5',
        'image_6',
    ] as $image){
        if (file_exists($image_path. ($row['data'][$image] ?? '__')) && is_file($image_path. ($row['data'][$image] ?? '__'))){
            $offers[$row['vendor_id']][$key]['images'][$image] = $image_path . $row['data'][$image];
        }
    } 


}

///debug($offers); exit;


echo PHP_EOL;

// * t: write the data
$count = 0;
foreach($attractions as $vendor){
    foreach($vendor as $_attraction){
        $count++;
    }
}

$i=1;
// attractions
if($processAttractons){
    foreach($attractions as $vendor){
        foreach($vendor as $_attraction){

            echo progress::cliProgressBar(25, 100/$count * $i++, 'attractions ');

            //echo "\nsaving attraction #{$_attraction['id']}";

            $images = [];

            foreach (($_attraction['images'] ?? []) as $image_name => $image){

                $images[] = [
                    'data' => base64_encode(file_get_contents($image)),
                    'name' => (($image_name == 'image_1' ? 'image_main': $image_name) ?? null),
                    'meta' => '{}',
                ];


            }

            $attraction = new attraction($_attraction);
            $attraction->save(withTimeStamp: true);
            $attraction->saveImages($images);

        }
    }
}

echo PHP_EOL;

$count = 0;
foreach($offers as $vendor){
    foreach($vendor as $_offer){
        $count++;
    }
}

$i=1;

//offers
if($processOffers){
    foreach($offers as $vendor){
        foreach($vendor as $_offer){

            echo progress::cliProgressBar(25, 100/$count * $i++, 'offers ');

            $_offer['attraction_id'] = current($db_target->preparedQuery('SELECT `id` FROM `attractions` WHERE `name` = :name AND `vendor_id` = :vendor_id',['vendor_id' => $_offer['vendor_id'], 'name' => $_offer['attraction_name']])->fetch_array() ?? [])['id'] ?? null;


            //echo "\nsaving offer for attraction #{$_offer['attraction_id']}";

            $images = [];

            foreach (($_offer['images'] ?? []) as $image_name => $image){

                $images[] = [
                    'data' => base64_encode(file_get_contents($image)),
                    'name' => (($image_name == 'image_1' ? 'image_main': $image_name) ?? null),
                    'meta' => '{}',
                ];


            }

            $offer = new offer($_offer);
            $offer->id = $offer->save(withTimeStamp: true);
            $offer->saveImages($images);

        }
    }
}



die("\n\n\nOK\n\n\n");