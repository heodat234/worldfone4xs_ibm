<?php
error_reporting(-1);
ini_set('display_errors', 1);
$folder = "cronjob";
$app_path = dirname(__DIR__);
require_once $app_path . "/vendor/autoload.php";
use Pheanstalk\Pheanstalk;
$pheanstalk = new Pheanstalk('127.0.0.1');
echo "START" . PHP_EOL;

// ----------------------------------------
// producer (queues jobs)

$pheanstalk
  ->useTube('triggerAutomation')
  ->put("job payload goes here\n");

// ----------------------------------------
// worker (performs jobs)

$job = $pheanstalk
  ->watch('triggerAutomation')
  ->ignore('default')
  ->reserve();

echo $job->getData();

$pheanstalk->delete($job);