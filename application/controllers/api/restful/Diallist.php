<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallist extends CI_Controller {

	private $collection = "Diallist";
	private $sub_collection = "Diallist_detail";
	private $jsondata_collection = "Jsondata";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
		$this->sub_collection = $this->sub . $this->sub_collection;
	}

	function read()
	{
		$this->load->library("crud");
		$request = json_decode($this->input->get("q"), TRUE);

		$response = $this->crud->read($this->collection, $request);
        // Change foreign_key
        $this->load->library("mongo_private");
        $jsondata_collection = set_sub_collection($this->jsondata_collection);
        $dialTypeOption = $this->mongo_private->where(array("tags" => ["Diallist", "type"]))->getOne($jsondata_collection);
        $dialTypeToName = array();
        if($dialTypeOption) {
	        foreach ($dialTypeOption["data"] as $row) {
	        	$dialTypeToName[$row["value"]] = $row["text"];
	        }
    	}
        $dialModeOption = $this->mongo_private->where(array("tags" => ["Diallist", "mode"]))->getOne($jsondata_collection);
        $dialModeToName = array();
        if($dialModeOption) {
	        foreach ($dialModeOption["data"] as $row) {
	        	$dialModeToName[$row["value"]] = $row["text"];
	        }
	    }
        foreach ($response["data"] as &$doc) {
        	if(isset($doc["type"])) $doc["type"] = isset($dialTypeToName[$doc["type"]]) ? $dialTypeToName[$doc["type"]] : $doc["type"];
        	$doc["count_detail"] = $this->mongo_db->where_object_id("diallist_id", $doc["id"])->count($this->sub_collection);
        	$doc["assigns"] = $this->mongo_db->where_object_id("diallist_id", $doc["id"])->distinct($this->sub_collection, "assign");
        	$this->mongo_db->where_id($doc["id"])
        	->set("count_detail", $doc["count_detail"])
        	->set("assigns", $doc["assigns"])->update($this->collection);
        }
        // Result
		echo json_encode($response);
	}

	function detail($id)
	{
		$this->load->model("language_model");
		$response = $this->crud->where_id($id)->getOne($this->collection);
		$response = $this->language_model->translate($response);
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["createdBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["updatedBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection);
		if($result) {
			$this->mongo_db->where_object_id("diallist_id", $id)->delete_all($this->sub_collection);
			$this->mongo_db->where("diallist_id", $id)->delete_all($this->sub . "Dial_queue");
		}
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}

}