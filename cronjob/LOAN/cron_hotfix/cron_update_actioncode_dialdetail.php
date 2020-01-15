<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$dial_details = $mongo_db->where(array('createdAt'=> array('$gt' => $midnight), 'action_code' => array('$exists' => false)))->get('LO_Diallist_detail');
	foreach ($dial_details as $key => $dial) {
		$lastDiallistDetail = $mongo_db->where("account_number", $dial["account_number"])->where("updatedAt",['$exists'=>TRUE])->order_by(array("_id" => -1))->getOne('LO_Diallist_detail');
		if(isset($lastDiallistDetail['action_code'])){
			echo $key;
			godown();
			$mongo_db->where_id($dial['id'])->update('LO_Diallist_detail', array('$set' => array('action_code' => $lastDiallistDetail['action_code'])));
		}
	}

}

function godown(){
	echo PHP_EOL;
}
