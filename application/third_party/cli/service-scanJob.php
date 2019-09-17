<?php

/*
 * Copyright © 2014 South Telecom
 */

require_once dirname(__DIR__) . '/beanstalk/autoload.php';

$queue = new Pheanstalk\Pheanstalk('127.0.0.1');

$LOCKFILE = "/var/lock/subsys/wfscanjobd";
$loopforever = TRUE;
while ($loopforever) {
    while ($job = $queue->watch("calljobs")->ignore('default')->reserve(1)) {
        try {
            $queue->bury($job);
            
            $callJob = json_decode($job->getData(), false);
            echo json_encode($callJob) . PHP_EOL;
            if ($callJob->startTimestamp <= time()) {
                echo " ==> Job is on time " . date('c',$callJob->startTimestamp) . "[". date('c',time()) . "]" . " -> run job " . PHP_EOL;
                //do some thing
                //and delete job
                $queue->delete($job);
            } else {
                echo " ==> Job is not on time -> kick job ". PHP_EOL;
                $queue->kickJob($job);
            }
        } catch (Exception $ex) {
            
        }
    }
    $loopforever = file_exists($LOCKFILE);
}