<?php defined("isInSideApplication")?null:die('no access');

$search = (object)[
    'vendor'     => ['live' => 1, 'group_id' => user_group::getIdFromGroupName('vendor')],
    'user'       => ['live' => 1],
    'attraction' => ['live' => 1],
    'offer'      => ['live' => 1],
    'redemption' => []
];


if (!user::hasAccess(array('admin', 'toc', 'rdg'))) {
    global $user;
    $search->user['vendor_id']       = $user->id;
    $search->attraction['vendor_id'] = $user->id;
    $search->offer['vendor_id']      = $user->id;
    $search->redemption['vendor_id'] = $user->id;
}

return [
    'counts' => [
        'vendors'     => [
            'total' => user::hasAccess(array('admin', 'toc', 'rdg')) ? count(user::getAll(array_merge($search->vendor, []))) : null,
        ],
        'attractions' => [
            'total' => count(attraction::fetchAll(array_merge($search->attraction, []))),
        ],
        'offers'      => [
            'total'      => count(offer::fetchAll($search->offer)),
            'offerTypes' => (function () use($search) {
                    $db = new db;
                    foreach($db->query("SELECT `type` FROM `offer_types`")->fetch_array() ?? [] as $row){
                        if($count = count(offer::fetchAll(array_merge($search->offer, ['offerType' => $row['type'] ])))){
                            $_t[$row['type']] = $count;
                        }
                    }
                    return $_t ?? [];
                })(),
            'advanced' => [
                    'total'   => count(offer::fetchAll(array_merge($search->offer, ['allowOnlineBooking' => 'true']))),
                    'codetype' => [
                        'unique'  => count(offer::fetchAll(array_merge($search->offer, ['allowOnlineBooking' => 'true', 'genericPromoCode' => 'false']))),
                        'generic' => count(offer::fetchAll(array_merge($search->offer, ['allowOnlineBooking' => 'true', 'genericPromoCode' => 'true']))),
                    ]
                ],
            'regions' => (function () use($search) {
                    $db = new db;
                    foreach($db->query("SELECT `name` FROM `counties`")->fetch_array() ?? [] as $row){
                        if($count = count(offer::fetchAll(array_merge($search->offer, ['region' => $row['name'] ])))){
                            $_t[$row['name']] = $count;
                        }
                    }
                    return $_t ?? [];
                })(),
            'campaigns' => (function () use($search) {
                    $db = new db;
                    foreach($db->query("SELECT * FROM `campaigns`")->fetch_array() ?? [] as $row){
                        if($count = count(offer::fetchAll(array_merge($search->offer, ['campaign_id' => $row['id'] ])))){
                            $_t[$row['db_id']] = $count;
                        }
                    }
                    return $_t ?? [];
                })(),
            'categories' => (function () use($search) {
                
                    $db = new db;
                    foreach($db->query("SELECT `name` FROM `categories`")->fetch_array() ?? [] as $row){
                        if($count = count(offer::fetchAll(array_merge($search->offer, ['category' => $row['name'] ])))){
                            $_t[$row['name']] = $count;
                        }
                    }
                    return $_t ?? [];
                })(),
            'redemptions' => (function() use($search){

                

                $monthNumberNow = date('n', time());
                $months         = array_merge(range($monthNumberNow +1, 12), range(1, $monthNumberNow ));

                foreach($months as $monthNumber){

                    $thisYear       = date('Y');
                    $lastYear       = date('Y', time() - ONEYEAR);
                    $month          = str_pad($monthNumber, 2, '0', STR_PAD_LEFT);
                    $year           = $monthNumber <= $monthNumberNow ? $thisYear : $lastYear;
                    $lastdayOfMonth = date('t', strtotime("{$year}-{$month}-01}"));

                    $search->redemption['datetime_from'] = "{$year}-{$month}-01 00:00:00";
                    $search->redemption['datetime_to']   = "{$year}-{$month}-{$lastdayOfMonth} 23:59:59";
                    $monthResult[date('M',  strtotime($search->redemption['datetime_from'])) .' '. $year] = (int)redemption::fetchCount(array_merge($search->redemption, [])) ?? 0;
                    
                }
                
                return [
                    'total' => array_sum($monthResult) ?? null,
                    'months' => $monthResult ?? []
                ];

            })()
        ]
    ]
];