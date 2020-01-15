<?php

/*
 * Copyright © 2019 South Telecom
 * Run once every second
 */
require_once dirname(__DIR__) . "/Header.php";

use Pheanstalk\Pheanstalk;

$limitCall = 30;

$queue = new Pheanstalk('127.0.0.1');
$mongo_db = new Mongo_db();

echo "START" . PHP_EOL;
$starttime = time();
while(1) {
    runAutoCall();
    sleep(1);
}
function runAutoCall() {
    global $limitCall;
    global $queue;
    global $mongo_db;

    $running_diallists = $mongo_db->where(array("runStatus"=>true, "mode" => "auto"))->get("LO_Diallist");
    shuffle($running_diallists);
    foreach ($running_diallists as $diallist) {
        echo " --> ".$diallist["name"]." is running" . PHP_EOL;

        $countDialInProcess = $mongo_db->count("LO_Dial_in_process");
        
        if($countDialInProcess >= $limitCall) {
            echo " --> Concurrent call greater than or equal limit $limitCall." . PHP_EOL;
            continue;
        }

        $dialQueueDoc = $mongo_db->where(
            ["diallist_id" => $diallist["id"], "called" => ['$ne' => TRUE]]
        )->order_by(["spin"=>1,"priority"=>1,"index"=>1])->getOne("LO_Dial_queue");

        if(!$dialQueueDoc) {
            echo " --> Don't have any dial queue of diallist " . $diallist["name"] . PHP_EOL;
            continue;
        }

        // Check In process
        if($mongo_db->where_or(["dialQueueId"=>$dialQueueDoc["id"], "phone"=>$dialQueueDoc["phone"]])->getOne("LO_Dial_in_process")) {
            echo " --> ".$dialQueueDoc["id"]." is already running" . PHP_EOL;
            continue;
        }
        //

        echo " --> Now run dial queue " . $dialQueueDoc["id"] . " for diallist " . $diallist["name"] . PHP_EOL;

        // Check Queue
        $queueDoc = getAssignedQueue($dialQueueDoc["diallist_id"]);
        if(!$queueDoc) {
            echo " --> ".$dialQueueDoc["diallist_id"]." can't find queue" . PHP_EOL;
            continue;
        }

        if(empty($queueDoc["queuename"]) || empty($queueDoc["members"])) {
            echo " --> ".$dialQueueDoc["diallist_id"]." don't have queue name or members" . PHP_EOL;
            continue;
        }

        echo " --> Queue ".$queueDoc["queuename"]." is setting" . PHP_EOL;
        //

        // Check In process
        $countQueueInProcess = $mongo_db->where(["queuename"=>$queueDoc["queuename"]])->count("LO_Dial_in_process");
        $coefficient = !empty($diallist["coefficient"]) ? (double) $diallist["coefficient"] : 1.5;
        $countQueueMembers = count($queueDoc["members"]);
        if($countQueueInProcess > ($coefficient *$countQueueMembers)) {
            echo " --> countQueueInProcess({$countQueueInProcess}) > {$coefficient} * countQueueMembers({$countQueueMembers})" . PHP_EOL;
            continue;
        }
        //

        // Check agent status members
        $countAgentReady = 0;
        foreach ($queueDoc["members"] as $extension) {
            $agentstatus = getStatusExtension($extension);
            // Ready and oncall
            if(in_array($agentstatus, [1,2])) {
                $countAgentReady++;
            }
        }
        if(!$countAgentReady) {
            echo " --> Don't have any agent ready" . PHP_EOL;
            continue;
        }
        
        if($countQueueInProcess > $coefficient * $countAgentReady) {
            echo " --> countQueueInProcess({$countQueueInProcess}) > {$coefficient} * countAgentReady({$countAgentReady})" . PHP_EOL;
            continue;
        }

        echo " --> Catch up queue ".$queueDoc["queuename"]." ready for " .$diallist["name"] . PHP_EOL;

        $countSameCaseInProcess = $mongo_db->where("diallistDetailId", $dialQueueDoc["diallistdetail_id"])->count("LO_Dial_in_process");

        $jData = array(
            "callernum"         => $queueDoc["queuename"],
            "destnum"           => $dialQueueDoc["phone"],
            "diallistDetailId"  => $dialQueueDoc["diallistdetail_id"],
            "dialQueueId"       => $dialQueueDoc["id"],
            "startTimestamp"    => time()
        );
        echo " --> Add job call ". $dialQueueDoc["phone"] . " => " . $queueDoc["queuename"] . PHP_EOL;

        $queue->useTube("call")->put(json_encode($jData), $countSameCaseInProcess * $countAgentReady);

        $mongo_db->insert("LO_Dial_in_process", array(
            "queuename"             => $queueDoc["queuename"],
            "phone"                 => $dialQueueDoc["phone"],
            "diallistId"            => $dialQueueDoc["diallist_id"],
            "diallistDetailId"      => $dialQueueDoc["diallistdetail_id"],
            "dialQueueId"           => $dialQueueDoc["id"],
            "createdAt"             => $mongo_db->date()
        ));

        $mongo_db->where_id($dialQueueDoc["diallistdetail_id"])->set("spin", $dialQueueDoc["spin"])->update("LO_Diallist_detail");
        usleep(100000);
    }
}

function getAssignedExtension($diallist_detail_id) {
    global $mongo_db;
    $doc = $mongo_db->where_id($diallist_detail_id)->getOne("LO_Diallist_detail");
    return !empty($doc["assign"]) ? $doc["assign"] : "";
}

function getStatusExtension($extension) {
    global $mongo_db;
    $doc = $mongo_db->where(array("extension" => $extension, "endtime" => 0))
            ->where_gt("lastupdate", time() - 10)
            ->order_by(array('starttime' => -1))
            ->getOne("LO_Agent_status");
    return isset($doc["statuscode"]) ? $doc["statuscode"] : null;
}

function getAssignedQueue($diallist_id) {
    global $mongo_db;
    $doc = $mongo_db->where_id($diallist_id)->getOne("LO_Diallist");
    $group_id = isset($doc["group_id"]) ? $doc["group_id"] : null;
    if(!$group_id) return null;
    $queueData = $mongo_db->where(["customGroups"=>$group_id,"type"=>"queue"])->get("LO_Group");
    if(!$queueData) return null;
    $k = array_rand($queueData);
    return $queueData[$k]; 
}
echo "END" . PHP_EOL;
