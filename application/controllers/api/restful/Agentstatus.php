<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agentstatus extends WFF_Controller {

	private $collection = "Agent_status_log";
	private $asc_collection = "Agent_status_code";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->asc_collection = set_sub_collection($this->asc_collection);
	}

	function read()
	{
		try {
			$request =  json_decode($this->input->get("q"), TRUE);
			$collection = $this->collection;
			$model = $this->crud->build_model($collection);
	        // Kendo to aggregate
	        $this->load->library("kendo_aggregate", $model);
	        $lookup = array('$lookup' => array(
	        		"from" => $this->asc_collection,
				    "localField" => "statuscode",
				    "foreignField" => "value",
				    "as" => "status"
	        	)
	    	);
	    	$unwind = array('$unwind' => array(
	    			'path'							=> '$status',
			    	'preserveNullAndEmptyArrays'	=> TRUE
	    		)
	    	);
	        $this->kendo_aggregate->set_kendo_query($request)->selecting()->adding($lookup, $unwind)->filtering();
	        // Get total
	        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
	        $total_result = $this->mongo_db->aggregate_pipeline($collection, $total_aggregate);
	        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
	        // Get data
	        
	        $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
	        $data = $this->mongo_db->aggregate_pipeline($collection, $data_aggregate);
	        // Result
        	$response = array("data" => $data, "total" => $total);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}