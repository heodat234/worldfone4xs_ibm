<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

//$inputFileName = "../../upload/ftp/telesales/ZACCF.csv";
$folder = "20191120";
$inputFileName = "/data/upload_file/{$folder}/LIST_OF_ACCOUNT_IN_COLLECTION_{$folder}";
$mongo_db = new Mongo_db();

$delimiter = " ";
$length = 0;
$startColumn = 0;
$endColumn = 9;

$file = fopen($inputFileName,"r");

$collection = "LO_Account";

$key_field = "account_number";
//$key_field_2 = strtolower("CUS_ID");

$mongo_db->switch_db("_worldfone4xs");

$header = [];

$model = $mongo_db->where(array("collection" => $collection))->order_by(array("index" => 1))->get("Model");

foreach ($model as $doc) {
    if(isset($doc["field"]) && !in_array($doc["field"], $header)) {
        $header[] = $doc["field"];
    }
}

$endColumn = count($header) - 1;

$mongo_db->switch_db();

$starttime = microtime(true);

$count = 0;

// Log import

$import_data = array(
    "collection"        => $collection, 
    "begin_import"      => $starttime,
    "complete_import"   => 0,
    "file_name"         => basename($inputFileName),
    "file_path"         => $inputFileName,
    "source"            => "Manual",
    "status"            => 2,
    "createdAt"         => time(),
    "complete"          => 0,
    "total"             => 0
);
$import_log = $mongo_db->insert("LO_Import", $import_data);

if(empty($import_log["id"])) exit();

// Create collection and index
$list = $mongo_db->command(["listCollections"=>1, "authorizedCollections"=> true, "nameOnly"=>true]);
$exists_collections = array_column($list, "name");
if(!in_array($collection, $exists_collections)) 
{
    $mongo_db->command(["create"=>$collection], FALSE);
    $index_array = [$key_field => -1];
    if(isset($key_field_2)) $index_array[$key_field_2] = -1;
    $index_result = $mongo_db->add_index($collection, $index_array);
}

// Import
echo "START" . PHP_EOL;

while(!feof($file))
{
    $temp = fgetcsv($file, $length, $delimiter);
    if(!empty($temp)) {
    	
    	$string_arr = array_values( array_filter($temp) );

    	$doc_arr = [];
    	$count_name_element = count($string_arr) - 8;
    	for ($i=0; $i < count($string_arr); $i++) { 
    		if($i >= 3 & $i < 3 + $count_name_element - 1) {
    			if(!isset($doc_arr[3])) {
    				$doc_arr[3] = "";
    			}
    			if(isset($string_arr[$i]))
    				$doc_arr[3] .= $string_arr[$i] . " ";
    		} else {
    			if(isset($string_arr[$i]))
    				$doc_arr[] = $string_arr[$i];
    		}

    	}
    	if(isset($doc_arr[3])) $doc_arr[3] = trim($doc_arr[3]);
    	//var_dump($doc_arr);
        // Condition data
    	$editedValue = array_slice($doc_arr, $startColumn, $endColumn + 1 - $startColumn);
        if(empty($editedValue[$endColumn-1-$startColumn])) continue;
        if(!strpos($editedValue[$endColumn], "/")) continue;
        //if(empty($editedValue[5])) continue;

        
        $doc = [];
        if($header) {
            foreach ($header as $index => $field) {
                $doc[$field] = $editedValue[$index];
            }
        }

        $queueData = array(
            "startTimestamp"    => time(),
            "doc"               => $doc,
            "collection"        => $collection,
            "key_field"         => $key_field,
            "import_id"         => $import_log["id"]
        );
        if(isset($key_field_2)) {
        	$queueData["key_field_2"] = $key_field_2;
        }

        $queue->useTube('import')->put(json_encode($queueData));
        ++$count;
        echo "NO.{$count}\t"; print_r(implode("\t\t", $editedValue)); echo PHP_EOL;
    }
}

fclose($file);

$mongo_db->where_id($import_log["id"])->set("total", $count)->update("LO_Import");

$endtime = microtime(true);
echo PHP_EOL . "TIME EXECUTE: " . ($endtime - $starttime) . " Seconds";
echo PHP_EOL . "RAM USAGE: " . memory_get_usage() . " Bytes";
echo PHP_EOL . "TOTAL: " . $count . " Records";
echo PHP_EOL . "END" . PHP_EOL;