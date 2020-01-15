<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$collection = "LO_Payment_history";
$key_field = "account_number";
$arr_code = ['2000', '2100', '2700'];
// CARD

$listofaccount = $mongo_db->get("LO_List_of_account_in_collection");

foreach ($listofaccount as $key => $i_listofaccount) {
	$data = [];
	$payment_card = $mongo_db->
	where("account_number", $i_listofaccount["account_number"])->
	where(array('map_listofaccount' => array('$exists' => false)))->
	get('LO_Report_input_payment_of_card');
	if(empty($payment_card)) continue;
	foreach ($payment_card as $k => $i_payment_card) {
		if(!in_array($i_payment_card['code'], $arr_code)) continue;
		$data["type"] 			= "CARD";
		$data["account_number"] = $i_listofaccount["account_number"];

		$data["payment_date"] 	= _isset($i_payment_card, 'posting_date');
		$data["payment_amount"] = _isset($i_payment_card, "amount");

		$data['due_date'] 		= _isset($i_listofaccount, "overdue_date");
		$data['overdue_amount'] = _isset($i_listofaccount, "overdue_amt");

		$sbv = $mongo_db->where(array('contract_no' => $i_listofaccount["account_number"]))->getOne("LO_SBV_Stored");
		$data['debt_group'] 	= _isset($sbv, "overdue_indicator");

		$mongo_db->where_id($i_payment_card['id'])->update('LO_Report_input_payment_of_card', array('$set' => array('map_listofaccount' => true)));
		$queueData = array(
			"collection"	=> $collection,
			"doc"			=> $data,
			"startTimestamp"=> time()
		);
		$queue->useTube('import')->put(json_encode($queueData));

	}//end foreach
}

//code: 2000 2100 2700 thi moi ghi
