<?php

/*
 * Copyright © 2019 South Telecom
 * Run once every second
 */
require_once dirname(__DIR__) . "/Header.php";

use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');
$mongo_db = new Mongo_db();

echo "START" . PHP_EOL;
$starttime = time();

while($starttime > time() - 300) {
    runFollowUp();
    sleep(5);
}

$timeBefore = 300;

function runFollowUp() {
    global $mongo_db;
    global $queue;
    global $timeBefore;
    $time = time();
    $followUpData = $mongo_db->where(["reCall" => ['$lt' => time() + $timeBefore], "addToAutoCallQueue" => ['$ne' => TRUE]])->get("LO_Follow_up");

    foreach ($followUpData as $followUpDoc) {

    	if(empty($followUpDoc["phone"])) {
    		echo " --> Don't have phone to call follow up " . $followUpDoc["id"] . PHP_EOL;
    		continue;
    	}

    	if(empty($followUpDoc["createdBy"])) {
    		echo " --> Don't have extension to call follow up " . $followUpDoc["id"] . PHP_EOL;
    		continue;
    	}

    	if(getStatusExtension($followUpDoc["createdBy"]) != 1) {
    		echo " --> Extension ".$followUpDoc["createdBy"]." is not ready" . PHP_EOL;
    		continue;
    	}

    	// Check have in ds goi hom nay
    	$check = $mongo_db->where_or(["phone"=>$followUpDoc["phone"],"other_phones"=>$followUpDoc["phone"]])
        ->where("createdAt", ['$gt'=>strtotime("today midnight")])
        ->where("Donotcall", "N")->getOne("LO_Diallist_detail");
    	if(!$check) {
    		$doc = $mongo_db->where_id($followUpDoc["id"])->getOne("LO_Follow_up");
            $doc["createdAt"] = $mongo_db->date();
    		$mongo_db->insert("LO_Follow_up_deleted", $doc);
    		$mongo_db->where_id($followUpDoc["id"])->delete("LO_Follow_up");
    		continue;
    	}


    	$jData = array(
			"callernum" 		=> $followUpDoc["createdBy"],
			"destnum"			=> $followUpDoc["phone"],
			"diallistDetailId"	=> isset($followUpDoc["foreign_id"]) ? $followUpDoc["foreign_id"] : "",
			"startTimestamp"	=> $time + $timeBefore
		);

		echo " --> Add job call ". $jData["callernum"] . " => " . $jData["destnum"] . PHP_EOL;  
		$queue->useTube("call")->put(json_encode($jData));

		// Create notification
		$countMinutes = ceil($timeBefore / 60);
		$notification = array(
			"title" => "Gọi lại theo lịch hẹn",
			"active" => true,
			"icon" => "fa fa-phone-square",
    		"color" => "text-warning",
			"content" => "Bạn có 1 cuộc gọi lại tự động theo lịch hẹn cho số điện thoại <b>".$jData["destnum"]."</b> vào " . date("H:i:s d-m-Y", $jData["startTimestamp"]) . ".",
			"link" => "/manage/follow_up",
			"to" => [ 
		        $followUpDoc["createdBy"]
		    ],
		    "notifyDate" => $mongo_db->date($time),
		    "createdBy" => $followUpDoc["createdBy"],
		    "createdAt" => $time
		);

		$mongo_db->insert("LO_Notification", $notification);

		$mongo_db->where_id($followUpDoc["id"])->set("addToAutoCallQueue", TRUE)->update("LO_Follow_up");
    }
}

function getStatusExtension($extension) {
	global $mongo_db;
	$doc = $mongo_db->where(array("extension" => $extension, "endtime" => 0))
 			->where_gt("lastupdate", time() - 10)
 			->order_by(array('starttime' => -1))
 			->getOne("LO_Agent_status");
	return isset($doc["statuscode"]) ? $doc["statuscode"] : null;
}

echo "END" . PHP_EOL;
