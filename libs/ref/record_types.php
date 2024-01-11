<?php

defined("isInSideApplication")?null:die('no access');




$field['NR Status']['BI']                = "Bid";
$field['NR Status']['OF']                = "Offered";
$field['NR Status']['CA']                = "Cancelled";
$field['NR Status']['Q']                 = "Q Path";
$field['NR Status']['OR']                = "Offer Response";

$field['Record Identity']['HD']          = "Header";
$field['Record Identity']['AA']          = "Association";
$field['Record Identity']['BS']          = "Basic Schedule";
$field['Record Identity']['BX']          = "Basic Schedule Extra Details";
$field['Record Identity']['LO']          = "Origin Location";
$field['Record Identity']['LI']          = "Intermediate Location";
$field['Record Identity']['CR']          = "Changes en Route";
$field['Record Identity']['LT']          = "Terminating Location";
$field['Record Identity']['TN']          = "Train Specific Note";
$field['Record Identity']['LN']          = "Location Specific Note";
$field['Record Identity']['TI']          = "TIPLOC Insert";
$field['Record Identity']['TA']          = "TIPLOC Amend";
$field['Record Identity']['TD']          = "TIPLOC Delete";
$field['Record Identity']['ZZ']          = "End of File";

$field['Bleed-off/Update Ind']['U']      = "Update extract";
$field['Bleed-off/Update Ind']['F']      = "Full extract";

$field['Transaction Type']['N']          = "New";
$field['Transaction Type']['D']          = "Delete";
$field['Transaction Type']['R']          = "Revise";

$field['Association-category']['JJ']     = "Join";
$field['Association-category']['VV']     = "Divide";
$field['Association-category']['NP']     = "Next";

$field['Association-date-ind']['S']      = "Standard (same day)";
$field['Association-date-ind']['N']      = "Over-next-midnight";
$field['Association-date-ind']['P']      = "Over-previous-midnight";

$field['Association Type']['P']          = "Passenger use";
$field['Association Type']['O']          = "Operating use only";

$field['STP Indicator']['C']             = "STP Cancellation of perm. entry";
$field['STP Indicator']['N']             = "New STP entry (not overlay)";
$field['STP Indicator']['P']             = "Permanent entry";
$field['STP Indicator']['O']             = "STP overlay of perm. entry";

$field['Bank Holiday Running']['X']      = "Does not run on specified Bank Holiday Mondays";
$field['Bank Holiday Running']['E']      = "Does not run on specified Edinburgh Holiday dates";
$field['Bank Holiday Running']['G']      = "Does not run on specified Glasgow Holiday dates";

$field['Train Status']['B']              = "Bus (LTP)";
$field['Train Status']['F']              = "Freight (LTP)";
$field['Train Status']['P']              = "Train (LTP)";
$field['Train Status']['S']              = "Ship (LTP)";
$field['Train Status']['T']              = "Trip (LTP)";
$field['Train Status']['1']              = "Train (STP)";
$field['Train Status']['2']              = "Freight (STP)";
$field['Train Status']['3']              = "Trip (STP)";
$field['Train Status']['4']              = "Ship (STP)";
$field['Train Status']['5']              = "Bus (STP)";

$field['Running Status']['RNG']          = 'Running';
$field['Running Status']['CND']          = 'Cancelled';
$field['Running Status']['OVL']          = 'Overlay';
$field['Running Status']['OVD']          = 'Overlaid';
$field['Running Status']['NEW']          = 'New (running)';

//Ordinary Passenger Trains
$field['Train Category']['OL']           = "London Underground/Metro Service";
$field['Train Category']['OO']           = "Ordinary Passenger";
$field['Train Category']['OU']           = "Unadvertised Ordinary Passenger";
$field['Train Category']['OS']           = "Staff Train";
$field['Train Category']['OW']           = "Mixed";

//Railfreight Distribution
$field['Train Category']['J2']           = "RfD Automotive (Components)";
$field['Train Category']['H2']           = "RfD Automotive (Vehicles)";
$field['Train Category']['J3']           = "RfD Edible Products";
$field['Train Category']['J4']           = "RfD Industrial Minerals";


