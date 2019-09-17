<?php
error_reporting(-1);
ini_set('display_errors', 1);
$app_path = str_replace("cronjob", "", __DIR__);
require_once $app_path . "/index.php";
require_once $app_path . "/vendor/autoload.php";
//require_once __DIR__ . "/mongo_db.php";
// Hopefully you're using Composer autoloading.

use Pheanstalk\Pheanstalk;
//var_dump(new Pheanstalk());
// Create using autodetection of socket implementation
$pheanstalk = new Pheanstalk('127.0.0.1');

// ----------------------------------------
// producer (queues jobs)

/*$pheanstalk
  ->useTube('agent_status')
  ->put("job payload goes here\n");*/
while(1){
	process();
	sleep(1);
}

// ----------------------------------------
// worker (performs jobs)
function process() {
	global $pheanstalk, $CI;
	//$mongo = new Mongo_db();
	$CI->load->library("mongo_db");
	$data = $CI->mongo_db->get("Agent_sign");
	echo json_encode($data);
	echo "START ".microtime(true).PHP_EOL;
	while ($job = $pheanstalk->watch("agent_status")->ignore('default')->reserve(2)) {
		$pheanstalk->bury($job);
		$data = $job->getData();
		write_log($data);
		echo $data;
		$pheanstalk->delete($job);
	}
	echo "END ".microtime(true).PHP_EOL;	
}

function write_log($content = "") {
	global $app_path;
	$now = date("Y-M-d H:i:s");  		
	$fd = fopen ($app_path . "/cron.log", 'a');
	$log = "$now - $content \n";
	fwrite($fd,$log);
	fclose($fd);
}