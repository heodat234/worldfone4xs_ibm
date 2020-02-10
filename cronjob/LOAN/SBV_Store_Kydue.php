<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "/log_cron/due_date_plus1_log.txt");

require_once dirname(__DIR__) . "/Header.php";
$today = date('d', time());

$mongo_db               = new Mongo_db();

$midnight = strtotime('today midnight');

$time1 = $midnight -1000;
$time2 = $midnight + 86000;

$check_duedate_plus1 = $mongo_db->where("due_date_add_1", ['$gte' => $time1, '$lte' => $time2])->getOne('LO_Report_due_date');
if(empty($check_duedate_plus1)) exit;

if(!isset($check_duedate_plus1['debt_group'])) {ghilog(' --Error: debt_group not isset'); exit;}

$due_date_cong1 = $check_duedate_plus1['debt_group'];

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
    echo $kydue . PHP_EOL;
    $data[] = array(
    	'contract_no' => $sbv['contract_no'], 
    	"overdue_indicator" => $sbv["overdue_indicator"], 
    	"kydue" => $kydue);
}

$mongo_db->batch_insert('LO_SBV_Stored', $data);

$date_log = date('d-m-Y H:i:s', time());
ghilog(' -- Run success on ' . $date_log);

function ghilog($err){
	$log_path = __DIR__ . "/log_cron/due_date_plus1_log.txt";
	$err = __FILE__ . (string) $err . PHP_EOL;
	file_put_contents( $log_path, $err, FILE_APPEND);
}