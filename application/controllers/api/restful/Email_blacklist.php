<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Email_blacklist extends WFF_Controller {

	private $collection = "Email_blacklist";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
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

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["createdBy"] = $this->session->userdata("extension");
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$data]));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["updatedBy"] = $this->session->userdata("extension");
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$data]));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}