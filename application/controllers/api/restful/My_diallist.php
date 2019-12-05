<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class My_diallist extends WFF_Controller {

	private $collection = "Diallist";
	private $sub_collection = "Diallist_detail";
	private $jsondata_collection = "Jsondata";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->sub_collection = set_sub_collection($this->sub_collection);
	}

	function read()
	{
		$this->load->library("crud");
		$request = json_decode($this->input->get("q"), TRUE);
		$extension = $this->session->userdata("extension");

		$response = $this->crud->read($this->collection, $request, [], ["members" => $extension]);
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
        }
        // Result
		echo json_encode($response);
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		echo json_encode($response);
	}
}