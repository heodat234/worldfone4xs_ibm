<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Voicemail extends WFF_Controller {

    private $collection = "voicemails";
    private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
        $this->sub = set_sub_collection("");
        $this->collection = $this->sub . $this->collection;
	}

	function index()
	{
		$this->load->library("crud");
		$request = json_decode($this->input->get("q"), TRUE);
        $requestString = json_encode($request);
		
		$model = $this->crud->build_model($this->collection);
        // Kendo to aggregate
        $this->load->library("kendo_aggregate", $model);
        $lookup = array('$lookup' => array(
        		"from" => "{$this->sub}Customer",
			    "localField" => "customernumber",
			    "foreignField" => "phone",
			    "as" => "customer"
        	)
    	);
        $this->kendo_aggregate->set_kendo_query($request)->selecting();
        if(strpos($requestString, "customer.name")) {
            $this->kendo_aggregate->adding($lookup)->filtering();
        } $this->kendo_aggregate->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        $this->kendo_aggregate->sorting()->paging();
        if(!strpos($requestString, "customer.name")) $this->kendo_aggregate->adding($lookup);
        $data_aggregate = $this->kendo_aggregate->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
        // Result
        $response = array("data" => $data, "total" => $total);
        echo json_encode($response);
	}

    function detail($calluuid)
    {
        try {
            $this->load->library("mongo_db");
            $response = $this->mongo_db->where(array("calluuid" => $calluuid))->getOne($this->collection);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}