<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Agentstatus_model extends CI_Model {

	private $WFF;
	private $sub;
	private $collection = "Agent_status";
	private $collection_reference = "Agent_status_code";
	private $phoneUnvailableStatusCode = 0;
	private $phoneOncallStatusCode = 2;
	private $time_cache = 60;
	// Status login and softphone available
	private $statusSoftphoneAvailableAfterLogin = 1;
	// Status after call
	private $statusAfterCall = 4;
	// Status softphone change from unvailable to available
	private $statusAfterSoftphoneAvailable = 1;
	// Status ACW
	private $statusACW = 4;

	private $statusBlock = 3;

	private $user_collection = "User";

	private $phoneOfflineSoftphoneStatusArr = ['LOGGEDOFF','UNKNOWN'];

	private $phoneOncallSoftphoneStatusArr = ["ONCALL"];

	private $phoneReadySoftphoneStatusArr = ["IDLE","RINGING"];

	private $acw_duration = 20;

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));

        $this->time_cache = $this->config->item("wff_time_cache");

        $this->sub = set_sub_collection("");
        $this->collection = $this->sub . $this->collection;
        $this->collection_reference = $this->sub . $this->collection_reference;
    }

 	function getOne($select = array(), $unselect = array()) {
 		$time = time();
 		$extension = $this->session->userdata("extension");
 		$data = $this->mongo_db->where(array("extension" => $extension, "endtime" => 0))
 			->where_gt("lastupdate", $time - $this->config->item("sess_time_to_update"))
 			->select($select, $unselect)
 			->order_by(array('starttime' => -1))
 			->getOne($this->collection);
 		if(isset($data["statuscode"])) {
 			$code = $data["statuscode"];
			if(!$status = $this->cache->get($this->sub . "Agent_status_code_{$code}")) {
				$status = $this->mongo_db->where("value", $code)->select([], ["_id","value","updatedAt","updatedBy"])->getOne($this->collection_reference);
				$this->cache->save($this->sub . "Agent_status_code_{$code}", $status, $this->time_cache);
			}
 			$data["status"] = $status;
 		}
 		return $data;
 	}

    function start($data = array(), $list_agent_state = null, $try = 0) 
    {
    	$time = time();
    	$extension = $this->session->userdata("extension");
        $my_session_id = $this->session->userdata("my_session_id");

        // Update previous
    	$cache_name = $my_session_id . "_update_previous_agent_status";
    	if ($this->cache->get($cache_name)) {
        	$this->update_previous($extension);
    		$this->cache->save($cache_name , 1, $this->config->item("sess_time_to_update"));
        }
        
        if(!$list_agent_state) {
        	$this->WFF =& get_instance();
        	$this->WFF->load->model("pbx_model");
        	$responseArr = $this->WFF->pbx_model->list_agent_state($extension);
    	} else $responseArr = $list_agent_state;
		
		$current_agent_state = $responseArr['data'][0];
		// Fix 19/07/2019. Try again if not data, fix error Disconnect 1 time
		if(!$current_agent_state && !$try) {
			$this->start($data, null, 1);
			return;
		}
        //start insert agentstatuslogs
        $default_data = array(
        	"extension" 		=> $extension,
            "statuscode" 		=> $this->phoneUnvailableStatusCode,
            "substatus" 		=> $current_agent_state ? "Unvailable" : "Disconnect",
            "agentstate" 		=> $current_agent_state,
            "starttime" 		=> $time,
            "endtime" 			=> 0,
            "lastupdate" 		=> $time,
            "my_session_ids" 	=> [$my_session_id],
            "note" 				=> $current_agent_state ? "Softphone - Unvailable" : "Can't connect to pbx"
        );

        if( isset($current_agent_state['state']) && !in_array($current_agent_state['state'], $this->phoneOfflineSoftphoneStatusArr) ) {
        	// Trang thai chu dong
        	if($current_agent_state["dnd"] == "1") {
        		$this->_action_queue($extension, $this->statusBlock);
        		$default_data["statuscode"] = $this->statusBlock;
	        	$default_data["substatus"] = "Unknown";
	        	$default_data["note"] = 'Continue Block';
	        	$lastACW = $this->mongo_db->where("statuscode", $this->statusBlock)->order_by(array("starttime" => -1))->getOne($this->collection);
	        	if($lastACW) {
	        		$default_data["substatus"] = $lastACW["substatus"];
	        	}
        	} else {
        		$queues = $current_agent_state["queues"]["queue"];
        		$checkAllAvailableQueues = FALSE;
        		if($queues) {
        			foreach ($queues as $doc) {
        				$paused = (int) $doc["queuememberpaused"];
        				if(!$paused) 
        				{
        					$checkAllAvailableQueues = TRUE;
        				}
        			}
        		} else $checkAllAvailableQueues = TRUE;
        		if($checkAllAvailableQueues) {
        			$default_data["statuscode"] = $this->statusSoftphoneAvailableAfterLogin;
		        	$default_data["substatus"] = "";
        		} else {
        			if( !in_array($current_agent_state['state'], $this->phoneOncallSoftphoneStatusArr) ) {
	        			$default_data["statuscode"] = $this->statusACW;
			        	$default_data["substatus"] = "Continue ACW";
		        	}
        		}
        		$default_data["note"] = 'Softphone - Available';
        	}
        }

        if(isset($data["note"]) && $data["note"] == "Login") {
        	$data["note"] = str_replace("Softphone", "Login", $default_data["note"]);
        }

        $insert_data = array_merge($default_data, $data);
        $result = $this->mongo_db->insert($this->collection, $insert_data);
        if($result) {
        	$user_collection = $this->sub . $this->user_collection;
    		$this->load->library("mongo_private");
        	$this->mongo_private->where(array("extension" => $extension))
        	->update($user_collection, array(
        		'$set' => array(
        			"statuscode"=> $insert_data["statuscode"],
        			"substatus"	=> $insert_data["substatus"]
        		)
        	));
        }
    	return $result;
    }

    function end($data = array())
    {
    	$time = time();
    	$update_data = array_merge($data, array('endtime'=> $time));
    	$extension = $this->session->userdata("extension");
    	$this->mongo_db->where(array('extension' => $extension, "endtime" => 0))
                ->set($update_data)
                ->update_all($this->collection);
    }

    function update($data = array()) 
    {
    	$extension = $this->session->userdata("extension");
        $time = time();
        $this->WFF =& get_instance();
		$this->WFF->load->model("pbx_model");
		$responseArr = $this->WFF->pbx_model->list_agent_state($extension);
		$current_agent_state = $responseArr['data'][0];

		$default_data = array(
			"agentstate" => $current_agent_state,
			"lastupdate" => $time
		);
		$check_data = $this->getOne(["statuscode","substatus","starttime"]);
		if(!$check_data) $this->start(array("note" => "Start after login"), $responseArr);

		if($check_data && $current_agent_state) {
			if($check_data["statuscode"] == $this->phoneUnvailableStatusCode) {
				// User login o trang thai unvailable sau do softphone available
		        if(!in_array($current_agent_state['state'], $this->phoneOfflineSoftphoneStatusArr)) 
		        {
		        	// Softphone available
		        	$data["endnote"] = 'Softphone available';
		        	$update_data = array_merge($default_data, $data);
		        	$this->end($update_data);
		        	$this->start([], $responseArr);
		        	return;
		        	// Return not update
		        }
			} else {
				if(in_array($current_agent_state['state'], $this->phoneOfflineSoftphoneStatusArr))
				{
					// Softphone unvailable
					$data["endnote"] = 'Softphone unvailable';
		        	$update_data = array_merge($default_data, $data);
		        	$this->end($update_data);
		        	$this->start([], $responseArr);
		        	return;
		        	// Return not update
				}
			}
			// Set phone on call ONCALL, RINGING
			if(in_array($current_agent_state['state'], $this->phoneOncallSoftphoneStatusArr) && $check_data["statuscode"]!=$this->phoneOncallStatusCode)
	        {
	        	
	            // Softphone oncall
	        	$data["endnote"] = 'Softphone oncall';
	        	$update_data = array_merge($default_data, $data);
	        	$this->end($update_data);
	        	$this->start(array(
	        		"statuscode" => $this->phoneOncallStatusCode
	        	), $responseArr);
	        	return;
	        	// Return not update
	        } elseif(!in_array($current_agent_state['state'], $this->phoneOncallSoftphoneStatusArr) && $check_data["statuscode"]==$this->phoneOncallStatusCode) {
	        	// Softphone available
	        	$data["endnote"] = 'Softphone available';
	        	$update_data = array_merge($default_data, $data);

	        	// Change 03/07/2019, busy after call
		        $queueData 	= $this->_action_queue($extension, $this->statusAfterCall);
		        $note 		= $queueData["note"];
		        $responseArr= $queueData["responseArr"];

	        	$this->end($update_data);
	        	$this->start(array(
	        		"statuscode" 	=> $this->statusAfterCall,
	        		"substatus"		=> "ACW",
	        		"note" 			=> $note
	        	), $responseArr);
	        	return;
	        	// Return not update
	        } elseif(in_array($current_agent_state['state'], $this->phoneReadySoftphoneStatusArr) && $check_data["statuscode"]==$this->statusAfterCall && strpos($check_data["substatus"], "ACW") === 0) {
	        	// Add 17/12/2019. Auto change to available after max_acw_duration seconds
	        	$type = getCT();
		        $this->load->library("mongo_private");
		        $config_type = $this->mongo_private->where(["type"=>$type])->getOne("ConfigType");
		        $max_acw_duration = !empty($config_type["acw_duration"]) ? $config_type["acw_duration"] : $this->acw_duration;

	        	$starttime = isset($check_data["starttime"]) ? $check_data["starttime"] : 0;
        		$acw_seconds = $time - $starttime;
	        	if($acw_seconds < $max_acw_duration) {
	        		$data["substatus"] = "ACW ".($acw_seconds + 1)."s";
	        	} else {
	        		// Softphone available
		        	$data["endnote"] = 'Auto available ACW';
		        	$update_data = array_merge($default_data, $data);

		        	$queueData 	= $this->_action_queue($extension, 1);
			        $note 		= $queueData["note"];
			        $responseArr= $queueData["responseArr"];

		        	$this->end($update_data);
		        	$this->start(array(
		        		"statuscode" 	=> 1,
		        		"substatus"		=> "",
		        		"note" 			=> $note
		        	), $responseArr);
		        	return;
		        	// Return not update
	        	}
	        }
	        // Else continue update
		} else {
			// Add 20/03/2019. TH ko the ket noi pbx
			$substatus = 'Disconnect';
	        if($check_data["statuscode"]!=$this->phoneUnvailableStatusCode && !$current_agent_state) {
	        	$data["endnote"] = $substatus;
	        	$update_data = array_merge($default_data, $data);
	        	$this->end($update_data);
	        	$this->start(array(), $responseArr);
	        	return;
	        }
		}
		if($check_data["statuscode"]==$this->phoneOncallStatusCode && in_array($check_data["substatus"], ["", "Unvailable"])) {
			$data["substatus"] = "";
			// Update substatus on call
			$this->WFF->load->model("call_model");
	        $currentCall = $this->WFF->call_model->get_current_call($extension);
	        if($currentCall) {
	        	$direction = $currentCall["direction"] == "outbound" ? "Out" : "In";
	        	$data["substatus"] = $direction . " " . $currentCall["customernumber"];
	        }
		}

        $update_data = array_merge($default_data, $data);
        if($currentStatus = $this->getOne()) {
        	$my_session_id = $this->session->userdata("my_session_id");
			$this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectId($currentStatus["id"])))
		                ->set($update_data)
		                ->addtoset("my_session_ids", $my_session_id)
		                ->update($this->collection);
	    }
    }

    private function update_previous($extension)
    {
    	// TH: User tat trinh duyet, khong dang xuat
    	$time = time();
    	$data = $this->mongo_db
    	->where(array("endtime" => 0, "extension" => $extension))
    	->select(["_id", "lastupdate"])
    	->get($this->collection);
    	if($data) {
	    	foreach ($data as $doc) {
	    		if( $time > $doc["lastupdate"] + $this->config->item("sess_time_to_update")) 
	            {
	                // Qua thoi han session update
		    		$this->mongo_db->where(array("_id" => new MongoDB\BSON\ObjectId($doc["id"])))
		    		->set(array("endtime" => $doc["lastupdate"], "endnote" => "No connect too long"))
		    		->update($this->collection);
		    	}
	    	}
    	}

    	// Move to log
    	$data = $this->mongo_db
    	->where(array(
    		"endtime" => array('$ne' => 0, '$lt' => strtotime('today midnight'))
    	))
    	->limit(100)
    	->get($this->collection);
    	if( $data ) {
    		foreach ($data as $doc) {
	    		$id = $doc["id"];
	    		unset($doc["id"]);
	    		$this->mongo_db->insert($this->collection . "_log", $doc);
	    		$this->mongo_db->where_id($id)->delete($this->collection);
    		}
    	}
    }

    function change($data = array())
    {
		if(!isset($data["agentState"])) {
			throw new Exception("Need state");
		}
		$status = (int) $data["agentState"];
		
		$extension = $this->session->userdata("extension");

		//change state process
        $queueData 	= $this->_action_queue($extension, $status);
        $note 		= $queueData["note"];
        $responseArr= $queueData["responseArr"];

        //log operation
		$substate = "";
		if(!empty($data["subState"])) {
			$substate = $data["subState"];
		} else {
			$agent_status_code = $this->mongo_db->where("value", $status)->getOne($this->collection_reference);
			$substate = !empty($agent_status_code["sub"]) ? $agent_status_code["sub"][0] : "";
		}

		// End current log
    	$this->end(array("endnote" => "User change status"));
    	// Start new log
    	return $this->start(array(
    		"statuscode"	=> $status,
    		"substatus" 	=> $substate,
    		"note" 			=> $note
    	));
    }

    private function _action_queue($extension, $agent_state)
    {
        $note		= '';
    	
    	$this->WFF =& get_instance();
    	$this->WFF->load->model("pbx_model");
		$responseArr = $this->WFF->pbx_model->list_agent_state($extension);
		
		switch($agent_state)
		{
			case 1:
				if($responseArr['data'][0]['queues']['queue']) {
					for($tq = 0; $tq < count($responseArr['data'][0]['queues']['queue']); $tq++) {
						$queue_name = $responseArr['data'][0]['queues']['queue'][$tq]['queuename'];
	            		$this->WFF->pbx_model->unpause_queue_member($queue_name, $extension, 1);
					}
				} else {
					$this->WFF->pbx_model->unpause_queue_member("", $extension, 1);
				}
				$note="User Action - Unpause queue";
				break;
			case 3:
				if($responseArr['data'][0]['queues']['queue']) {
					for($tq = 0; $tq < count($responseArr['data'][0]['queues']['queue']); $tq++) {
						$queue_name = $responseArr['data'][0]['queues']['queue'][$tq]['queuename'];
	            		$this->WFF->pbx_model->pause_queue_member($queue_name, $extension, 1);
					}
				} else {
					$this->WFF->pbx_model->pause_queue_member("", $extension, 1);
				}
                $note="User Action - Pause queue";
				break;
			case 4:
				if($responseArr['data'][0]['queues']['queue']) {
					for($tq = 0; $tq < count($responseArr['data'][0]['queues']['queue']); $tq++) {
						$queue_name = $responseArr['data'][0]['queues']['queue'][$tq]['queuename'];
	            		$this->WFF->pbx_model->pause_queue_member($queue_name, $extension, 0);
					}
				}
                $note="User Action - Pause queue";
				break;

			default:
				// Do nothing
				break;
		}
		return array("note" => $note, "responseArr" => $responseArr);
    }

    private function _agent_state($extension)
    {
    	$this->WFF =& get_instance();
    	$this->WFF->load->model("pbx_model");
		$responseArr = $this->WFF->pbx_model->list_agent_state($extension);
    	return $responseArr;
    }

    function get_current_by_extension($extension)
    {
    	return $this->mongo_db->where(array("extension" => $extension, "endtime" => 0))
    		->where_gt("lastupdate", time() - 60)
    		->order_by(array("starttime" => -1))
    		->getOne($this->collection);
    }

    function get_today_by_extension($extension)
    {
        $aggregate = array(
        	array('$match' => array(
	        		"extension"	=> $extension,
	        		"starttime"	=> array('$gte' => strtotime('today midnight')),
	        		"lastupdate"=> array('$gt'	=> strtotime('today midnight'))
	        	)
	    	),
	    	array('$sort' => array("starttime" => 1, "lastupdate" => 1)),
	    	array('$group' => array(
	        		"_id" 			=> array(
	        			"extension"		=> '$extension',
	        			"statuscode"	=> '$statuscode'
	        		),
	        		"last_substatus"=> array('$last' => '$substatus'),
	        		"last_starttime"=> array('$last' => '$starttime'),
	        		"last_endtime"	=> array('$last' => '$endtime'),
	        		"last_update"	=> array('$last' => '$lastupdate'),
	        		"total_time"	=> array('$sum' => array('$subtract' => ['$lastupdate', '$starttime']))
	        	)
	    	),
	    	array('$project' => array(
	 				"statuscode"		=> '$_id.statuscode',
	 				"_id"				=> 0,
	 				"last_substatus"	=> 1,
	 				"last_starttime"	=> 1,
	 				"last_endtime"		=> 1,
	 				"last_update"		=> 1,
	 				"total_time"		=> 1
	 			)
	 		),
	 		array('$sort' => array("statuscode" => 1)),
	    	array('$project' => array(
	 				"statuscode"					=> 1,
	 				"last_substatus"				=> 1,
	 				"last_starttime"				=> 1,
	 				"last_endtime"					=> 1,
	 				"last_update"					=> 1,
	 				"total_time"					=> 1,
	 			)
	 		)
        );
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $aggregate);
        foreach ($data as &$doc) {
        	$code = isset($doc["value"]) ? $doc["value"] : "";
        	$cache_name = $this->collection_reference . "_{$code}";
			if(!$status = $this->cache->get($cache_name)) {
				$status = $this->mongo_db->where("value", $code)->select([], ["_id","value","updatedAt","updatedBy"])->getOne($this->collection_reference);
				$this->cache->save($cache_name, $status, $this->time_cache);
			}
			$doc["statustext"] = isset($status["text"]) ? $status["text"] : "";
        }
        return $data;
    }

    function start_from_other($data = array(), $list_agent_state = null, $extension) 
    {
    	$time = time();
    	$user_collection = set_sub_collection($this->user_collection);
    	$this->load->library("mongo_private");
    	$user = $this->mongo_private->where(array("extension" => $extension))->getOne($user_collection);
    	if(!isset($user["current_my_session_id"])) throw new Exception("No current my session id exists");
        $my_session_id = $user["current_my_session_id"];
        
        if(!$list_agent_state) {
        	$this->WFF =& get_instance();
        	$this->WFF->load->model("pbx_model");
        	$responseArr = $this->WFF->pbx_model->list_agent_state($extension);
    	} else $responseArr = $list_agent_state;
		
		$current_agent_state = $responseArr['data'][0];
        //start insert agentstatuslogs
        $default_data = array(
        	"extension" 		=> $extension,
            "statuscode" 		=> $this->phoneUnvailableStatusCode,
            "substatus" 		=> "",
            "agentstate" 		=> $current_agent_state,
            "starttime" 		=> $time,
            "endtime" 			=> 0,
            "lastupdate" 		=> $time,
            "my_session_ids" 	=> [$my_session_id],
            "note" 				=> "Softphone - Unvailable"
        );

        $insert_data = array_merge($default_data, $data);
        
    	$result = $this->mongo_db->insert($this->collection, $insert_data);
    	if($result) {
        	$user_collection = $this->sub . $this->user_collection;
    		$this->load->library("mongo_private");
        	$this->mongo_private->where(array("extension" => $extension))
        	->update($user_collection, array(
        		'$set' => array(
        			"statuscode"=> $insert_data["statuscode"],
        			"substatus"	=> $insert_data["substatus"]
        		)
        	));
        }
        return $result;
    }

    function end_from_other($data = array(), $extension)
    {
    	$time = time();
    	$update_data = array_merge($data, array('endtime'=> $time));
    	$this->mongo_db->where(array('extension' => $extension, "endtime" => 0))
                ->set($update_data)
                ->update_all($this->collection);
    }

    function change_from_other($extension, $data = array())
    {
		if(!isset($data["agentState"])) {
			throw new Exception("Need state");
		}
		$status = (int) $data["agentState"];

		$change_extension = $this->session->userdata("extension");

		//change state process
        $queueData 	= $this->_action_queue($extension, $status);
        $note 		= $queueData["note"];
        $responseArr= $queueData["responseArr"];
        $time 		= time();
        $current_agent_state = $responseArr['data'][0];

        if( in_array($current_agent_state['state'], ['LOGGEDOFF','UNKNOWN']) ) {
        	throw new Exception("Can't change status because softphone not available");
        }

        //log operation
		$substate = "";
		if(!empty($data["subState"])) {
			$substate = $data["subState"];
		} else {
			$agent_status_code = $this->mongo_db->where("value", $status)->getOne($this->collection_reference);
			$substate = !empty($agent_status_code["sub"]) ? $agent_status_code["sub"][0] : "";
		}

		// End current log
    	$this->end_from_other(array("endnote" => "{$change_extension} change"), $extension);
    	// Start new log
    	return $this->start_from_other(array(
    		"statuscode"	=> $status,
    		"substatus" 	=> $substate,
    		"note" 			=> $note
    	), $responseArr, $extension);
    }
}