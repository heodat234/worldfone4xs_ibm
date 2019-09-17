<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallistdetail extends CI_Controller {

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
		$request = $_GET;
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
		$data = $_POST;
		$total = count($this->crud->get($this->collection, ["_id"], FALSE));
		$data["index"]	= $total;
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function create_many()
	{
		$data = $this->input->post("data");
		$last_doc = $this->crud->order_by(array("index" => -1))->getOne($this->collection, ["index"], FALSE);
		$next_index = $last_doc ? $last_doc["index"] + 1 : 0; 
		$results = array();
		if($data) {
			foreach ($data as $index => $doc) {
				$doc["index"] = $next_index + $index;
				$results = $this->crud->create($this->collection, $doc);
			}
		}
		echo json_encode(array("status" => !in_array(FALSE, $results) ? 1 : 0));
	}

	function update($id)
	{
		$data = $_POST;
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0));
	}
}