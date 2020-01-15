<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "makeCallingList_logs.txt");

require_once dirname(__DIR__) . "/Header.php";

$today = date('d', time());
$today_midnight = strtotime('today midnight');

$mongo_db               = new Mongo_db();

$diallist   = $mongo_db->where( 
							array(
								'loan_campaign_name' 	=> array('$regex' => 'A01'),
								'createdAt'				=> array('$gte' => $today_midnight),
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

function godown(){
	echo PHP_EOL;
}
