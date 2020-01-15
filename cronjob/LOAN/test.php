<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", "/var/www/html/worldfone4xs_ibm/cronjob/LOAN/autoCreateDial_Logs.txt");

echo $test['123'];
exit;

require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');
$mongo_db = new Mongo_db();

ini_set('memory_limit', '-1');
$arr_contractNo_partner = [];

$cus_assigned_partner = $mongo_db->get('LO_Cus_assigned_partner');
foreach ($cus_assigned_partner as $key => $value) {
    $arr_contractNo_partner[] = $value['CONTRACTNR'];
}
echo 't1: ' . round(microtime(true) * 1000) . '//ram: ' . convert(memory_get_usage()) . PHP_EOL;
if (in_array('28030000186994', $arr_contractNo_partner)) {
// if ($arr_contractNo_partner['771030000001258000']) {
    echo 'true';
} else {
    echo 'false';
}
godown();
// if (in_array('21030000001724000', $arr_contractNo_partner)) {
//     echo 'true';
// } else {
//     echo 'false';
// }
godown();
echo 't2: ' . round(microtime(true) * 1000) . '//ram: ' . convert(memory_get_usage()) . PHP_EOL;

function convert($size)
{
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function godown()
{
    echo PHP_EOL;
}