<?php

/*
 * Copyright © 2014 South Telecom
 */

require_once $app_path . "/vendor/autoload.php";
require_once "Mongo_db.php";

use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');
while ($job = $queue->watch("import")->ignore('default')->reserve(10)) {
    try {
        $queue->bury($job);

        $jData = json_decode($job->getData(), false);
        echo json_encode($jData) . PHP_EOL;
        if ($jData->startTimestamp <= time() && !empty($jData->data) && !empty($jData->collection)) {
           
            $data = $jData->data;

            if(empty($jData->key_field)) {
            	$mongo_db->insert($jData->collection, $editedValue);
            } else {
            	$mongo_db->where($key_field, $data[$key_field])->set($editedValue)->update($jData->collection);
            }
            
        } else {
            echo " ==> Job is not on time -> kick job ". PHP_EOL;
            $queue->kickJob($job);
        }
    } catch (Exception $ex) {
             echo " ==> Job is not on excute -> kick job ".$ex. PHP_EOL;
    }
}
