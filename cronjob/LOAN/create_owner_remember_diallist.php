<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "/log_cron/due_date_plus1_log.txt");

require_once dirname(__DIR__) . "/Header.php";

$today = date('d', time());
$today_midnight = strtotime('today midnight');

$mongo_db               = new Mongo_db();

$midnight = strtotime('today midnight');

$time1 = $midnight -1000;
$time2 = $midnight + 86000;

$check_duedate_plus1 = $mongo_db->where("due_date_add_1", ['$gte' => $time1, '$lte' => $time2])->getOne('LO_Report_due_date');
if(empty($check_duedate_plus1)) exit;

$diallist   = $mongo_db->where( 
							array(
								'loan_campaign_name' 	=> array('$regex' => 'A01'),
								'createdAt'				=> array('$gte' => $today_midnight),
								'team'					=> 'CARD',
							)
						)->get('LO_Diallist');

foreach ($diallist as $key_dial => $i_diallist) {
	$dial_detail = $mongo_db->where_object_id('diallist_id', $i_diallist['id'])->get('LO_Diallist_detail');
	$mongo_db->where_object_id('diallist_id', $i_diallist['id'])->update_all('LO_Diallist_detail', array('$set' => array('owner_group_remember' => $i_diallist['group_name'],)));
	print_r($i_diallist['id']);
	godown();
	print_r($i_diallist['group_name']);
	godown();
}

$diallist   = $mongo_db->where( 
							array(
								'loan_campaign_name' 	=> array('$regex' => 'A02'),
								'createdAt'				=> array('$gte' => $today_midnight),
								'team'					=> 'CARD',
							)
						)->get('LO_Diallist');

foreach ($diallist as $key_dial => $i_diallist) {
	$dial_detail = $mongo_db->where_object_id('diallist_id', $i_diallist['id'])->get('LO_Diallist_detail');
	$mongo_db->where_object_id('diallist_id', $i_diallist['id'])->update_all('LO_Diallist_detail', array('$set' => array('owner_group_remember' => $i_diallist['group_name'],)));
	print_r($i_diallist['id']);
	godown();
	print_r($i_diallist['group_name']);
	godown();
}

$date_log = date('d-m-Y H:i:s', time());
ghilog(' -- Run success on ' . $date_log);

function godown(){
	echo PHP_EOL;
}

function ghilog($err){
	$log_path = __DIR__ . "/log_cron/due_date_plus1_log.txt";
	$err = __FILE__ . (string) $err . PHP_EOL;
	file_put_contents( $log_path, $err, FILE_APPEND);
}