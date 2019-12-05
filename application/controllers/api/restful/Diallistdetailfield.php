<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallistdetailfield extends WFF_Controller {

	private $collection = "Model";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$this->mongo_db->switch_db("_worldfone4xs");
		$this->load->library("crud");
		$this->sub = set_sub_collection();
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$match = array("collection" => $this->sub . "Diallist_detail");
		$response = $this->crud->read($this->collection, $request, ["index","title","field","type","sub_type"], $match);
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["collection"] = $this->sub . "Diallist_detail";

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
		$result = $this->crud->where_id($id)->delete($this->collection);
		echo json_encode(array("status" => $result ? 1 : 0));
	}
}