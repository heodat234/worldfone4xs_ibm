<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Organization extends WFF_Controller {

	private $collection = "Organization";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$parent_id = isset($request["id"]) ? new MongoDB\BSON\ObjectId($request["id"]) : array('$exists' => false);
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
		$parent_id = isset($data["parent_id"]) ? new MongoDB\BSON\ObjectId($data["parent_id"]) : array('$exists' => false);
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
		unset($data["selected"], $data["expanded"], $data["items"]);
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		if(!isset($data["parent_id"])) {
			$this->updateChild($data["id"], $data["id"]);
		} else {
			$this->updateParent($data["parent_id"]);
		}
		$this->syncToGroup($id);
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

	function detail($id)
	{
		try {
			$response = $this->crud->where_id($id)->getOne($this->collection);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
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
		$doc = $childData ? array("hasChild" => TRUE) : array("hasChild" => FALSE);
		$this->crud->where_id($id)->update($this->collection, array('$set' => $doc));
		return $doc;
	}

	private function syncToGroup($organization_id)
	{
		$data = $this->mongo_db->where_id($organization_id)->getOne($this->collection);
		if(empty($data["hasChild"]) && !empty($data["members"])) {
			$data["type"] = "custom";
			$data["name"] = trim($this->getParentTreeName($organization_id), "/");
			$data["tree_lead"] = trim($this->getParentTreeLead($organization_id), "/");
			if(empty($data["lead"]) && !empty($data["tree_lead"])) {
				$leads = explode("/", $data["tree_lead"]);
				$data["lead"] = $leads[count($leads) - 1];
			}
			if(isset($data["group_id"])) {
				$group = $this->mongo_db->where_id($data["group_id"])->getOne($this->sub . "Group");
				if($group) {
					// Da sync truoc day va van con group
					$this->mongo_db->where_id($data["group_id"])->set($data)->update($this->sub . "Group");
				} else {
					// Da sync truoc day nhung ko con group
					$group = $this->mongo_db->insert($this->sub . "Group", $data);
					// Update group id
					$this->mongo_db->where_id($organization_id)->set(array("group_id" => $group["id"]))->update($this->collection);
				}
			} else {
				// Chua tung sync
				$group = $this->mongo_db->insert($this->sub . "Group", $data);
				// Update group id
				$this->mongo_db->where_id($organization_id)->set(array("group_id" => $group["id"]))->update($this->collection);
			}
		}
	}

	private function getParentTreeName($id)
	{
		$doc = $this->mongo_db->where_id($id)->getOne($this->collection);
		if(!$doc) return "";
		if(empty($doc["name"])) return "";
		if(empty($doc["parent_id"])) return "";
		return $this->getParentTreeName($doc["parent_id"]) . "/" . $doc["name"];
	}

	private function getParentTreeLead($id)
	{
		$doc = $this->mongo_db->where_id($id)->getOne($this->collection);
		if(!$doc) return "";
		$tree_lead = !empty($doc["parent_id"]) ? $this->getParentTreeLead($doc["parent_id"]) : "";
		$lead = !empty($doc["lead"]) ? $doc["lead"] : "";
		return $tree_lead . "/" . $lead;
	}
}