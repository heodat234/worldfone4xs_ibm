<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$diallist = $mongo_db->where(array('name'=> array('$eq' => 'Card/Group A/Team Thùy Trang/G2 2020-01-07')))->getOne('LO_Diallist');
	$dial_details = $mongo_db->where_object_id('diallist_id', $diallist['id'])->get('LO_Diallist_detail');
	// echo count($dial_details);exit;
	foreach ($dial_details as $key => $dial) {
		echo $dial['id'];
		godown();
		echo $dial['assign'];
		godown();
		$last_dial_detail = $mongo_db->
		where("account_number", $dial['account_number'])->
		where(array("createdAt" => array('$lte' => strtotime("today midnight"))))->
		order_by(array('_id' => -1))->get('LO_Diallist_detail');

		if(count($last_dial_detail) >0){
			$last_dial_detail = $last_dial_detail[0];
			$mongo_db->where_id($dial['id'])->update('LO_Diallist_detail', array('$set' => array('assign' => $last_dial_detail['assign'])));

		}
		echo $dial['assign'];
		// godown();exit;
	}

}

function godown(){
	echo PHP_EOL;
}
