<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "makeCallingList_logs.txt");

require_once dirname(__DIR__) . "/Header.php";
$today = date('d', time());

$mongo_db               = new Mongo_db();

$due_date_cong1 = '02';

$mongo_db->where('kydue', $due_date_cong1)->delete_all('LO_SBV_Stored');
