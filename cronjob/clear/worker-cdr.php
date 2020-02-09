<?php

/*
 * Copyright © 2019 South Telecom
 */
require_once dirname(__DIR__) . "/Header.php";

$mongo_db = new Mongo_db();

$mongo_db->switch_db($config['_mongo_db']);

$types = $mongo_db->distinct("ConfigType", "type");

$mongo_db->switch_db();

$time = time();

$rangeDays = 31;
$ori_collection = "worldfonepbxmanager";

echo "START" . PHP_EOL;

foreach ($types as $type) {
	$collection = $type . "_" . $ori_collection;
	$data = $mongo_db->order_by(["_id"=>1])->limit(10000)->get($collection);
	$count = 0;
	foreach ($data as $doc) {
		$_id = new MongoDB\BSON\ObjectId($doc["id"]);
		$timestamp = $_id->getTimestamp();

		if($timestamp < $time-($rangeDays*86400)) {
			// Set backup collection name
			$backup_collection = $collection . "_" . date("Ym", $timestamp);

			// Check exists
			$exists = $mongo_db->where("id", $_id)->getOne($backup_collection, $doc);
			if($exists) continue;
			
			// Insert backup collection
			$doc["_id"] = $_id;
			$result = $mongo_db->insert($backup_collection, $doc);
			if(!$result) continue;
			$count++;
			echo "Insert " . $count . " documents to " . $backup_collection . PHP_EOL;
			// Delete main collection
			$res = $mongo_db->where("id", $_id)->delete($collection);
			if($res) echo "Detete " . $count . " documents from " . $collection . PHP_EOL;
		}
	}
}

echo "END" . PHP_EOL;