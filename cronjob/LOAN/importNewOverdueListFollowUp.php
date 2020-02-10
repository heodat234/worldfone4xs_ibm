<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

//$inputFileName = "../../upload/ftp/telesales/ZACCF.csv";
$folder = "YYYYMMDD";
$inputFileName = "/data/upload_file/{$folder}/New_Overdue list follow up daily ABCDEF.csv";
$mongo_db = new Mongo_db();

$delimiter = ";";
$length = 0;
$startColumn = 1;
$endColumn = 9;

$file = fopen($inputFileName,"r");

$collection = "LO_Customer";

$key_field = "LIC_NO";

$header = ["LIC_NO","account_number","name","product_type","montly_installment","overdue_amt","os_balance","description"];

$endColumn = count($header) - 1;

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
    $index_result = $mongo_db->add_index($collection, [$key_field => -1]);
}

// Import
echo "START" . PHP_EOL;

while(!feof($file))
{
    $temp = fgetcsv($file, $length, $delimiter);
    if(!empty($temp)) {

        // Condition data
    	$editedValue = array_slice($temp, $startColumn, $endColumn + 1 + $startColumn);

        if(!is_numeric($editedValue[0])) continue;
        
        $doc = [];
        if($header) {
            foreach ($header as $index => $field) {
                $doc[$field] = isset($editedValue[$index]) ? $editedValue[$index] : null;
            }
        }

        unset($doc["montly_installment"], $doc["overdue_amt"], $doc["os_balance"]);

        $queueData = array(
            "startTimestamp"    => time(),
            "doc"               => $doc,
            "collection"        => $collection,
            "key_field"         => $key_field,
            "import_id"         => $import_log["id"]
        );

        $queue->useTube('import')->put(json_encode($queueData));
        ++$count;

        echo "NO.{$count}\t"; print_r(implode("\t\t", $editedValue)); echo PHP_EOL;
    }
}

fclose($file);

$mongo_db->where_id($import_log["id"])->set("total", $count)->set("status", 2)->set("command", "/usr/bin/php " .  __FILE__)->update("LO_Import");

$endtime = microtime(true);
echo PHP_EOL . "TIME EXECUTE: " . ($endtime - $starttime) . " Seconds";
echo PHP_EOL . "RAM USAGE: " . memory_get_usage() . " Bytes";
echo PHP_EOL . "TOTAL: " . $count . " Records";
echo PHP_EOL . "END" . PHP_EOL;