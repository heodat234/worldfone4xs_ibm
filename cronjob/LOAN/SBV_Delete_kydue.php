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
$due_date_cong1 = '02';

//store old data for report
$sbv_backup = $mongo_db->where('kydue', $due_date_cong1)->get('LO_SBV_Stored');
$mongo_db->where('kydue', $due_date_cong1)->delete_all('LO_SBV_Stored_Old');
$mongo_db->batch_insert('LO_SBV_Stored_Old', $sbv_backup);
//end

$mongo_db->where('kydue', $due_date_cong1)->delete_all('LO_SBV_Stored');
$date_log = date('d-m-Y H:i:s', time());
ghilog(' -- Run success on ' . $date_log);

function ghilog($err){
	$log_path = __DIR__ . "/log_cron/due_date_plus1_log.txt";
	$err = __FILE__ . (string) $err . PHP_EOL;
	file_put_contents( $log_path, $err, FILE_APPEND);
}