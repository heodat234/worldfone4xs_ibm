<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$to_time = time() - 600;
	$from_time = time() - 3600;


	$cdrs = $mongo_db->where(array('starttime'=> array('$gt' => $from_time, '$lte' => $to_time), 'group_name' => array('$exists' => false)))->get('LO_worldfonepbxmanager');

	foreach ($cdrs as $key => $cdr) {
		
		$mongo_db->switch_db('_worldfone4xs');

		$group_name = $mongo_db->where("extension", $cdr["userextension"])->order_by(array("_id" => -1))->getOne('LO_User')['group_name'];

		$mongo_db->switch_db('worldfone4xs');

		echo $group_name;
		godown();
		$mongo_db->where_id($cdr['id'])->update('LO_worldfonepbxmanager', array('$set' => array('group_name' => $group_name)));
	}

}

function godown(){
	echo PHP_EOL;
}