//Express Passenger Trains
$field['Train Category']['XC']           = "Channel Tunnel";
$field['Train Category']['XD']           = "Sleeper (Europe Night Service)";
$field['Train Category']['XI']           = "International";
$field['Train Category']['XR']           = "Motorail";
$field['Train Category']['XU']           = "Unadvertised Express";
$field['Train Category']['XX']           = "Express Passenger";
$field['Train Category']['XZ']           = "Sleeper (Domestic)";

//Buses
$field['Train Category']['BR']           = "Bus – Replacement due to engineering work";
$field['Train Category']['BS']           = "Bus – WTT Service";



//Empty Coaching Stock Trains
$field['Train Category']['EE']           = "Empty Coaching Stock";
$field['Train Category']['EL']           = "ECS, London Underground/Metro Service";
$field['Train Category']['ES']           = "ECS & Staff";


$field['Train Category']['B1']           = "Metals";
$field['Train Category']['B5']           = "Domestic and Industrial Waste";

//Parcels and Postal Trains
$field['Train Category']['JJ']           = "Postal";
$field['Train Category']['PM']           = "Post Office Controlled Parcel";
$field['Train Category']['PP']           = "Parcels";
$field['Train Category']['PV']           = "Empty NPCCS";

//Departmental Trains
$field['Train Category']['DD']           = "Departmental";
$field['Train Category']['DH']           = "Civil Engineer";
$field['Train Category']['DI']           = "Mechanical & Electrical Engineer";
$field['Train Category']['DQ']           = "Stores";
$field['Train Category']['DT']           = "Test";
$field['Train Category']['DY']           = "Signal & Telecommunications";

//Light Locomotives
$field['Train Category']['ZB']           = "Locomotive & Brake Van";
$field['Train Category']['ZZ']           = "Light Locomotive";

//Railfreight Distribution
$field['Train Category']['J2']           = "RfD Automotive (Components)";
$field['Train Category']['H2']           = "RfD Automotive (Vehicles)";
$field['Train Category']['J3']           = "RfD Edible Products (UK Contracts)";
$field['Train Category']['J4']           = "RfD Industrial Minerals (UK Contracts)";
$field['Train Category']['J5']           = "RfD Chemicals (UK Contracts)";
$field['Train Category']['J6']           = "RfD Building Materials (UK Contracts)";
$field['Train Category']['J8']           = "RfD General Merchandise (UK Contracts)";
$field['Train Category']['H8']           = "RfD European";
$field['Train Category']['J9']           = "RfD Freightliner (Contracts)";
$field['Train Category']['H9']           = "RfD Freightliner (Other)";

//Trainload Freight
$field['Train Category']['A0']           = "Coal (Distributive)";
$field['Train Category']['E0']           = "Coal (Electricity) MGR";
$field['Train Category']['B0']           = "Coal (Other) and Nuclear";
$field['Train Category']['B1']           = "Metals";
$field['Train Category']['B4']           = "Aggregates";
$field['Train Category']['B5']           = "Domestic and Industrial Waste";
$field['Train Category']['B6']           = "Building Materials (TLF)";
$field['Train Category']['B7']           = "Petroleum Products";

//Railfreight Distribution (Channel Tunnel)
$field['Train Category']['H0']           = "RfD European Channel Tunnel (Mixed Business)";
$field['Train Category']['H1']           = "RfD European Channel Tunnel Intermodal";
$field['Train Category']['H3']           = "RfD European Channel Tunnel Automotive";
$field['Train Category']['H4']           = "RfD European Channel Tunnel Contract Services";
$field['Train Category']['H5']           = "RfD European Channel Tunnel Haulmark";
$field['Train Category']['H6']           = "RfD European Channel Tunnel Joint Venture";

//other
$field['Train Category']['SS']           = "Ship";

