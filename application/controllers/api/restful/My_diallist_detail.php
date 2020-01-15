<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class My_diallist_detail extends WFF_Controller {

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
		$extension = $this->session->userdata("extension");
		$match = array("assign" => $extension);
		if(isset($request["diallist_id"])) {
			$match["diallist_id"] = new MongoDB\BSON\ObjectId($request["diallist_id"]);
		}
		$response = $this->crud->read($this->collection, $request, [], $match);
		foreach ($response['data'] as $key => &$value) {
			if(isset($value['PRODGRP_ID'])){
				$temp = $this->mongo_db->where('code', $value['PRODGRP_ID'])->getOne('LO_Product');
				if(!empty($temp)){
					$value['PRODGRP_ID'] = $temp['name'];
				}
			}
			
		}
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