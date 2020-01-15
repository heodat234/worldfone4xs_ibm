<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$collection = "LO_Payment_history";

// SIBS
$LNCJ05 = $mongo_db->get("LO_LNJC05");

foreach ($LNCJ05 as $key => $i_LNCJ05) {
	$data = [];
	$LN3206F = $mongo_db->
	where("account_number", $i_LNCJ05["account_number"])->
	where(array('map_LNCJ05' => array('$exists' => false)))->
	get('LO_LN3206F');

	if(empty($LN3206F)) continue;

	foreach ($LN3206F as $k => $i_LN3206F) {
		$data["type"] 			= "SIBS";
		$data["account_number"] = $i_LNCJ05["account_number"];
		$data["payment_date"] 	= _isset($i_LN3206F, 'date');
		$data["payment_amount"] = _isset($i_LN3206F, 'amt');

		$data['due_date'] 		= _isset($i_LNCJ05, 'due_date');
		$data['overdue_amount'] = (int)$i_LNCJ05["overdue_amount_this_month"] - (int)$i_LNCJ05['advance_balance'];
		$data['overdue_amount'] = convertToCurrency($data['overdue_amount']);
		$data['debt_group'] 	= _isset($i_LNCJ05, 'group_id');
		$mongo_db->where_id($i_LN3206F['id'])->update('LO_LN3206F', array('$set' => array('map_LNCJ05' => true)));
		$queueData = array(
			"collection"	=> $collection,
			"doc"			=> $data,
			"startTimestamp"=> time()
		);
		$queue->useTube('import')->put(json_encode($queueData));
	}//end foreach
}
