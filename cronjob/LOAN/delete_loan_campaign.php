<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');
 
$mongo_db               = new Mongo_db();

$mongo_db->drop_db("LOAN_campaign_list");