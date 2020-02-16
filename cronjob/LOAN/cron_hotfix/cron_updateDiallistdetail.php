<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$diallist_detail = $mongo_db->
	where_object_id('diallist_id', '5e1bc112314edc31257acf8b')->
	where(array('callResult' => array('$exists' => true)))->
	get('LO_Diallist_detail');

	foreach ($diallist_detail as $key => $dial) {
		echo $dial['account_number'];
		godown();

		$last_dial_detail = $mongo_db->
		where("account_number", $dial['account_number'])->
		where(array("createdAt" => array('$gte' => strtotime("today midnight"))))->
		update_all('LO_Diallist_detail', array('$set' => array('callResult' => $dial['callResult'])));
	}

}

function godown(){
	echo PHP_EOL;
}
