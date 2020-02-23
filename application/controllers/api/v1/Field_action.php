<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Field_action extends WFF_Controller {

	private $collection = "Field_action";

	function __construct()
    {
    	parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
    }

    function read() {
    	try {
	    	$request = json_decode($this->input->get("q"), TRUE);
	    	$response = $this->crud->read($this->collection, $request);
	    	echo json_encode($response);
    	} catch(Exception $e) {
    		echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    	}
    }
}