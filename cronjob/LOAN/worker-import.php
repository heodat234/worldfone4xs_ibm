<?php

/*
 * Copyright © 2019 South Telecom
 */
require_once dirname(__DIR__) . "/Header.php";

use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');
$mongo_db = new Mongo_db();

echo "START" . PHP_EOL;
$starttime = time();
while($starttime > time() - 1200) {
    runQueue();
    sleep(1);
}
function runQueue() {
    global $queue;
    global $mongo_db;
    while ($job = $queue->watch("import")->ignore('default')->reserve(10)) {
        try {
            $queue->bury($job);

            $jData = json_decode($job->getData(), true);
            echo json_encode($jData) . PHP_EOL;
            $time = time();
            if($jData["startTimestamp"] > $time || empty($jData["doc"]) || empty($jData["collection"])) {
                 echo " ==> Job is not on time -> kick job " . PHP_EOL;
                $queue->kickJob($job);
               
            }
            
            if($time-$jData["startTimestamp"] > 3600){
                echo " ==> Job is over time -> delete job " . PHP_EOL;
                $queue->delete($job);
            } else {
                $doc = $jData["doc"];

                if(empty($jData["key_field"])) {
                    $doc["createdAt"] = time();
                    $doc["createdBy"] = "System";
                	$result = $mongo_db->insert($jData["collection"], $doc);
                } else {
                    $key_field = $jData["key_field"];
                    $where = array($key_field => $doc[$key_field]);
                    if(!empty($jData["key_field_2"])) {
                        $key_field_2 = $jData["key_field_2"];
                        $where[$key_field_2] = $doc[$key_field_2];
                    }
                    if($mongo_db->where($where)->getOne($jData["collection"])) {
                        if(!isset($doc["updatedAt"])) 
                            // $doc["updatedAt"] = time();
                            $doc['updatedAt'] = strtotime('14-01-2020 10:59:59');
                        $doc["updatedBy"] = "System";
                        $result = $mongo_db->where($where)->set($doc)->update($jData["collection"]);
                    } else {
                        if(!isset($doc["createdAt"])) 
                            // $doc["createdAt"] = time();
                            $doc['createdAt'] = strtotime('14-01-2020 10:59:59');
                        $doc["createdBy"] = "System";
                        $result = $mongo_db->insert($jData["collection"], $doc);
                    }
                }
                if($result) {
                    echo " ==> Import is success -> delete job ". PHP_EOL;
                    $queue->delete($job);
                    if(!empty($jData["import_id"])) {
                        $mongo_db->where_id($jData["import_id"])->inc("complete", 1)
                        ->set(array("updatedAt" => $time))->set("status", 1)->update("LO_Import");
                    }
                } else {
                    throw new Exception("Import not success");
                }
            }
            
        } catch (Exception $ex) {
            echo $ex->getMessage(). PHP_EOL;
            $queue->delete($job);
            if(!empty($jData["import_id"])) 
                $mongo_db->where_id($jData["import_id"])
                ->push("error", $jData["doc"])
                ->set(array("updatedAt" => $time))->update("LO_Import");
            // Log error
            /*$doc = $jData["doc"];
            $doc["result"] = "error";
            $doc["import_id"] = $jData["import_id"];
            $mongo_db->insert($jData["collection"] . "_result", $doc);*/
        }
    }
}
echo "END" . PHP_EOL;
