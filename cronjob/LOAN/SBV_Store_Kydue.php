<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "makeCallingList_logs.txt");

require_once dirname(__DIR__) . "/Header.php";
$today = date('d', time());

$mongo_db               = new Mongo_db();

$due_date_cong1 = '03';

$list_of_account   = $mongo_db->get('LO_List_of_account_in_collection');
$data = [];
foreach ($list_of_account as $key => $value) {
	$due_date = (int)date('d', $value["overdue_date"]);
	if($due_date >= 12 && $due_date <= 21){
		$kydue = '01';
	}else if($due_date >= 22 && $due_date <= 27){
		$kydue = '02';
	}else{
		$kydue = '03';
	}
	if($kydue != $due_date_cong1) continue;
    $sbv = $mongo_db->where("contract_no", $value["account_number"])->order_by(array('_id' => -1))->getOne('LO_SBV');
    $data[] = array(
    	'contract_no' => $sbv['contract_no'], 
    	"overdue_indicator" => $sbv["overdue_indicator"], 
    	"kydue" => $kydue);
}

$mongo_db->batch_insert('LO_SBV_Stored', $data);