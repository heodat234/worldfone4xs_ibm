<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class My_diallist extends WFF_Controller {

	private $collection = "Diallist";
	private $sub_collection = "Diallist_detail";

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
		$request = $_REQUEST;;
		$extension = $this->session->userdata("extension");
		$model = $this->crud->build_model($this->collection);
        // Kendo to aggregate
        $this->load->library("kendo_aggregate", $model);
        $lookup = array('$lookup' => array(
        		"from" => $this->sub_collection,
			    "localField" => "_id",
			    "foreignField" => "diallist_id",
			    "as" => "diallist_detail"
        	)
    	);
    	$project = array(
    		'$project' => array(
    			'name'						=> 1,
    			'type'						=> 1,
    			'mode'						=> 1,
    			"assigns"	=> array('$reduce' => array(
		            "input"	=> '$diallist_detail',
		            "initialValue"	=> [],
		            "in"	=> array('$setUnion' => array('$$value', array('$split' => [ '$$this.assign', "@" ])))
		        ))
    		)
    	);
    	$match = array(
    		'$match' => array(
    			"assigns"	=> $extension
    		)
    	);
        $this->kendo_aggregate->set_kendo_query($request)->adding($lookup, $project, $match)->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        
        $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
        // Change foreign_key
        $dialTypeOption = $this->mongo_db->where(array("tags" => ["Diallist", "type"]))->getOne("Jsondata");
        $dialTypeToName = array();
        if($dialTypeOption) {
	        foreach ($dialTypeOption["data"] as $row) {
	        	$dialTypeToName[$row->value] = $row->text;
	        }
        }
        $dialModeOption = $this->mongo_db->where(array("tags" => ["Diallist", "mode"]))->getOne("Jsondata");
        $dialModeToName = array();
        if($dialModeToName) {
	        foreach ($dialModeOption["data"] as $row) {
	        	$dialModeToName[$row->value] = $row->text;
	        }
    	}
        foreach ($data as &$doc) {
        	if(isset($doc["type"])) $doc["type"] = isset($dialTypeToName[$doc["type"]]) ? $dialTypeToName[$doc["type"]] : $doc["type"];
        	if(isset($doc["mode"])) $doc["mode"] = isset($dialModeToName[$doc["mode"]]) ? $dialModeToName[$doc["mode"]] : $doc["mode"];
        }
        // Result
        $response = array("data" => $data, "total" => $total);
		echo json_encode($response);
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		echo json_encode($response);
	}

	function create()
	{
		$data = $_POST;
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function update($id)
	{
		$data = $_POST;
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function delete($id)
	{
		$permanent = TRUE;
		$result = $this->crud->where_id($id)->delete($this->collection, $permanent);
		if($result) {
			$this->crud->where_object_id("diallist_id", $id)->delete_all($this->sub_collection, $permanent);
		}
		echo json_encode(array("status" => $result ? 1 : 0));
	}
}