<?php

/*
 * Copyright © 2014 South Telecom
 */
ini_set("log_errors", 1);
ini_set("error_log", "schedule_logs.txt");
require_once dirname(__DIR__) . '/beanstalk/autoload.php';
require_once 'mongodriver.php';
$mongodb= new MyMongoDriver('worldfone4x');
//$queue = new Pheanstalk\Pheanstalk('127.0.0.1');
$config_info=$mongodb->where(array("callcenter_type"=>"co"))->getOne('wff_config');
while(1){
	handle_data();
	sleep(3);
}
//var_dump($queue);
function handle_data(){
    global $mongodb;
    //global $queue;
    global $config_info;
    $secret=$config_info['secret_key'];
    $available_list= get_Available_List();
    $busy_list= get_busy_list();
    $list_dialist=get_list_diallist(); // Nhung diallist dialType='3' va status 1
    $free_list=array();
    $now = new DateTime();
    $pingtime=$now->getTimestamp();
	foreach($list_dialist as $dialist){ 
		
		$group=get_group_by_id($dialist['group']);
		$diallistId = $dialist["_id"]->{'$id'};
		echo "DIALLIST START {$diallistId}".PHP_EOL; 
		
		foreach($group['members'] as $extension){  
			echo "EXTENSION {$extension}".PHP_EOL;
			//echo $extension; 
			$time = time();
			if( in_array($extension,$available_list) && !in_array($extension, $busy_list)){
				echo "PROCESS {$extension}".PHP_EOL;
				if(isset($diallist["ping_time_{$extension}"]))
					if($diallist["ping_time_{$extension}"] > ($time - 60)) continue;
				$where                  = array(
					'diallistId' 	=> $diallistId,
					'assign'        => $extension,
					'called'		=> array('$ne' => true),
					'complete'		=> array('$ne' => true),
					'loop'			=> 0
				);
				$dialStack = $mongodb->where($where)->where_lt('re_call', $time)
														->limit(2)->order_by(array('index' => 1))
														->get('dialStack');
				$dialStack = $dialStack ? $dialStack : $mongodb->where($where)->where_lt('re_call', $time)
														->limit(2)->order_by(array('index' => 1))
														->get('dialStack');
				// Neu $dialStack tuc la con cuoc goi duoc assign, khong can assign moi
				
				if(!$dialStack) {
					// Assign moi
					$whereDetail = array(
						"_diallistId" 	=> new MongoId($diallistId),
						"assign"		=> array('$exists' => false)
					);
					$diallistDetail = $mongodb->where($whereDetail)->getOne("diallistDetail");
					$diallistDetail = $diallistDetail ? $diallistDetail : $mongodb->where($whereDetail)->getOne("diallistDetail");
					$diallistDetailId = $diallistDetail['_id']->{'$id'};
					$mongodb->where(array("_id" => new MongoId($diallistDetailId)))->update("diallistDetail", array('$set' => array("assign" => $extension, "timeAssign" => $time)));
					$mongodb->where(array(
						"assign" => array('$exists' => false),
						"diallistDetailId" => $diallistDetailId
					))->update_all("dialStack", array('$set' => array("assign" => $extension, "timeAssign" => $time)));
					echo "ASSIGN {$diallistDetailId} TO {$extension}".PHP_EOL;
				}
				
                $where                  = array(
					'diallistId' 	=> $diallistId,
					'called'		=> array('$ne' => true),
					'complete'		=> array('$ne' => true),
					'assign'		=> $extension
				);
				$dialStackList = $mongodb->where($where)->where_lt('re_call', $time)
														->limit(2)->order_by(array('index' => 1))
														->get('dialStack');
				$dialStackList = $dialStackList ? $dialStackList : $mongodb->where($where)->where_lt('re_call', $time)
														->limit(2)->order_by(array('index' => 1))
														->get('dialStack');
				echo "CHECK DIAL STACK".PHP_EOL;
				//print_r($dialStackList);die();
				if(!$dialStackList) continue;
				$dialStack = $dialStackList[0];
				if(isset($dialStack["phone"]) && isset($dialStack["diallistDetailId"])){
					if($dialStack["diallistDetailId"] && $dialStack["phone"] && $extension) {
						echo "CALL {$dialStack["phone"]}. BUSY {$extension}".PHP_EOL;
						 //đẩy vào queue, insert busy list
						// $callJob = new stdClass();
						// $callJob->secret = $secret;
						// $callJob->callernum = $extension;
						// $callJob->destnum=$dialStack["phone"];                           
						// $callJob->dialid=$dialStack["diallistDetailId"];
						// $callJob->dialStackId = $dialStack["_id"]->{'$id'};
						// $callJob->startTimestamp = $pingtime - 2;
						// echo json_encode($callJob) . PHP_EOL;
					
						// $queue->useTube("calljobs")->put(json_encode($callJob));
						
						$dialStackId = $dialStack["_id"]->{'$id'};
						$httpQuery = http_build_query(array(
							"callernum" => $extension,
							"destnum" => $dialStack["phone"],
							"dialid" => $dialStack["diallistDetailId"],
							"secrect" => $secret,
						));
						
						$url = "http://127.0.0.1:8089/externalcrm/makecall2.php?".$httpQuery;
						echo $url.PHP_EOL;
						echo "make call". PHP_EOL;
						$response = file_get_contents($url);
						echo $response. PHP_EOL;
						
						if($response=="200"){
							if(isset($dialStackId)) $mongodb->where("_id", new MongoId($dialStackId))->update("dialStack", array('$set' => array("called" => true, "autoCall" => true, "callTime" => $time)));
						}
						$mongodb->insert("extension_inprocess",array("extension"=>$extension,"create_time"=>$time));
					}
				}
			}
		}
	} 
}