$field['Power Type']['D']                = "Diesel";
$field['Power Type']['DEM']              = "Diesel Electric Multiple Unit";
$field['Power Type']['DMU']              = "Diesel Mechanical Multiple Unit";
$field['Power Type']['E']                = "Electric";
$field['Power Type']['ED']               = "Electro-Diesel";
$field['Power Type']['EML']              = "EMU plus D, E, ED locomotive";
$field['Power Type']['EMU']              = "Electric Multiple Unit";
$field['Power Type']['EPU']              = "Electric Parcels Unit";
$field['Power Type']['HST']              = "High Speed Train";
$field['Power Type']['LDS']              = "Diesel Shunting Locomotive.";

$field['Operating Characteristics']['B'] = "Vacuum Braked.";
$field['Operating Characteristics']['C'] = "Timed at 100 m.p.h.";
$field['Operating Characteristics']['D'] = "DOO (Coaching stock trains).";
$field['Operating Characteristics']['E'] = "Conveys Mark 4 Coaches.";
$field['Operating Characteristics']['G'] = "Trainman (Guard) required.";
$field['Operating Characteristics']['M'] = "Timed at 110 m.p.h.";
$field['Operating Characteristics']['P'] = "Push/Pull train.";
$field['Operating Characteristics']['Q'] = "Runs as required.";
$field['Operating Characteristics']['R'] = "Air conditioned with PA system.";
$field['Operating Characteristics']['S'] = "Steam Heated.";
$field['Operating Characteristics']['Y'] = "Runs to Terminals/Yards as required.";
$field['Operating Characteristics']['Z'] = "May convey traffic to SB1C gauge. Not to be diverted from booked route without authority.";

$field['Train Class']['B']               = "First & Standard seats";
$field['Train Class']['S']               = "Standard class only";

$field['Seating Class']['B']             = "First & Standard seats";
$field['Seating Class']['S']             = "Standard class only";

$field['Class'][1]                       = 'Express passenger train; nominated postal or parcels train; breakdown or overhead line equipment train going to clear the line (headcode 1Z99); traction unit going to assist a failed train (1Z99); snow plough going to clear the line (1Z99)';
$field['Class'][2]                       = 'Ordinary passenger train; Officers’ special train (2Z01)';
$field['Class'][3]                       = 'Freight train if specially authorised; a parcels train; autumn-railhead treatment train; empty coaching stock train if specially authorised';
$field['Class'][4]                       = 'Freight train which can run up to 75 mph';
$field['Class'][5]                       = 'Empty coaching stock train';
$field['Class'][6]                       = 'Freight train which can run up to 60 mph';
$field['Class'][7]                       = 'Freight train which can run up to 45 mph';
$field['Class'][8]                       = 'Freight train which can run up to 35 mph';
$field['Class'][9]                       = 'Class 373 train; other passenger train if specially authorised';
$field['Class'][0]                       = 'Light locomotive or locomotives';

$field['passengerTrainClasses']          = array(1,2,9);
$field['emptyCoachingClasses']           = array(5);
$field['freightClasses']                 = array(3,4,6,7,8);
$field['busClasses']                     = array('0B00');

$field['Connection Indicator']['C']      = "Connections not allowed into this train.";
$field['Connection Indicator']['S']      = "Connections not allowed out of this train.";
$field['Connection Indicator']['X']      = "Connections not allowed at all.";

$field['Service Branding']['E']          = "Eurostar.";
$field['Service Branding']['U']          = "Alphaline";

$field['Sleepers']['B']                  = "First & Standard Class";
$field['Sleepers']['F']                  = "First Class only";
$field['Sleepers']['S']                  = "Standard Class only";

$field['Catering Code']['C']             = "Buffet Service";
$field['Catering Code']['F']             = "Restaurant Car available for First Class passengers";
$field['Catering Code']['H']             = "Service of hot food available";
$field['Catering Code']['M']             = "Meal included for First Class passengers";
$field['Catering Code']['P']             = "Wheelchair only reservations";
$field['Catering Code']['R']             = "Restaurant";
$field['Catering Code']['T']             = "Trolley Service";

$field['Portion Id']['Z']                = "Train may be used to convey Red Star parcels";

