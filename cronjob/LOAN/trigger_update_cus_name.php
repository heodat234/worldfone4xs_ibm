<?php

require_once dirname(__DIR__) . "/Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$today_midnight = strtotime('today midnight');

	$dial_detail_count = $mongo_db->
	where(array('createdAt'		=> array('$gt' 		=> $today_midnight	)))->
	// where(array('sync_cus_name' => array('$exists' 	=> true 			)))->
	limit(40000)->
	count('LO_Diallist_detail');

	$limit = floor($dial_detail_count / 5);
	for($i =0; $i<5; $i++){
		$from = $limit * $i;
		if($i == 4) $limit = $limit + 10;
		$url = "nohup php " . dirname(__DIR__) . "/LOAN/processes/update_cus_name_process.php $from $limit > /dev/null 2>&1 &";
		$t = exec($url);
		echo $t . PHP_EOL;
	}
}

function godown(){
	echo PHP_EOL;
}