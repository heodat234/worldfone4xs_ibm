<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$collection = "TS_Sibs";

$total = $mongo_db->count("LO_ZACCF");

$maxRecordsPerPage = 10000;

$pages = ceil($total / $maxRecordsPerPage);

for ($i=0; $i < $pages; $i++) { 

	$data = $mongo_db->select(["account_number","CUS_ID","name","W_ORG","B_ADV"])->offset($i * $maxRecordsPerPage)->limit($maxRecordsPerPage)->get("LO_ZACCF");
	$count = 0;
	foreach ($data as $temp) {
		
		$doc = [];
		$doc["account_no"] 		= isset($temp["account_number"]) ? $temp["account_number"] : "";
		$doc["cif"] 			= isset($temp["CUS_ID"]) ? $temp["CUS_ID"] : "";
		$doc["cus_name"] 		= isset($temp["name"]) ? $temp["name"] : "";
		$doc["current_balance"] = isset($temp["W_ORG"]) ? $temp["W_ORG"] : "";
		$doc["advance"] 		= isset($temp["B_ADV"]) ? $temp["B_ADV"] : "";

		$count++;
		echo "NO.{$count}\t"; print_r(implode("\t\t", $doc)); echo PHP_EOL;

		$queueData = array(
	        "startTimestamp"    => time(),
	        "doc"               => $doc,
	        "collection"        => $collection,
	        "key_field"         => "account_no",
	        "key_field_2"       => "cif",
	    );
		$queue->useTube('import')->put(json_encode($queueData));
	}

}

$endtime = microtime(true);
echo PHP_EOL . "TIME EXECUTE: " . ($endtime - $starttime) . " Seconds";
echo PHP_EOL . "RAM USAGE: " . memory_get_usage() . " Bytes";
echo PHP_EOL . "TOTAL: " . $count . " Records";
echo PHP_EOL . "END" . PHP_EOL;