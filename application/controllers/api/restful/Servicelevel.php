<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Servicelevel extends WFF_Controller {

	private $collection = "Service_level";

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
		$parent_id = !empty($request["id"]) ? new MongoDB\BSON\ObjectId($request["id"]) : array('$exists' => false);
		$where = array('parent_id' => $parent_id);
		if(isset($request["lv"])) $where["lv"] = $request["lv"];
		$aggregate = array(
			array('$match' 	=> $where),
			array('$sort' 	=> array('pos' => 1))
		);
		try {
			$aggData = $this->crud->aggregate_pipeline($this->collection, $aggregate);
			echo json_encode($aggData);
		} catch (Exception $e) {
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
		}
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		unset($data["selected"]);
		$result = $this->crud->create($this->collection, $data);
		if( isset($result["parent_id"]) ) $this->updateParent($result["parent_id"]);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		unset($data["selected"], $data["items"], $data["fit"]);
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		if(!isset($data["parent_id"])) {
			$this->updateChild($data["id"], $data["id"]);
		}
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function delete($id)
	{
		$data = $this->crud->where_id($id)->getOne($this->collection);
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		if(isset($data["parent_id"])) {
			$this->updateChild($id, $data["parent_id"]);
			$this->updateParent($data["parent_id"]);
		} else $this->updateChild($id);
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	private function updateChild($id, $parent_id = "")
	{
		$currentData = $this->crud->where_id($id)->getOne($this->collection);
		$childData = $this->crud->where_object_id("parent_id", $id)->get($this->collection);
		foreach ($childData as $doc) {
			if($parent_id) {
				$newData = array(
					"parent_id" => $parent_id, 
				);
				$this->crud->where_id($doc["id"])->update($this->collection, array('$set' => $newData));
			} else {
				$this->crud->where_id($doc["id"])->update($this->collection, array('$unset' => array("parent_id" => 1)));
			}
		}
	}

	private function updateParent($id)
	{
		$doc = array();
		$childData = $this->crud->where_object_id("parent_id", $id)->get($this->collection);
		$doc = $childData ? 
				array("hasChild" => TRUE) : 
				array("hasChild" => FALSE);
		$this->crud->where_id($id)->update($this->collection, array('$set' => $doc));
		return $doc;
	}
}