function get_Available_List(){
    global $mongodb;
    $now = new DateTime();
    $pingtime=$now->getTimestamp();
    $time_limit=$pingtime-60;
    $agents_available=$mongodb->where(array('endtime'=> "", 'statuscode' => array('$in' => [1, "1"])))->where_gt("lastupdate",$time_limit)->get('agentstatuslogs_realtime');
	
    //$agent_idle=$mongodb->where(array('status'=>"IDLE"))->get('extension_status');
	
    $free_agents=array();
    foreach($agents_available as $available){
        // foreach($agent_idle as $idle){
            // if($available['userextension']==$idle['extension']){
                // $free_agents[]=$available['userextension'];
            // }
        // }
		if(isset($available["agentstate"]) && isset($available["agentstate"]["state"])) {
			if($available["agentstate"]["state"] == "IDLE") {
				$free_agents[] = $available["userextension"];
			}
		}
    }
	//print_r($free_agents);die();
    return $free_agents;
 
}
function get_list_diallist(){
    global $mongodb;
    $data = $mongodb->select([], ["field", "cretime", "owner","name", "info", "type"])->where(array("dialType"=>"3","status"=>1))->where_ne("delete",1)->get("diallist");
    return $data;
}
function get_busy_list(){
    global $mongodb;
    $data = $mongodb->get("extension_inprocess");
	$data = $data ? $data : $mongodb->get("extension_inprocess");
    $busy_list=array();
    foreach ($data as $row){
        $busy_list[]=$row['extension'];
    }
    return $busy_list;
}
function get_group_by_id($str_id){
    global $mongodb;
    $data= $mongodb->where(array("_id"=>New MongoId($str_id)))->getOne("co_groups");
    return $data;
}
function get_Dialist_by_Group($id_group){
    global $mongodb;
    $data= $mongodb->select(array("name"))->where(array("group"=>$id_group))->getOne("diallist");
    return $data;
}
function getDiallistDetailByDiallistId($dialistId){
    global $mongodb;
    $data= $mongodb->where(array("_diallistId"=>new MongoId($dialistId)))->getOne("diallistDetail");
    return $data;
}

function add_busy_list($extension){
    global $mongodb;
    $mongodb->insert("extension_inprocess",array("extension"=>$extension,"create_time"=>time()));
}