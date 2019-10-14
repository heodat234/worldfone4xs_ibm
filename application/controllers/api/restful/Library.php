<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Library extends WFF_Controller {

	private $collection = "Library";

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
		$parent_id = isset($request["id"]) ? $request["id"] : array('$exists' => false);
		$aggregate = array(
			array('$match' 	=> array('parent_id' => $parent_id)),
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
		//unset($data["selected"], $data["expanded"]);
		unset($data["selected"]);
		$parent_id = isset($data["parent_id"]) ? $data["parent_id"] : array('$exists' => false);
		$lastDoc = $this->mongo_db->where(array("parent_id" => $parent_id))->order_by(array("pos" => -1))->getOne($this->collection);
		$lastPos = isset($lastDoc["pos"]) ? $lastDoc["pos"] : 0;
		$data["pos"] = $lastPos + 1;
		$result = $this->crud->create($this->collection, $data);
		if( isset($result["parent_id"]) ) $this->updateParent($result["parent_id"]);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		//unset($data["selected"], $data["expanded"]);
		unset($data["selected"]);
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
		$childData = $this->crud->where(array("parent_id" => $id))->get($this->collection);
		foreach ($childData as $doc) {
			if($parent_id) {
				$newData = array(
					"parent_id" => $parent_id, 
					"only_admin" => !empty($currentData["only_admin"])
				);
				if(isset($currentData["module_id"])) $newData["module_id"] = $currentData["module_id"];
				$this->crud->where_id($doc["id"])->update($this->collection, array('$set' => $newData));
			} else {
				$this->crud->where_id($doc["id"])->update($this->collection, array('$unset' => array("parent_id" => 1)));
			}
		}
	}

	private function updateParent($id)
	{
		$doc = array();
		$childData = $this->crud->where(array("parent_id" => $id))->get($this->collection);
		$doc = $childData ? 
				array("hasChild" => TRUE, "uri" => "parent", "apis" => [], "module_id" => NULL) : 
				array("hasChild" => FALSE, "uri" => "");
		$this->crud->where_id($id)->update($this->collection, array('$set' => $doc));
		return $doc;
	}
}