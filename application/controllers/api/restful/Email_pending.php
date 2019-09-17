<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Email_pending extends WFF_Controller {

	private $collection = "Email_pending";

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
			$request = json_decode($this->input->get("q"), TRUE);
			$response = $this->crud->read($this->collection, $request);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function detail($id)
	{
		try {
			$response = $this->crud->where_id($id)->getOne($this->collection);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function create()
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["createdBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->create($this->collection, $data);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function create_many()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$results = array();
		$extension = $this->session->userdata("extension");
		if($data) {
			foreach ($data as $index => $doc) {
				$doc["createdBy"]	=	$extension;
				$results = $this->crud->create($this->collection, $doc);
			}
		}
		echo json_encode(array("status" => !in_array(FALSE, $results) ? 1 : 0));
	}

	function update($id)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function delete($id)
	{
		try {
			$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}