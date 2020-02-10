<?php
define('BASEPATH', pathinfo(__FILE__, PATHINFO_BASENAME));
error_reporting(-1);
ini_set('display_errors', 1);
// ini_set("log_errors", 1);
$app_path = dirname(__DIR__);
// ini_set("error_log", $app_path . "/PHP_errors.log");

require_once $app_path . "/vendor/autoload.php";
require_once "Mongo_db.php";
require_once $project_path . "/application/config/_mongo.php";

// HELPER FUNCTION
function convertStringToTimestamp($str) {
	// 311019 -> 2019-10-31
	$dateArr = str_split($str, 2);
	$dateStr = "20";
	for ($i=count($dateArr)-1; $i >= 0; $i--) { 
		$dateStr .= $dateArr[ $i ] . "-";
	}
	return strtotime(trim($dateStr, "-"));
}

function show_error($message, $code) {
	throw new Exception($message, $code);
}


function _isset($data, $field){
    if(isset($data[$field]))
        return $data[$field];
    else
        return '';
}

function convertToCurrency($money){
	$money = number_format((double)$money, 2);
	$money = str_replace('.00','',(string)$money);
	return $money;
}