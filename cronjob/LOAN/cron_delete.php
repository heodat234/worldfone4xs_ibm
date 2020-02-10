<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$collection = "LO_Temporary_payment";

$mongo_db->delete_all($collection);

$mongo_db->delete_all('LO_LNJC05');

$mongo_db->delete_all('LO_List_of_account_in_collection');

$mongo_db->delete_all('LO_SBV');

