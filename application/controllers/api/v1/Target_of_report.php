<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Target_of_report extends WFF_Controller {

	private $collection = "Target_of_report";

	function __construct()
    {
    	parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
    }

    function checkTrungName() {
        $this->load->library('mongo_db');
    	try {
	    	$request = $_GET;
	    	$response = $this->mongo_db->where($request)->get($this->collection);
	    	echo json_encode($response);
    	} catch(Exception $e) {
    		echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    	}
    }
}