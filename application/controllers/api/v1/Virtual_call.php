<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Virtual_call extends WFF_Controller {

    private $collection = "Virtual_call";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->config("_mongo");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);
		$this->collection = set_sub_collection($this->collection);
    }

    function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            foreach ($response["data"] as &$doc) {
            	if(!empty( $doc["events"] )) {
            		$events = $doc["events"];
	            	foreach ($events as $index => $event) {
	            		foreach ($event as $key => $value) {
		            		if(in_array($key, ["receivedtime","starttime","createdAt","answertime","endtime","datereceived"])) {
		            			if(!is_numeric($value))
		            				$events[$index][$key] = strtotime($value);
		            		}
	            		}
	            	}
	            	$doc["events"] = $events;
            	}
            	if(!empty( $doc["createdAt"] ) && !is_numeric($doc["createdAt"])) {
            		$doc["createdAt"] = strtotime($doc["createdAt"]);
            	}
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function update($id)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

    function remove_cdr($calluuid)
    {
    	try {
    		$this->mongo_db->switch_db();
	    	$result = $this->mongo_db->where("calluuid", $calluuid)->delete(set_sub_collection("worldfonepbxmanager"));
	    	echo json_encode(array("status" => $result ? 1 : 0));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}