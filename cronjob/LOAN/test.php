<?php
$inputFileName = "/data/upload_file/20200213/ZACCF.txt";

$delimiter = ";";
$length = 0;
$startColumn = 0;
$endColumn = 9;
$count = 0;

$file = fopen($inputFileName, "r");

$collection = "LO_ZACCF";

$key_field = "account_number";
$key_field_2 = "CUS_ID";

$starttime = microtime(true);

// Import
echo "START" . PHP_EOL;
while (!feof($file)) {
    $temp = fgetcsv($file, $length, $delimiter);
    ++$count;
    echo "NO.{$count}\t";
    echo PHP_EOL;
}

fclose($file);

$endtime = microtime(true);
echo PHP_EOL . "TIME EXECUTE: " . ($endtime - $starttime) . " Seconds";
echo PHP_EOL . "RAM USAGE: " . memory_get_usage() . " Bytes";
echo PHP_EOL . "TOTAL: " . $count . " Records";
echo PHP_EOL . "END" . PHP_EOL;