$field['Operating Characteristics']['B'] = "Vacuum Braked.";
$field['Operating Characteristics']['C'] = "Timed at 100 m.p.h.";
$field['Operating Characteristics']['D'] = "Driver only operated.";
$field['Operating Characteristics']['E'] = "Conveys Mark 4 Coaches.";
$field['Operating Characteristics']['G'] = "Trainman (Guard) required.";
$field['Operating Characteristics']['M'] = "Timed at 110 m.p.h.";
$field['Operating Characteristics']['P'] = "Push/Pull train.";
$field['Operating Characteristics']['Q'] = "Runs as required.";
$field['Operating Characteristics']['R'] = "Air conditioned with PA system.";
$field['Operating Characteristics']['S'] = "Steam Heated.";
$field['Operating Characteristics']['Y'] = "Runs to Terminals/Yards as required.";
$field['Operating Characteristics']['Z'] = "May convey traffic to SB1C gauge. Not to be diverted from booked route without authority.";

$field['Reservations']['A']              = "Seat Reservations Compulsory";
$field['Reservations']['E']              = "Reservations for Bicycles Essential";
$field['Reservations']['R']              = "Seat Reservations Recommended";
$field['Reservations']['S']              = "Seat Reservations possible from any station";

$field['ATOC Code']['AW']                = "Arriva Trains Wales";
$field['ATOC Code']['CC']                = "c2c";
$field['ATOC Code']['CH']                = "Chiltern Railways";
$field['ATOC Code']['CS']                = "Caledonian Sleeper";
$field['ATOC Code']['EM']                = "East Midlands Trains";
$field['ATOC Code']['ES']                = "Eurostar UK";
$field['ATOC Code']['GC']                = "Grand Central";
$field['ATOC Code']['GN']                = "Great Northern";
$field['ATOC Code']['GR']                = "Virgin Trains East Coast";
$field['ATOC Code']['GW']                = "First Great Western";
$field['ATOC Code']['GX']                = "Gatwick Express";
$field['ATOC Code']['HC']                = "Heathrow Connect";
$field['ATOC Code']['HT']                = "First Hull Trains";
$field['ATOC Code']['HX']                = "Heathrow Express";
$field['ATOC Code']['IL']                = "Island Line";
$field['ATOC Code']['LE']                = "Abellio Greater Anglia";
$field['ATOC Code']['LM']                = "London Midland";
$field['ATOC Code']['LO']                = "London Overground";
$field['ATOC Code']['LT']                = "London Underground";
$field['ATOC Code']['ME']                = "Merseyrail";
$field['ATOC Code']['NT']                = "Northern";
$field['ATOC Code']['NY']                = "North Yorkshire Moors Railway";
$field['ATOC Code']['SE']                = "Southeastern";
$field['ATOC Code']['SN']                = "Southern";
$field['ATOC Code']['SR']                = "ScotRail";
$field['ATOC Code']['SW']                = "South West Trains";
$field['ATOC Code']['TL']                = "Thameslink";
$field['ATOC Code']['TP']                = "First TransPennine Express";
$field['ATOC Code']['VT']                = "Virgin Trains West Coast";
$field['ATOC Code']['WR']                = "West Coast Railway Co.";
$field['ATOC Code']['XC']                = "CrossCountry";
$field['ATOC Code']['XR']                = "Crossrail";
$field['ATOC Code']['TW']                = 'Nexus (Tyne & Wear Metro)';
$field['ATOC Code']['ZZ']                = "Freight and Engineering";

$field['Applicable Timetable Code']['Y'] = "Train is subject to performance monitoring (Applicable Timetable Service)";
$field['Applicable Timetable Code']['N'] = "Train is not subject to performance monitoring (Not Applicable Timetable Service)";

