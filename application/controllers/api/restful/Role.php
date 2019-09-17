<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Role extends CI_Controller {
	private $collection = "Role";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->load->config("_mongo");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);
		$this->collection = set_sub_collection($this->collection);
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$response = $this->crud->read($this->collection, $request);
		foreach ($response["data"] as &$doc) {
			if(!empty($doc["privileges"]))
			{
				foreach ($doc["privileges"] as &$privilege) {
					if(isset($privilege["module_id"]) && $privilege["module_id"] instanceof MongoDB\BSON\ObjectId)
					{
						$privilege["module_id"] = $privilege["module_id"]->__toString();
					}
				}
			}
		}
		echo json_encode($response);
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		if($response) {
			if(!empty($response["privileges"]))
			{
				foreach ($response["privileges"] as &$privilege) {
					if(isset($privilege["module_id"]) && $privilege["module_id"] instanceof MongoDB\BSON\ObjectId)
					{
						$privilege["module_id"] = $privilege["module_id"]->__toString();
					}
				}
			}
		}
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(isset($data["privileges"]))
		{
			foreach ($data["privileges"] as &$privilege) {
				foreach ($privilege as $key => $value) {
					if($key == "module_id") $privilege[$key] = new MongoDB\BSON\ObjectId($value);
				}
			}
		}
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(isset($data["privileges"]))
		{
			foreach ($data["privileges"] as &$privilege) {
				foreach ($privilege as $key => $value) {
					if($key == "module_id") $privilege[$key] = new MongoDB\BSON\ObjectId($value);
				}
			}
		}
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}