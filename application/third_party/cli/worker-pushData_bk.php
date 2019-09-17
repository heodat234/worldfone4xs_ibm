<?php

/*
 * Copyright © 2014 South Telecom
 */

require_once dirname(__DIR__) . '/beanstalk/autoload.php';
require_once 'mongodriver.php';
$mongodb= new MyMongoDriver('worldfone4x');
$queue = new Pheanstalk\Pheanstalk('127.0.0.1');
$config_info=$mongodb->where(array("callcenter_type"=>"co"))->getOne('wff_config');
while(1){
handle_data();
sleep(10);
}
//var_dump($queue);
function handle_data(){
    global $mongodb;
    global $queue;
    global $config_info;
    $secret=$config_info['secret_key'];
    $available_list= get_Available_List();
    $busy_list= get_busy_list();
    $list_dialist=get_list_diallist();
    $free_list=array();
    $now = new DateTime();
    $pingtime=$now->getTimestamp();
	//print_r($list_dialist);
   // print_r() "available list";
	foreach($list_dialist as $dialist){
		$group=get_group_by_id($dialist['group']);
	//	print_r($group);
	//	print_r($available_list);
	//	print_r($busy_list);
		
		foreach($group['members'] as $extension){
			//echo $extension;
			if(in_array($extension,$available_list)&&!in_array($extension, $busy_list)){
				 $dialist=get_DialistDetail_by_id($dialist['_id']);
				// print_r($dialist);die();
				 if(is_array($dialist)&& count($dialist)>0&&(trim($dialist['phone'][0])!="")){
                     //đẩy vào queue, insert busy list
                    $callJob = new stdClass();
                    $callJob->secret = $secret;
                    $callJob->callernum = $extension;
                    $callJob->destnum=$dialist['phone'][0];                           
                    $callJob->dialid=$dialist['_id']->{'$id'};
                    $callJob->startTimestamp = $pingtime +5;
                    echo json_encode($callJob) . "<br>";
                    $queue->useTube("calljobs")->put(json_encode($callJob));
                    assign_dialist($dialist['_id'],$extension);
					set_diallis_controler($extension);
                    add_busy_list($extension);
                }else{
					reset_dialist($dialist_id);
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
    $agents_available=$mongodb->where(array('endtime'=>""))->where_in("statuscode", array("1",(int)1))->where_gt("lastupdate",$time_limit)->get('agentstatuslogs_realtime');
	//print_r($agents_available);
    $agent_idle=$mongodb->where(array('status'=>"IDLE"))->get('extension_status');
	//print_r($agent_idle);
    $free_agents=array();
    foreach($agents_available as $available){
        foreach($agent_idle as $idle){
            if($available['userextension']==$idle['extension']){
                $free_agents[]=$available['userextension'];
            }
        }
    }
	//print_r($free_agents);die();
    return $free_agents;
 
}
function get_list_diallist(){
    global $mongodb;
    $data= $mongodb->select(array("group","maxTryCount"))->where(array("dialType"=>"3","status"=>1))->where_ne("delete",1)->get("diallist");
    return $data;
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
function get_DialistDetail_by_id($dialist_id){
    global $mongodb;
    $data= $mongodb->where(array("_diallistId"=>$dialist_id))->where_in("assign",array("",null))->getOne("diallistDetail");
    return $data;
}
function assign_dialist($dialistDetailId,$extension){
    global $mongodb;
    $mongodb->where(array("_id"=>$dialistDetailId))->set(array("assign"=>$extension))->update("diallistDetail");
}
function set_diallis_controler($extension){
    global $mongodb;
    $mongodb->where(array("userextension"=>$extension))->set(array("calltype"=>3))->update("diallistController");
}
function add_busy_list($extension){
    global $mongodb;
    $mongodb->insert("extension_inprocess",array("extension"=>$extension,"create_time"=>time()));
}

function reset_dialist($dialistID){
    global $mongodb;
	$data=$mongodb->where(array("_diallistId"=>$dialistID))->where_ne("assign",null)->get("diallistDetail");
	if(is_array($data)&&(count($data)>0)){		
		foreach($data as $row){
			$update_data=array(
				"assign"=>null,
				"tryCount"=>0,
				"tryNumber"=>0
			);
			$mongodb->where(array("_id"=>$row['_id']))->set($update_data)->update("diallistDetail");
		}
    }
}