$field['Activity']['A']                  = "Stops or shunts for other trains to pass";
$field['Activity']['AE']                 = "Attach/detach assisting locomotive";
$field['Activity']['AX']                 = "Shows as X on arrival";
$field['Activity']['BL']                 = "Stops for banking locomotive";
$field['Activity']['C']                  = "Stops to change trainmen";
$field['Activity']['D']                  = "Stops to set down passengers"; //public
$field['Activity']['-D']                 = "Stops to detach vehicles";
$field['Activity']['E']                  = "Stops for examination";
$field['Activity']['G']                  = "National Rail Timetable data to add";
$field['Activity']['H']                  = "National activity to prevent WTT timing columns merge";
$field['Activity']['HH']                 = "National activity to prevent WTT timing columns merge, where a third column is involved";
$field['Activity']['K']                  = "Passenger count point";
$field['Activity']['KC']                 = "Ticket collection and examination point";
$field['Activity']['KE']                 = "Ticket examination point";
$field['Activity']['KF']                 = "Ticket Examination Point, 1st Class only";
$field['Activity']['KS']                 = "Selective Ticket Examination Point";
$field['Activity']['L']                  = "Stops to change locomotives";
$field['Activity']['N']                  = "Stop not advertised";
$field['Activity']['OP']                 = "Stops for other operating reasons";
$field['Activity']['OR']                 = "Train Locomotive on rear";
$field['Activity']['PR']                 = "Propelling between points shown";
$field['Activity']['R']                  = "Stops when required"; //public
$field['Activity']['RM']                 = "Reversing movement, or driver changes ends";
$field['Activity']['RR']                 = "Stops for locomotive to run round train";
$field['Activity']['S']                  = "Stops for railway personnel only";
$field['Activity']['T']                  = "Stops to take up and set down passengers"; //public
$field['Activity']['-T']                 = "Stops to attach and detach vehicles";
$field['Activity']['TB']                 = "Train begins"; //public
$field['Activity']['TF']                 = "Train finishes"; //public
$field['Activity']['TS']                 = "Detail Consist for TOPS Direct requested by EWS";
$field['Activity']['TW']                 = "Stops (or at pass) for tablet, staff or token.";
$field['Activity']['U']                  = "Stops to take up passengers"; //public
$field['Activity']['-U']                 = "Stops to attach vehicles";
$field['Activity']['W']                  = "Stops for watering of coaches";
$field['Activity']['X']                  = "Passes another train at crossing point on single line";

$field['passenger_stop_codes'][]         = "D";
$field['passenger_stop_codes'][]         = "U";
$field['passenger_stop_codes'][]         = "T";
$field['passenger_stop_codes'][]         = "TB";
$field['passenger_stop_codes'][]         = "TF";
$field['passenger_stop_codes'][]         = "R";
$field['passenger_stop_codes'][]         = "K";

$field['operational_stop_code']['RM']                  = "Reversing movement, or driver changes ends";
$field['operational_stop_code']['A']                  = "Stops or shunts for other trains to pass";
$field['operational_stop_code']['AE']                 = "Attach/detach assisting locomotive";
$field['operational_stop_code']['BL']                 = "Stops for banking locomotive";
$field['operational_stop_code']['C']                  = "Stops to change trainmen";
$field['operational_stop_code']['-D']                 = "Stops to detach vehicles";
$field['operational_stop_code']['E']                  = "Stops for examination";
$field['operational_stop_code']['L']                  = "Stops to change locomotives";
$field['operational_stop_code']['OP']                 = "Stops for other operating reasons";
$field['operational_stop_code']['OR']                 = "Train Locomotive on rear";
$field['operational_stop_code']['PR']                 = "Propelling between points shown";
$field['operational_stop_code']['R']                  = "Stops when required"; //public
$field['operational_stop_code']['RR']                 = "Stops for locomotive to run round train";
$field['operational_stop_code']['S']                  = "Stops for railway personnel only";
$field['operational_stop_code']['-T']                 = "Stops to attach and detach vehicles";
$field['operational_stop_code']['TS']                 = "Detail Consist for TOPS Direct requested by EWS";
$field['operational_stop_code']['TW']                 = "Stops (or at pass) for tablet, staff or token.";
$field['operational_stop_code']['-U']                 = "Stops to attach vehicles";
$field['operational_stop_code']['W']                  = "Stops for watering of coaches";

