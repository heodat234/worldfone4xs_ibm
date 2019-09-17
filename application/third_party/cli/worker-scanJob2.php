<?php

/*
 * Copyright © 2014 South Telecom
 */
echo "SCAN JOB".PHP_EOL;
ini_set("log_errors", 1);
ini_set("error_log", "schedule_logs.txt");
require_once dirname(__DIR__) . '/beanstalk/autoload.php';
require_once 'mongodriver.php';
$mongodb= new MyMongoDriver('worldfone4x');
$queue = new Pheanstalk\Pheanstalk('127.0.0.1');
//var_dump($queue);
$k=0;
while ($job = $queue->watch("calljobs")->ignore('default')->reserve(10)) {
	$k++;
    try {
        $queue->bury($job);
		$time = time();
        $callJob = json_decode($job->getData(), false);
        echo json_encode($callJob) . PHP_EOL;
        if ($callJob->startTimestamp <= $time) {
            echo " ==> Job is on time " . date('c',$callJob->startTimestamp) . "[". date('c',time()) . "]" . " -> run job " . PHP_EOL;
            //do some thing 
			if((time()-$callJob->startTimestamp)>3600){
				$callernum=$callJob->callernum;
				$DiallistId=$callJob->dialid;
				free_extension($callernum,$DiallistId);
				$queue->delete($job);
				echo "Overtime".PHP_EOL;
			}else{				
				$agent_available=get_Available_List();
				$callernum=$callJob->callernum;
				if(in_array($callernum, $agent_available)){
					if(!$callJob->dialid) throw new Exception("No diallist detail");
					$destnum=$callJob->destnum;
					$secret=$callJob->secret;
					$DiallistId=$callJob->dialid;
					//$dialStackId = $callJob->dialStackId;
					$data_dial=get_dialistDetailbyID($DiallistId);
					if(is_array($data_dial) && count($data_dial) && diallist_is_run($data_dial['_diallistId'])){
						$httpQuery = http_build_query(array(
							"callernum" => $callernum,
							"destnum" => $destnum,
							"dialid" => $DiallistId,
							"secrect" => $secret,
						));
						
						$url = "http://127.0.0.1:8089/externalcrm/makecall2.php?".$httpQuery;
						echo $url.PHP_EOL;
						echo "make call". PHP_EOL;
						$response = file_get_contents($url);
						echo $response. PHP_EOL;
						sleep(2);
						$err = false;
						//$responseArr = json_decode($response,true);
						 if (!$err) {
							if($response!=null){
								if($response=="200"){
									$queue->delete($job);
									add_busy_list($callernum);
								}else{
									$data=$job->getData();
									$queue->delete($job);
									$queue->useTube("calljobs")->put($data,$k,2);
									echo " ==> Job is not on excute -> kick job ". PHP_EOL;
								}
								// update called dialStack
								// if(isset($dialStackId)) $mongodb->where("_id", new MongoId($dialStackId))->update("dialStack", array('$set' => array("called" => true, "autoCall" => true)));
								
								$log = (array) $callJob;
								$log["time"] = $time;
								$log["create_time"] = date("c", $time);
								$log["response"] = $response;
								$mongodb->insert("beanstalkLogs", $log);
							}else{
								echo " ==> Job is not on excute -> kick job ". PHP_EOL;
							    $data=$job->getData();
								$queue->delete($job);
								$queue->useTube("calljobs")->put($data,$k,2);
						   }         
						}else{
							echo " ==> Job is not on excute Can't make call  -> kick job ". PHP_EOL;
							
						   $queue->kickJob($job);
					   }
					}else{
						
						//$callernum=$callJob->callernum;
						//free_extension($callernum,$DiallistId);
						$queue->delete($job);
						sleep(1);
					}
				//and delete job
				}else{
					
					$data=$job->getData();
					$queue->delete($job);
					$queue->useTube("calljobs")->put($data,$k,2);
					sleep(1);
					echo " ==> Job is not on excute -> kick job ". PHP_EOL;
				}
			}			
        } else {
            echo " ==> Job is not on time -> kick job ". PHP_EOL;
            $queue->kickJob($job);
        }
    } catch (Exception $ex) {
        echo " ==> Job is not on excute -> delete job ".$ex->getMessage(). PHP_EOL;
		$queue->delete($job);
    }
}
if($k==0){
	clear_list();
}
function get_Available_List(){
    global $mongodb;
    $now = new DateTime();
    $pingtime=$now->getTimestamp();
    $time_limit=$pingtime-60;
    $agents_available=$mongodb->where(array('endtime'=>""))->where_in("statuscode", array("1",(int)1))->where_gt("lastupdate",$time_limit)->get('agentstatuslogs_realtime');
	//var_dump($agents_available);
    $agent_idle=$mongodb->where(array('status'=>"IDLE"))->get('extension_status');
	//var_dump($agent_idle);
    $free_agents=array();
    foreach($agents_available as $available){
        foreach($agent_idle as $idle){
            if($available['userextension']==$idle['extension']){
                $free_agents[]=$available['userextension'];
            }
        }
    }
    return $free_agents;
 
}

function get_busy_list(){
    global $mongodb;
    $data= $mongodb->get("extension_inprocess");
    $busy_list=array();
    foreach ($data as $row){
        $busy_list[]=$row['extension'];
    }
    return $busy_list;
}

function add_busy_list($extension){
    global $mongodb;
    $mongodb->insert("extension_inprocess",array("extension"=>$extension,"create_time"=>time()));
}

function clear_list(){
	global $mongodb;
	$extension_inprocess=$mongodb->get("extension_inprocess");
	//print_r($extension_inprocess);
	if(is_array($extension_inprocess)&&count($extension_inprocess)>0){
		foreach($extension_inprocess as $extension){
			$mongodb->where(array($extension[0]))->delete("extension_inprocess");
			//$mongodb->where(array("assign"=>$extension[0]))->set(array("assign"=>null))->update("diallistDetail");
		}
	}
}
function free_extension($extension,$dialid){
	global $mongodb;
	$mongodb->where(array("extension"=>$extension))->delete_all("extension_inprocess");
	//$mongodb->where(array("_id"=>new MongoId($dialid)))->set(array("assign"=>null))->update("diallistDetail");
}
function get_dialistDetailbyID($dialid){
	global $mongodb;
	$data = [];
	if($dialid)
		$data=$mongodb->where(array("_id"=>new MongoId($dialid)))->select(array("_diallistId"))->getOne("diallistDetail");
	return $data;
}
function diallist_is_run($Diallistid){
	global $mongodb;
	$data=$mongodb->where(array("_id"=>$Diallistid,"status"=>1))->where_ne("delete",1)->getOne("diallist");
	if($data){
		return true;
	}
	return false;
}
