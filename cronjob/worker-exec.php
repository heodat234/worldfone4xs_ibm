<?php

/*
 * Copyright © 2019 South Telecom
 */
require_once __DIR__ . "/Header.php";

use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');

echo "START" . PHP_EOL;
$starttime = time();
$rangetime = 3600;

$queue->useTube("exec")->put(json_encode(["command"=>"COMMAND 1","startTimestamp"=>$starttime+10]));
$queue->useTube("exec")->put(json_encode(["command"=>"COMMAND 2","startTimestamp"=>$starttime+5]));
// $queue->useTube("exec")->put("COMMAND 2", 100, 0, $starttime + 10000);
// $queue->useTube("exec")->put("COMMAND 3", 100, 0, $starttime + 20000);

while($starttime > time() - $rangetime) {
    runQueue();
    sleep(10);
}

function runQueue() {
	global $queue;
    while ($job = $queue->watch("exec")->ignore('default')->reserve(10)) {
        try {
        	$time = time();
            $queue->bury($job);
            $jData = json_decode($job->getData(), TRUE);

            if(empty($jData["command"])) {
            	throw new Exception("Command empty");
            }

            if($jData["startTimestamp"] > $time) {
                $queue->delete($job);
                $jData["startTimestamp"] += 60;
                $queue->put(json_encode($jData), 0);
                echo " ==> Job is not on time -> kick job " . PHP_EOL;
                continue;
            }

            echo "RUNTIME: ". microtime() . PHP_EOL;
            echo "COMMAND: ". ($command) . PHP_EOL;
            
            //$result = shell_exec($command);
            $queue->delete($job);

            echo "ENDTIME: ". microtime() . PHP_EOL;
            echo "RESULT: ".$result . PHP_EOL;
            
        } catch (Exception $e) {
            echo $e->getMessage(). PHP_EOL;
            $queue->delete($job);
        }
    }
}

