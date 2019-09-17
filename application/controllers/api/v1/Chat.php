<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Chat extends CI_Controller { 

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$this->load->library("session");
		$this->load->model("language_model");
		$this->sub = set_sub_collection();
	}

    function change_status_chat() {
		$request = json_decode(file_get_contents('php://input'), TRUE);
		
		try {
			$this->load->model("chatstatus_model");
			$result = $this->chatstatus_model->change($request);
			if(!$result) throw new Exception("@Change not success@");
			$message = $this->language_model->translate("Chat @change to status@ @".($request["statuscode"] ? "Ready":"Busy") . "@", "NOTIFICATION");
			$message = str_replace("@", "", $message);
			echo json_encode(array("status" => 1, "message" => $message));			
		} catch (Exception $e) {
			echo json_encode(array('status' => 0, "message" => $e->getMessage()));
		}
    }

    function users() {
    	$request = json_decode($this->input->get("q"), TRUE);
    	$_db = $this->config->item("_mongo_db");
    	$this->load->library("crud");
    	$this->crud->select_db($_db);
    	$match = array(
    		"lastpingtime" => array('$gt' => time() - 1000)
    	);
    	$data = $this->crud->read($this->sub . "User", $request, ["extension", "agentname", "avatar", "chat_statuscode"], $match);
    	$this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    function rooms() {
    	$this->output->set_content_type('application/json');
    	try {
    		$extension = $this->session->userdata("extension");
    		$request = json_decode($this->input->get("q"), TRUE);
    		$this->load->library("crud");
    		$response = $this->crud->read("Room", $request, [], ["members" => $extension]);
    		$todayMidnight = strtotime("today midnight");
    		foreach ($response["data"] as &$doc) {
    			if(isset($doc["last_time"])) {
    				$last_timestamp = $doc["last_time"]->toDateTime()->getTimestamp();
    				$doc["last_time"] = date($last_timestamp > $todayMidnight ? "H:i" : "d/m/y", $last_timestamp);
    			}
    			$doc["unread_count"] = $this->mongo_db->where(array('user_id' => array('$ne' => $extension), 'read.extension' => array('$ne' => $extension)))->count("Message_" . $doc["id"]);
    		}
    		$this->output->set_output(json_encode($response));
    	} catch (Exception $e) {
			$this->output->set_output(json_encode(array('status' => 0, "message" => $e->getMessage())));
		}
    }

    function createRoom() {
    	try {
    		$request = json_decode(file_get_contents('php://input'), TRUE);
    		if(empty($request["members"]) || !is_array($request["members"])) throw new Exception("Error Processing Request", 1);
    		
    		sort($request["members"]);
    		if(isset($request["name"])) {
    			$request["createdAt"] = $this->mongo_db->date();
    			$result = $this->mongo_db->insert("Room", $request);
    		} else {
	    		// Check exists
	    		$result = $this->mongo_db->where("members", $request["members"])->getOne("Room");
	    		if( !$result ) {
		    		$request["createdAt"] = $this->mongo_db->date();
		    		$result = $this->mongo_db->insert("Room", $request);
	    		}
    		}
    		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
    	} catch (Exception $e) {
			echo json_encode(array('status' => 0, "message" => $e->getMessage()));
		}
    }

    function editRoom($id = "") {
    	try {
    		$request = json_decode(file_get_contents('php://input'), TRUE);
    		if(!$id || empty($request["members"]) || !is_array($request["members"])) throw new Exception("Error Processing Request", 1);
    		
    		sort($request["members"]);
    		$request["updatedAt"] = $this->mongo_db->date();
    		$result = $this->mongo_db->where_id($id)->set($request)->update("Room");
    		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
    	} catch (Exception $e) {
			echo json_encode(array('status' => 0, "message" => $e->getMessage()));
		}
    }

    function readMessage()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$room_id = isset($request["room_id"]) ? $request["room_id"] : "";
			$collection = $room_id ? "Message_". $room_id : "Message";
			$this->load->library("crud");
			$date = $this->mongo_db->date();
			$extension = $this->session->userdata("extension");
			$response = $this->crud->read($collection, $request);
			foreach ($response["data"] as &$doc) {
				$doc["time"] = date("c", $doc["time"]->toDateTime()->getTimestamp());
				$this->crud->where_id($doc["id"])->update($collection, 
					['$push' => ['read' => ["extension" => $extension, "createdAt" => $date]]]
				);
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function readMessageId()
	{
		try {
			$request = json_decode(file_get_contents('php://input'), TRUE);
			if(empty($request["message_id"])) throw new Exception("Error Processing Request", 1);
			$room_id = isset($request["room_id"]) ? $request["room_id"] : "";
			$collection = $room_id ? "Message_". $room_id : "Message";
			$extension = $this->session->userdata("extension");
			$this->load->library("crud");
			$date = $this->mongo_db->date();
			$this->crud->where_id($request["message_id"])->update($collection, 
				['$push' => ['read' => ["extension" => $extension, "createdAt" => $date]]]
			);
			echo json_encode(array("status" => 1));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}