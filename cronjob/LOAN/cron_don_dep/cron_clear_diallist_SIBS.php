<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$sevendays = strtotime("-1 week");

	$diallist = $mongo_db->
	where(array('createdAt' => array('$lt' => $sevendays)))->
	where(array('team' => 'SIBS'))->
	get('LO_Diallist');

	foreach ($diallist as $key => $i_diallist) {
		$dial_detail = $mongo_db->where_object_id("diallist_id", $i_diallist["id"])->getOne('LO_Diallist_detail');
		if(empty($dial_detail)){
			$mongo_db->insert('LO_Diallist_removed', $i_diallist);
			$mongo_db->where_id($i_diallist["id"])->delete('LO_Diallist');
			continue;
		}
		$mongo_db->batch_insert('LO_Diallist_detail_removed', $dial_detail);
		$mongo_db->where_object_id("diallist_id", $i_diallist["id"])->delete_all('LO_Diallist_detail');
		$mongo_db->insert('LO_Diallist_removed', $i_diallist);
		$mongo_db->where_id($i_diallist["id"])->delete('LO_Diallist');
	}

}

function godown(){
	echo PHP_EOL;
}