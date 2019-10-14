<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agentsign extends WFF_Controller {

	/**
	 * API restful [Agent_sign] collection.
	 * READ from base_url + api/restful/agentsign 
	 */

	private $collection = "Agent_sign";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function read()
	{
		try {
			$request =  json_decode($this->input->get("q"), TRUE);
			$response = $this->crud->read($this->collection, $request);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}