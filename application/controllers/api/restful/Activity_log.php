<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Activity_log extends WFF_Controller {

	private $collection = "Activity_log";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$response = $this->crud->read($this->collection, $request);
		echo json_encode($response);
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		echo json_encode($response);
	}
}