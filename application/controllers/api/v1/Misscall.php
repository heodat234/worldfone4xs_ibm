<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Misscall extends CI_Controller {

	private $collection = "misscall";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->collection = set_sub_collection($this->collection);
	}

	function index()
	{
		$this->load->library("crud");
		$request = json_decode($this->input->get("q"), TRUE);
		$response = $this->crud->read($this->collection, $request);
		echo json_encode($response);
	}

	function update($id)
	{
		$data = $_POST;
		$data["updatedBy"] = $this->session->userdata("extension");
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0));
	}
}