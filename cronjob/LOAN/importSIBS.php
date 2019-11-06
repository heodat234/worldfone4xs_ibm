<?php
define('BASEPATH', pathinfo(__FILE__, PATHINFO_BASENAME));

require_once "../../vendor/autoload.php";
require_once "Mongo_db.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

//$inputFileName = "../../upload/ftp/telesales/ZACCF.csv";
$inputFileName = "LIST_OF_ACCOUNT_IN_COLLECTION_20191031.csv";
$mongo_db = new Mongo_db();

$delimiter = ",";
$length = 0;
$column = 10;

$file = fopen($inputFileName,"r");

$data = [];
$header = array();

$starttime = microtime(true);

$count = 0;

while(!feof($file))
{
    $temp = fgetcsv($file, $length, $delimiter);
    if(!empty($temp)) {
    	$editedValue = array_slice($temp, 0, $column);
        if(empty($data)) {
            $header = array_values(array_filter($editedValue));
        }  
        if(empty($editedValue[0])) break;

        /*$pheanstalk
		  ->useTube('import')
		  ->put(json_encode(value));*/
        //$count++;
        echo "$count\t"; print_r(implode("\t\t", $editedValue)); echo PHP_EOL;
        /*$mongo_db->insert("LO_Test", $editedValue);
        echo PHP_EOL . "Inserted " . (++$count);*/
        usleep( 50000 );
        //array_push($data, $editedValue);
    }
}

fclose($file);

$endtime = microtime(true);
echo PHP_EOL . ($endtime - $starttime) . " Seconds";
echo PHP_EOL . memory_get_usage() . " Bytes";
echo PHP_EOL;