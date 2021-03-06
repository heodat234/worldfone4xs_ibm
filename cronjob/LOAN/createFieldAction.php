<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$collection = "LO_Field_action";
$key_field 	= "contract_no";

$LO_Site_result = $mongo_db->get("LO_Site_result");

foreach ($LO_Site_result as $doc) {
	$contract_no 	= $doc['contract_no'];
	$type 			= $mongo_db->where('account_number', $contract_no)->get('LO_LNJC05');

	if(empty($type)){

		$type = $mongo_db->where('contract_no', $contract_no)->get('LO_SBV');

		if(empty($type)){
		// "0028080000007153" => "28080000007153"
			//$remove00 	= substr($contract_no, 2);
			$type 		= $mongo_db->where("contract_no", $contract_no)->getOne("LO_SBV");
		}
		
		$type 			= !empty($type) ? 'CARD' : 'Not Found';
	}
	else {
		$type = "SIBS";
	}

	$doc["type"] 	= $type;

	$queueData 		= array(
		"collection"	=> $collection,
		"doc"			=> $doc,
		"key_field"		=> $key_field,
		"startTimestamp"=> time()
	);

	$queue->useTube('import')->put(json_encode($queueData));
}
