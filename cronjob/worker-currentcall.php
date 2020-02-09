<?php

/*
 * Copyright © 2019 South Telecom
 */
require_once __DIR__ . "/Header.php";

$cdr = "worldfonepbxmanager_realtime";

$mongo_db = new Mongo_db();
$mongo_db->switch_db($config['_mongo_db']);

$types = $mongo_db->distinct("ConfigType", "type");

$mongo_db->switch_db();

echo "START" . PHP_EOL;

foreach ($types as $type) {
	$collection = $type . "_" . $cdr;
	$mongo_db->where("workstatus","Complete")->delete_all($collection);
	$outbound = $mongo_db->where(["direction"=>"outbound"])->count($collection);
	$inbound = $mongo_db->where(["direction"=>"inbound", "workstatus"=>"On-Call"])->count($collection);
	$total = $outbound + $inbound;
	$data = array(
		"type" 			=> $type,
		"outbound"		=> $outbound,
		"inbound"		=> $inbound,
		"total"			=> $total,
		"createdAt" 	=> $mongo_db->date()	
	);
	$mongo_db->insert("Report_current_call", $data);
}

echo "END" . PHP_EOL;