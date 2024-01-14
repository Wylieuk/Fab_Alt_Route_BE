<?php defined("isInSideApplication")?null:die('no access');

$fileConfigs = (function(){

    return [

        'stationAltRouteDataSheet' =>  [
            'sheets' => [
                'DATA' => [
                    'targetClass' => 'alt_bus_route',
                    'titleRow' => 1,
                    'readTitleRow' => true,
                    'maxColumnsToRead' => 100,
                    'headerRow' => 2,
                    'startDataRow' => 3,
                    'verifyHeaders' => true,
                    'maxRowsToRead' => 500,
                    'stopOnEmptyRow' => true,
                    'columns' => [
                        'A' => [
                            'name' => 'TO CRS:',
                            'translatedkey' => 'to_crs',
                            'type' => 'string',
                            'preProcess' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return $v;
                            },
                            'postProcess' => function($v, $row=null, $data=null){ //NOSONAR
                                return $v;
                            },
                            'valid' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return true;
                            }
                        ], 
                        'B' => [
                            'name' => 'TO STATION:',
                            'translatedkey' => 'to_station',
                            'type' => 'string',
                            'preProcess' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return $v;
                            },
                            'postProcess' => function($v, $row=null, $data=null){ //NOSONAR
                                return $v;
                            },
                            'valid' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return true;
                            }
                        ], 
                        'C' => [
                            'name' => 'SUGGESTED ALTERNATIVE:',
                            'translatedkey' => 'alt_route_description',
                            'type' => 'string', 
                            'preProcess' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return $v;
                            },
                            'postProcess' => function($v, $row=null, $data=null){ //NOSONAR
                                return $v;
                            },
                            'valid' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return true; 
                            }
                        ]
                    ]
                ],
                'BUS STOPS' => [
                    'targetClass' => 'bus_stop',
                    'titleRow' => 1,
                    'readTitleRow' => true,
                    'maxColumnsToRead' => 100,
                    'headerRow' => 2,
                    'startDataRow' => 3,
                    'verifyHeaders' => true,
                    'maxRowsToRead' => 500,
                    'stopOnEmptyRow' => true,
                    'columns' => [
                        'A' => [
                            'name' => 'STOP NUMBER OR LETTER',
                            'translatedkey' => 'stopId',
                            'type' => 'string',
                            'preProcess' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return $v;
                            },
                            'postProcess' => function($v, $row=null, $data=null){ //NOSONAR
                                return $v;
                            },
                            'valid' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return true;
                            }
                        ],
                        'B' => [
                            'name' => 'NAPTAN',
                            'translatedkey' => 'naptan',
                            'type' => 'string',
                            'preProcess' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return $v;
                            },
                            'postProcess' => function($v, $row=null, $data=null){ //NOSONAR
                                return $v;
                            },
                            'valid' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return true;
                            }
                        ],
                        'C' => [
                            'name' => 'ATCO CODE',
                            'translatedkey' => 'atco_code',
                            'type' => 'string',
                            'preProcess' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return $v;
                            },
                            'postProcess' => function($v, $row=null, $data=null){ //NOSONAR
                                return $v;
                            },
                            'valid' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return true;
                            }
                        ]
                    ]
                            
                ],
                'RRBS' => [
                    'targetClass' => 'rrbs',
                    'titleRow' => 1,
                    'readTitleRow' => true,
                    'maxColumnsToRead' => 100,
                    'headerRow' => 2,
                    'startDataRow' => 3,
                    'verifyHeaders' => true,
                    'maxRowsToRead' => 500,
                    'stopOnEmptyRow' => true,
                    'columns' => [
                        'A' => [
                            'name' => 'RRBS TEXT',
                            'translatedkey' => 'rrbs_text',
                            'type' => 'string',
                            'preProcess' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return $v;
                            },
                            'postProcess' => function($v, $row=null, $data=null){ //NOSONAR
                                return $v;
                            },
                            'valid' => function($v, $sheet=null, $currentRow=null){ //NOSONAR
                                return true;
                            }
                        ]
                    ]
                ]
            ]
        ]
    ];
})();