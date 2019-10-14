<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Organization extends WFF_Controller {

	private $collection = "Organization";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);
		$this->collection = set_sub_collection($this->collection);
	}

	function readAll()
	{
		try {
			$data = $this->readLevel();
			echo json_encode($data);
		} catch (Exception $e) {
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
		}
	}

	function readLevel($parent_id = null)
	{
		$where = !$parent_id ? array("parent_id" => array('$exists' => false)) : array("parent_id" => new MongoDB\BSON\ObjectId($parent_id));
		$data = $this->mongo_db->where($where)->select(["name","hasChild","color","parent_id","members","lead"])->get($this->collection);
		if($data) {
			foreach ($data as &$doc) {
				if(!empty($doc["hasChild"])) {
					$doc["items"] = $this->readLevel($doc["id"]);
				}
				if(!empty($doc["parent_id"])) {
					$doc["parent_id"] = $doc["parent_id"]->__toString();
				}
			}
		}
		return $data;
	}
}