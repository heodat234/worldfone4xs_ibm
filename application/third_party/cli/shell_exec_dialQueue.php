<?php 

// -1st Spin: Touch Main phone number only 
// 	Priority rule:
// 		MAIN PRODUCT
// 			1)	Overdue 5-9 days last month
// 			2)	Product code = 401, 701
// 			3)	Column V – LNJC05 = 1 AND not overdue last month
// 			4)	Others
// 		CARD PRODUCT
// 			1)	Overdue 5-9 days last month
// 			2)	not overdue last month
// 			3)	Others

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", "/var/www/html/worldfone4xs_ibm/application/third_party/cli/schedule_logs.txt");

require_once 'mongodriver.php';
$mongodb= new MyMongoDriver('worldfone4xs');

$diallist_id 	= "5dce64491ef2b42f9b29eaf8";
$type = "SIBS";

if($type == "SIBS"){

	SIBS($diallist_id, $mongodb);

}elseif($type =="CARD"){

	CARD($diallist_id, $mongodb);

}elseif($type =="WO"){

}


function SIBS($diallist_id, $mongodb){

	$idx 			= 0;
	$arrData		= [];

//Priority rule 2:
	$diallistDetailPrio2 = $mongodb->where(array(
		"diallist_id" 	=> new MongoId($diallist_id),
		"PRODGRP_ID" 	=> array('$in' => ['401', '701']),
	))->get('LO_Diallist_detail');
	print_r($diallistDetailPrio2);exit;
	foreach ($diallistDetailPrio2 as $key => $value) {
		$temp 							= [];
		$temp['phone'] 					= $value['phone'];	
		$temp['diallist_id'] 			= $value['diallist_id']->{'$id'};
		$temp['diallistdetail_id'] 		= $value['_id']->{'$id'};
		$temp['spin'] 					= 1;
		$temp['index'] 					= $idx;
		$temp['rule'] 					= "2_MAIN";

		$arrData[] 						= $temp;
		$idx++;
	}
	$mongodb->batch_insert('LO_dialQueue', $arrData);

//Priority rule 3:
	$arrData		= [];

	$diallistDetailPrio3 = $mongodb->where(
		array(
			"diallist_id" 	=> new MongoId($diallist_id),
			"PRODGRP_ID" 	=> array('$nin' => ['401', '701']),
			"installment_type" => array('$in' => [1,'1']),)
	)->get('LO_Diallist_detail');

	foreach ($diallistDetailPrio3 as $key => $value) {
		$temp 							= [];
		$temp['phone'] 					= $value['phone'];	
		$temp['diallist_id'] 			= $value['diallist_id']->{'$id'};
		$temp['diallistdetail_id'] 		= $value['_id']->{'$id'};
		$temp['spin'] 					= 1;
		$temp['index'] 					= $idx;
		$temp['rule'] 					= "3_MAIN";

		$arrData[] 						= $temp;
		$idx++;
	}
	$mongodb->batch_insert('LO_dialQueue', $arrData);

//Priority rule 4:
	$arrData		= [];

	$diallistDetailPrio4 = $mongodb->where(
		array(
			"diallist_id" 	=> new MongoId($diallist_id),
			"PRODGRP_ID" 	=> array('$nin' => ['401', '701']),
			"installment_type" => array('$nin' => [1,'1']),)
	)->get('LO_Diallist_detail');

	foreach ($diallistDetailPrio4 as $key => $value) {
		$temp 							= [];
		$temp['phone'] 					= $value['phone'];	
		$temp['diallist_id'] 			= $value['diallist_id']->{'$id'};
		$temp['diallistdetail_id'] 		= $value['_id']->{'$id'};
		$temp['spin'] 					= 1;
		$temp['index'] 					= $idx;
		$temp['rule'] 					= "4_MAIN";

		$arrData[] 						= $temp;
		$idx++;
	}
	$mongodb->batch_insert('LO_dialQueue', $arrData);

}

function CARD($diallist_id, $mongodb) {

	$idx 			= 0;
	$arrData		= [];
	//Priority rule 3:

	$diallistDetailPrio4 = $mongodb->where(
		array(
			"diallist_id" 	=> new MongoId($diallist_id),
		)
	)->get('LO_Diallist_detail');

	foreach ($diallistDetailPrio4 as $key => $value) {
		$temp 							= [];
		$temp['phone'] 					= $value['phone'];	
		$temp['diallist_id'] 			= $value['diallist_id']->{'$id'};
		$temp['diallistdetail_id'] 		= $value['_id']->{'$id'};
		$temp['spin'] 					= 1;
		$temp['index'] 					= $idx;
		$temp['rule'] 					= "3_CARD";

		$arrData[] 						= $temp;
		$idx++;
	}
	$mongodb->batch_insert('LO_dialQueue', $arrData);
}