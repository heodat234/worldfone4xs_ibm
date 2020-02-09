<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "makeCallingList_logs.txt");

require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();
$arr_contractNo_partner = [];
$MAIN = [];
$today = date('Y-m-d', time());

// Group A
$officer_id = $mongo_db->where(array(
    'group_id' => array('$in' => ['A01', 'A02', 'A03']),
))->distinct('LO_LNJC05', 'officer_id');

foreach ($officer_id as $key => $id) {
    $url = "nohup php " . dirname(__DIR__) . "/LOAN/processes/process_makeCallingList_MAIN_A.php $id > /dev/null 2>&1 &";

    exec($url);
    echo $id . PHP_EOL;
}
