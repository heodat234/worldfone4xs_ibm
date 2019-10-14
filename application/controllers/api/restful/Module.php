<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Module extends WFF_Controller {

	private $sub = "";
	private $collection = "Module";
	private $navigator_collection = "Navigator";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->load->config("_mongo");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
		$this->navigator_collection = $this->sub . $this->navigator_collection;
		$this->load->model("language_model");
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
		if($response) {
			$paths = $this->crud->where_object_id("module_id", $id)->where(array("hasChild" => array('$ne' => true)))
			->get($this->navigator_collection, ["name", "uri"]);
			$response["paths"] = $paths;
		}
		$response = $this->language_model->translate($response);
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0));
	}
}