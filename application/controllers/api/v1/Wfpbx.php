<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Wfpbx extends WFF_Controller {

	private $sub = "";
	private $cdr_collection = "worldfonepbxmanager";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$this->load->model("pbx_model");
		$this->load->model("language_model");
		$this->sub = set_sub_collection();
	}

	public function change_status() {
		$request = json_decode(file_get_contents('php://input'), TRUE);
		$this->load->model("agentstatus_model");
		try {
			$my_session_id  = $this->session->userdata('my_session_id');
    		$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
    		$cache_id = $my_session_id . "_update_status";
    		$try_count = 0;
    		$changed = FALSE;
    		while(!$changed && $try_count < 10) {
    			$try_count++;
    			if(!$this->cache->get($cache_id)) {
    				$changed = TRUE;
	    			$this->cache->save($cache_id, 1, 5);
					$result = $this->agentstatus_model->change($request);
					$this->cache->delete($cache_id);
					if(!$result) throw new Exception("@Change not success@");
				} else usleep(500000);
    		}
    		if(!$changed) throw new Exception("@Unable to change at current time@");
    		
			$current_status = $this->agentstatus_model->getOne(["statuscode"]);
			$message = !empty($current_status["status"]) ? $this->language_model->translate("@Change to status@ @".$current_status["status"]["text"]."@", "NOTIFICATION") : "";
			$message = str_replace("@", "", $message);
			echo json_encode(array("status" => 1, "message" => $message));			
		} catch (Exception $e) {
			$message = $this->language_model->translate($e->getMessage(), "NOTIFICATION");
			echo json_encode(array('status' => 0, "message" => $message));
		}
    }

	function change_one_queue()
	{
		try {
			$request = json_decode(file_get_contents('php://input'), TRUE);
			if(!isset($request["queuename"])) throw new Exception("Queuename is empty!");
			// Check status code
			$this->load->model("agentstatus_model");
			$agentstatus = $this->agentstatus_model->getOne(["statuscode"]);
			if($agentstatus["statuscode"] != 1 && $agentstatus["statuscode"] != 2) throw new Exception("@Your status is not available@");
			//
			$queuename = $request["queuename"];
			$action = !empty($request["pause"]) ? "pause" : "unpause";
			$method = $action . "_queue_member";
			$extension = $this->session->userdata("extension");
			$response = $this->pbx_model->$method($queuename, $extension, false);
			if(empty($response['status'])) throw new Exception("No success.");
			$message = $this->language_model->translate("@{$action}@ @success@!", "NOTIFICATION");
			echo json_encode(array("status" => 1, "message" => ucfirst($message)));
		} catch (Exception $e) {
			$message = $this->language_model->translate($e->getMessage(), "NOTIFICATION");
			echo json_encode(array("status" => 0, "message" => $message));
		}
	}

	function change_queue_member($action = "add")
	{
		try {
			$request = json_decode(file_get_contents('php://input'), TRUE);
			if(!isset($request["queuename"])) throw new Exception("Queuename is empty!");
			if(!isset($request["extension"])) throw new Exception("Extension is empty!");
			
			$method = $action . "_queue_member";
			$queuename = $request["queuename"];
			$extension = $request["extension"];
			$response = $this->pbx_model->$method($queuename, $extension);
			if(empty($response['status'])) throw new Exception("No success. Status pbx empty");
			if($response['status'] != "200") throw new Exception("No success. Reason: ". json_encode($response));
			$actions = $action == "add" ? ["ADDED"] : ["REMOVED","NOTINQUEUE"];
			if(!in_array($response['data']['aqmstatus'], $actions)) 
				throw new Exception("No success. Reason: ". $response['data']['aqmstatus']);
			$action = ucfirst($action);
			$message = $this->language_model->translate("@Success@", "NOTIFICATION");
			$this->load->model("afterlogin_model");
			$this->afterlogin_model->update_group();
			echo json_encode(array("status" => 1, "message" => $message. json_encode($response)));
		} catch (Exception $e) {
			$message = $this->language_model->translate($e->getMessage(), "NOTIFICATION");
			echo json_encode(array("status" => 0, "message" => $message));
		}
	}

	function makeCall() { 

		$extension 	= $this->session->userdata("extension");
		$phone		= $this->input->get("phone");
		$dialid 	= $this->input->get("dialid");
		$type 		= $this->input->get("type");

		try {        
			$this->load->model("agentstatus_model");
			$agentstatus = $this->agentstatus_model->getOne(["statuscode"]);
			if( in_array($agentstatus["statuscode"], [1,4]) ) {
				// Check để remove realtime
				$this->load->library("mongo_db");
				$this->mongo_db->where(array("userextension" => $extension))
				->delete_all($this->sub . $this->cdr_collection . "_realtime");
			}
			if( !in_array($agentstatus["statuscode"], [1,2,4]) ) throw new Exception("Your status is not available");

			$responseArr = $this->pbx_model->make_call_2($extension, $phone, $dialid, $type);
			if($responseArr != 200) throw new Exception("Call error");
			$message = $this->language_model->translate("@Call success@", "NOTIFICATION");
			echo json_encode(array("status" => 1, "message" => $message));
		} catch(Exception $e) {
			$message = $this->language_model->translate("@".$e->getMessage()."@", "NOTIFICATION");
			echo json_encode(array("status" => 0, "message" => $message));
		}
    }

    function hangup() {
    	try {
    		$request = json_decode(file_get_contents('php://input'), TRUE);
    		$calluuid = !empty($request["calluuid"]) ? $request["calluuid"] : "";
    		$responseArr = $this->pbx_model->hangup($calluuid);
    		if($responseArr != 200) throw new Exception("@Hangup@ @error@: " . $responseArr);
    		$message = $this->language_model->translate("@Hangup@ @success@", "NOTIFICATION");
    		echo json_encode(array("status" => 1, "message" => $message));
    	} catch (Exception $e) {
    		$message = $this->language_model->translate($e->getMessage(), "NOTIFICATION");
    		echo json_encode(array("status" => 0, "message" => $message));
    	}
    }

    function transfer() {
    	try {
    		$request = json_decode(file_get_contents('php://input'), TRUE);
    		$calluuid = !empty($request["calluuid"]) ? $request["calluuid"] : "";
    		$extension = !empty($request["extension"]) ? $request["extension"] : "";
    		$responseArr = $this->pbx_model->transfer($calluuid, $extension);
    		if($responseArr != 200) throw new Exception("@Transfer@ @error@: " . $responseArr);
    		$message = $this->language_model->translate("@Transfer@ @success@", "NOTIFICATION");
    		echo json_encode(array("status" => 1, "message" => $message));
    	} catch (Exception $e) {
    		$message = $this->language_model->translate($e->getMessage(), "NOTIFICATION");
    		echo json_encode(array("status" => 0, "message" => $message));
    	}
    }

    function call_before_week()
    {
    	$extension = $this->input->get("extension");
    	$week = (int) $this->input->get("week");
    	$end = strtotime('monday next week midnight');
    	$end += 604800 * $week;
    	$start = $end - 604800;
    	$durations = interval_duration($start, $end, 1440);
    	$response = array();
    	$collection = set_sub_collection("worldfonepbxmanager");
    	$match = array();
    	if($extension) $match["userextension"] = $extension;
    	foreach ($durations as $duration) {
    		$match["starttime"] = array('$gte' => $duration["start"], '$lt' => $duration["end"]);
    		$data = $this->mongo_db->aggregate_pipeline($collection, 
	    		array(
	    			array('$match' => $match),
		    		array('$group' => array(
		    				'_id' => '$direction',
		    				"total" => array('$sum' => 1)
		    			)
		    		)
	    		)
	    	);
	    	$day_data = array();
	    	$day_data["start"] = $duration["start"];
	    	$day_data["date"] = date('d/m/Y', $duration["start"]);
	    	foreach ($data as $doc) {
	    		$day_data[$doc["_id"]] = $doc["total"];
	    	}
	    	$response[] = $day_data;
    	}
    	echo json_encode($response);
    }

    function updateQueueMembers() 
    {
    	try {
			$this->load->model("afterlogin_model");
			$this->afterlogin_model->update_group();
			echo json_encode(array("status" => 1, "message" => "Success"));
		} catch (Exception $e) {
			$message = $this->language_model->translate($e->getMessage(), "NOTIFICATION");
			echo json_encode(array("status" => 0, "message" => $message));
		}
    }
}