<?php

/*
 * Copyright © 2019 South Telecom
 */
require_once dirname(__DIR__) . "/Header.php";

use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');
$mongo_db = new Mongo_db();
$_mongo_db = new Mongo_db();
$_mongo_db->switch_db($config['_mongo_db']);

$numberOfQueue = 4;

$configType = $_mongo_db->where("type", "LO")->getOne("ConfigType");

echo "START" . PHP_EOL;
$starttime = time();
while($starttime > time() - $numberOfQueue * 60) {
    runQueue();
    sleep(1);
}
function runQueue() {
    global $queue;
    global $mongo_db;
    global $_mongo_db;
    global $configType;

    $k=0;
    $delay = 2;
    while ($job = $queue->watch("call")->ignore('default')->reserve(10)) {
    	$k++;
        try {
            $queue->bury($job);

            $jData = json_decode($job->getData(), true);
            echo json_encode($jData) . PHP_EOL;
            $time = time();
            if($jData["startTimestamp"] > $time) {
                $queue->kickJob($job);
                echo " ==> Job is not on time -> kick job ";
                continue;
            }

            if(empty($jData["diallistDetailId"]) || empty($jData["callernum"]) || empty($jData["destnum"])) {
                throw new Exception(" ==> Lack of data -> delete job ");
            }
            
            if($time-$jData["startTimestamp"] > 300){
            	throw new Exception(" ==> Job is over time -> delete job ");
            } else {
                
                if(isset($jData["dialQueueId"])) {
                    // MAKE CALL 3
                    $httpQuery = http_build_query(array(
                        "callback"      => trim($jData["callernum"]),
                        "callto"        => trim($jData["destnum"]),
                        "callback_type" => "queue",
                        "dialid"        =>  base64_encode(json_encode(array(
                            "dialid"        => $jData["diallistDetailId"], 
                            "dialtype"      => "auto",
                            "dialQueueId"   => $jData["dialQueueId"],
                            "makecalltype"  => 3
                        ))),
                        "secret"       => $configType["secret_key"],
                    ));
                    
                    $url = $configType["pbx_url"] . "externalcrm/makecall3.php?".$httpQuery;
                } else {
                    // MAKE CALL 2
                    $httpQuery = http_build_query(array(
    					"callernum" => trim($jData["callernum"]),
    					"destnum" 	=> trim($jData["destnum"]),
    					"dialid" 	=>  base64_encode(json_encode(array(
    						"dialid" 		=> $jData["diallistDetailId"], 
    						"dialtype" 		=> "auto",
                            "makecalltype"  => 2
    					))),
    					"secret" 	=> $configType["secret_key"],
    				));
    				
    				$url = $configType["pbx_url"] . "externalcrm/makecall2.php?".$httpQuery;
                }
				echo "Make call " . $url . PHP_EOL;
				$response = file_get_contents($url);
				echo $response. PHP_EOL;

				if(empty($response)) {
                    $queue->delete($job);
                    $queue->useTube("call")->put($job->getData(), $k, 2);
                    echo " ==> Call not success (response empty) -> put job again ". PHP_EOL;
                    continue;
                }
				if($response!="200") {
                    if(isset($jData["dialQueueId"])) {
                        $mongo_db->where_id($jData["dialQueueId"])->set(array("called" => TRUE, "calledAt" => $mongo_db->date(), "error" => $response))->update("LO_Dial_queue");
                        $mongo_db->where("dialQueueId", $jData["dialQueueId"])->delete("LO_Dial_in_process");
                    }
                    throw new Exception(" ==> Call not success ($response) -> delete job ");
				}

                if(isset($jData["dialQueueId"])) {
                    $mongo_db->where_id($jData["dialQueueId"])->set(array("called" => TRUE, "calledAt" => $mongo_db->date()))->update("LO_Dial_queue");
                }

                $queue->delete($job);
                echo " ==> Call success -> delete job ". PHP_EOL;
            }
            
        } catch (Exception $e) {
            echo $e->getMessage(). PHP_EOL;
            $queue->delete($job);

            $doc["error"] = $e->getMessage();
            $doc["job"] = $jData;
            $mongo_db->insert("LO_Call_queue_error", $doc);
        }
    }
}
echo "END" . PHP_EOL;
