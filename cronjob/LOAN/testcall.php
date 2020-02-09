<?php
require_once dirname(__DIR__) . "/Header.php";

use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

/*$data = array(
	"callernum" 		=> "911",
	"destnum"			=> "0987261660",
	"diallistDetailId"	=> "12DFFdsfssfDSFFSFsd",
	"dialQueueId"		=> "12DFFdsfssfDSFFSFsd",
	"startTimestamp"	=> time()
);

$queue->useTube("call")->put(json_encode($data));*/

$cdr_collection = "LO_worldfonepbxmanager";

$starttime = microtime(true);
echo "START" . PHP_EOL;

$month = 11;
$year = 2019;

$first = strtotime(date("{$year}-{$month}-1 00:00:00"));
$last = strtotime(date("{$year}-{$month}-t 23:59:59"));

$collection = $cdr_collection . "_" . date("Ym", $first);

$key_field = "calluuid";

// Create collection and index
$list = $mongo_db->command(["listCollections"=>1, "authorizedCollections"=> true, "nameOnly"=>true]);
$exists_collections = array_column($list, "name");
if(!in_array($collection, $exists_collections)) 
{
    $mongo_db->command(["create"=>$collection], FALSE);
    $index_result = $mongo_db->add_index($collection, [$key_field => -1]);
}

$count++;

while($doc = $mongo_db->where_between("starttime", $first, $last)->getOne($cdr_collection)) {
	$queueData = array(
        "startTimestamp"    => time(),
        "doc"               => $doc,
        "collection"        => $collection,
        "key_field"         => $key_field
    );
    $queue->useTube('import')->put(json_encode($queueData));
    $mongo_db->where_id($doc["id"])->delete($cdr_collection);
    $count++;
    echo "NO.{$count}\t"; print_r(implode("\t\t", $doc)); echo PHP_EOL;
}

$endtime = microtime(true);
echo PHP_EOL . "TIME EXECUTE: " . ($endtime - $starttime) . " Seconds";
echo PHP_EOL . "RAM USAGE: " . memory_get_usage() . " Bytes";
echo PHP_EOL . "TOTAL: " . $count . " Records";
echo PHP_EOL . "END" . PHP_EOL;