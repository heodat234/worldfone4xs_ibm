<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;

	$mongo_db->switch_db('LOAN_campaign_list');
	$listCol = $mongo_db->listCollections();
	foreach ($listCol as $key => $col) {
		echo $col;
		$mongo_db->drop_collection($col);
		godown();
	}

}

function godown(){
	echo PHP_EOL;
}