$field['Days Run']['000000']['code']     = "never";
$field['Days Run']['0000000']['code']    = "never";
$field['Days Run']['1000000']['code']    = "mo";
$field['Days Run']['0100000']['code']    = "to";
$field['Days Run']['1100000']['code']    = "mto";
$field['Days Run']['0010000']['code']    = "wo";
$field['Days Run']['1010000']['code']    = "mwo";
$field['Days Run']['0110000']['code']    = "two";
$field['Days Run']['1110000']['code']    = "ysthfx";
$field['Days Run']['0001000']['code']    = "tho";
$field['Days Run']['1001000']['code']    = "mtho";
$field['Days Run']['0101000']['code']    = "ttho";
$field['Days Run']['1101000']['code']    = "wfx";
$field['Days Run']['0011000']['code']    = "wtho";
$field['Days Run']['1011000']['code']    = "tfx";
$field['Days Run']['0111000']['code']    = "mfx";
$field['Days Run']['1111000']['code']    = "fx";
$field['Days Run']['0000100']['code']    = "fo";
$field['Days Run']['1000100']['code']    = "mfo";
$field['Days Run']['0100100']['code']    = "tfo";
$field['Days Run']['1100100']['code']    = "wthx";
$field['Days Run']['0010100']['code']    = "wfo";
$field['Days Run']['1010100']['code']    = "tthx";
$field['Days Run']['0110100']['code']    = "mthx";
$field['Days Run']['1110100']['code']    = "thx";
$field['Days Run']['0001100']['code']    = "thfo";
$field['Days Run']['1001100']['code']    = "twx";
$field['Days Run']['0101100']['code']    = "mwx";
$field['Days Run']['1101100']['code']    = "wx";
$field['Days Run']['0011100']['code']    = "mtx";
$field['Days Run']['1011100']['code']    = "tx";
$field['Days Run']['0111100']['code']    = "mx";
$field['Days Run']['1000010']['code']    = "mso";
$field['Days Run']['0100010']['code']    = "tso";
$field['Days Run']['1100010']['code']    = "mtso";
$field['Days Run']['0010010']['code']    = "wso";
$field['Days Run']['1010010']['code']    = "mwso";
$field['Days Run']['0110010']['code']    = "twso";
$field['Days Run']['0001010']['code']    = "thso";
$field['Days Run']['1001010']['code']    = "mthso";
$field['Days Run']['0101010']['code']    = "tthso";
$field['Days Run']['0011010']['code']    = "wthso";
$field['Days Run']['1110110']['code']    = "mtwfso";
$field['Days Run']['1111010']['code']    = "mtwthso";
$field['Days Run']['0111110']['code']    = "twthfso";
$field['Days Run']['0000110']['code']    = "fso";
$field['Days Run']['1000110']['code']    = "mfso";
$field['Days Run']['0100110']['code']    = "tfso";
$field['Days Run']['0010110']['code']    = "wfso";
$field['Days Run']['0001110']['code']    = "thfso";
$field['Days Run']['0000010']['code']    = "so";
$field['Days Run']['0000001']['code']    = "suo";
$field['Days Run']['1111100']['code']    = "sx"; //"ewd";
$field['Days Run']['1111110']['code']    = "ewd"; //ewd
$field['Days Run']['1111111']['code']    = "ed"; //ewd

