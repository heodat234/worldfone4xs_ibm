<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallist_detail extends CI_Controller {

	private $collection = "Diallist_detail";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
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
		$total = count($this->crud->get($this->collection, ["_id"], FALSE));
		$data["index"]	= $total;
		$data["createdBy"] = $this->session->userdata("extension");
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function create_many()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$last_doc = $this->crud->order_by(array("index" => -1))->getOne($this->collection, ["index"], FALSE);
		$next_index = $last_doc ? $last_doc["index"] + 1 : 0; 
		$results = array();
		$extension = $this->session->userdata("extension");
		if($data) {
			foreach ($data as $index => $doc) {
				$doc["index"] = $next_index + $index;
				$doc["createdBy"] = $extension;
				$results = $this->crud->create($this->collection, $doc);
			}
		}
		echo json_encode(array("status" => !in_array(FALSE, $results) ? 1 : 0));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["updatedBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0));
	}
}