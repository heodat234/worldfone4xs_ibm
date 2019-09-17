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
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$response = $this->crud->read($this->collection, $request, ["id", "name", "parent_id", "lv"]);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function select()
	{
		try {
			$data = array();
			$service_lv1 = $this->mongo_db->where(array("lv" => 1))->select(["name", "lv"])->get($this->collection);
			foreach ($service_lv1 as $doc1) {
				$service_lv2 = $this->mongo_db->where(array("lv" => 2, "parent_id" => new MongoDB\BSON\ObjectId($doc1["id"])))
				->select(["name", "lv"])->get($this->collection);
				foreach ($service_lv2 as $doc2) {
					$service_lv3 = $this->mongo_db->where(array("lv" => 3, "parent_id" => new MongoDB\BSON\ObjectId($doc2["id"])))
					->select(["name", "lv"])->get($this->collection);
					foreach ($service_lv3 as $doc3) {
						$data[] = array(
							"value" 		=> $doc1["name"] . " / " . $doc2["name"] . " / " . $doc3["name"],
							"value1"		=> $doc1["name"],
							"value2"		=> $doc2["name"],
							"value3"		=> $doc3["name"],
						); 
					}
				}
			}
			echo json_encode(array("data" => $data, "total" => count($data)));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}