$field['Days Run']['1000000']['full']    = "Mondays only"; //";//mo
$field['Days Run']['0100000']['full']    = "Tuesdays only"; //";//to
$field['Days Run']['1100000']['full']    = "Mondays and Tuesdays only"; //";//mto
$field['Days Run']['0010000']['full']    = "Wednesdays only"; //wo
$field['Days Run']['1010000']['full']    = "Mondays and Wednesdays only"; //mwo
$field['Days Run']['0110000']['full']    = "Tuesdays and Wednesdays only"; //two
$field['Days Run']['1110000']['full']    = "Not Thursdays and Fridays"; //ysthfx
$field['Days Run']['0001000']['full']    = "Thursdays only"; //tho
$field['Days Run']['1001000']['full']    = "Mondays and Thursdays only"; //mtho
$field['Days Run']['0101000']['full']    = "Tuesdays and Thursdays only"; //ttho
$field['Days Run']['1101000']['full']    = "Not Wednesdays and Fridays"; //wfx
$field['Days Run']['0011000']['full']    = "Wednesdays and Thursdays only"; //wtho
$field['Days Run']['1011000']['full']    = "Not Tuesdays and Fridays"; //tfx
$field['Days Run']['0111000']['full']    = "Not Mondays and Fridays"; //mfx
$field['Days Run']['1111000']['full']    = "Not Fridays"; //fx
$field['Days Run']['0000100']['full']    = "Fridays only"; //fo
$field['Days Run']['1000100']['full']    = "Mondays and Fridays only"; //mfo
$field['Days Run']['0100100']['full']    = "Tuesdays and Fridays only"; //tfo
$field['Days Run']['1100100']['full']    = "Not Wednesdays and Thursdays"; //wthx
$field['Days Run']['0010100']['full']    = "Wednesdays and Fridays only"; //wfo
$field['Days Run']['1010100']['full']    = "Not Tuesdays and Thursdays"; //tthx
$field['Days Run']['0110100']['full']    = "Not Mondays and Thursdays"; //mthx
$field['Days Run']['1110100']['full']    = "Not Thursdays"; //thx
$field['Days Run']['0001100']['full']    = "Thursdays and Fridays only"; //thfo
$field['Days Run']['1001100']['full']    = "Not Tuesdays and Wednesdays"; //twx
$field['Days Run']['0101100']['full']    = "Not Mondays and Wednesdays"; //mwx
$field['Days Run']['1101100']['full']    = "Not Wednesdays"; //wx
$field['Days Run']['0011100']['full']    = "Not Mondays and Tuesdays"; //mtx
$field['Days Run']['1011100']['full']    = "Not Tuesdays"; //tx
$field['Days Run']['0111100']['full']    = "Not Mondays"; //mx
$field['Days Run']['1000010']['full']    = "Mondays and Saturdays only"; //mso
$field['Days Run']['0100010']['full']    = "Tuesdays and Saturdays only"; //tso
$field['Days Run']['1100010']['full']    = "Mondays, Tuesdays and Saturdays only"; //mtso
$field['Days Run']['0010010']['full']    = "Wednesdays and Saturdays only"; //wso
$field['Days Run']['1010010']['full']    = "Mondays, Wednesdays and Saturdays only"; //mwso
$field['Days Run']['0110010']['full']    = "Tuesdays, Wednesdays and Saturdays only"; //twso
$field['Days Run']['1110110']['full']    = "Mondays, Tuesdays, Wednesdays, Fridays and Saturdays only"; //twso
$field['Days Run']['0001010']['full']    = "Thursdays and Saturdays only"; //thso
$field['Days Run']['1001010']['full']    = "Mondays, Thursdays and Saturdays only"; //mthso
$field['Days Run']['0101010']['full']    = "Tuesdays, Thursdays and Saturdays only"; //tthso
$field['Days Run']['0011010']['full']    = "Wednesdays, Thursdays and Saturdays only"; //wthso
$field['Days Run']['0000110']['full']    = "Fridays and Saturdays only"; //fso
$field['Days Run']['1000110']['full']    = "Mondays, Fridays and Saturdays only"; //mfso
$field['Days Run']['0100110']['full']    = "Tuesdays, Fridays and Saturdays only"; //tfso
$field['Days Run']['0010110']['full']    = "Wednesdays, Fridays and Saturdays only"; //wfso
$field['Days Run']['0001110']['full']    = "Thursdays, Fridays and Saturdays only"; //thfso
$field['Days Run']['1111010']['full']    = "Mondays, Tuesdays, Wednesdays, Thursdays and Saturdays only"; //mtwthso
$field['Days Run']['0111110']['full']    = "Tuesdays, Wednesdays, Thursdays, Friday and Saturdays only"; //twthfso;
$field['Days Run']['0000010']['full']    = "Saturdays only"; //so
$field['Days Run']['0000001']['full']    = "Sundays only"; //suo
$field['Days Run']['1111100']['full']    = "Mondays to Fridays"; //ewd
$field['Days Run']['1111111']['full'] = "All Days"; //ewd
