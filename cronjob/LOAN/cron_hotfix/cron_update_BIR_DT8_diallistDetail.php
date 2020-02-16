<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$dial_details = $mongo_db->where(array('createdAt'=> array('$gt' => $midnight), 'from' => array('$regex' => 'SIBS')))->get('LO_Diallist_detail');

	foreach ($dial_details as $key => $dial) {
		if(isset($dial["BIR_DT8"])){
			// print_r($dial['BIR_DT8']);exit;
			if(strlen($dial['BIR_DT8']) == 8){
				$t = $dial['BIR_DT8'];
				$birth_date = $t[0] . $t[1] . '/' . $t[2] . $t[3] . '/' . $t[4] . $t[5] . $t[6] . $t[7];
				echo $birth_date;exit;
			}
		}
	}
}

function godown(){
	echo PHP_EOL;
}
