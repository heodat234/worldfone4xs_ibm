<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Model extends CI_Controller {

	private $collection = "Model";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$this->load->config("_mongo");
		$_db = $this->config->item("_mongo_db");
		$this->mongo_db->switch_db($_db);
		$this->load->library("crud");
		$this->crud->select_db($_db);
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$response = $this->crud->read($this->collection, $request, ["index", "collection","field", "title", "type","sub_type"]);
		$this->load->model("language_model");
		$response = $this->language_model->translate($response, "CONTENT");
		echo json_encode($response);
	}

	function duplicate()
	{
		try {
			$sub = strtoupper($this->input->get("sub"));
			$config = $this->mongo_db->get("ConfigType", "type");
			$and_where = array();
			foreach ($config as $doc) {
				if(!empty($doc["type"])) {
					$and_where[] = array("collection" => array('$regex' => '^(?!' . $doc["type"] . '.*$).*', '$options' => ""));
				}
			}
			$data = $this->mongo_db->where(array(
					'$and' => $and_where
				)
			)->get($this->collection);

			foreach ($data as $doc) {
				if(isset($doc["collection"], $doc["field"])) {
					$doc["collection"] = $sub . "_" . $doc["collection"];
					$check = $this->mongo_db->where(array(
						"collection" => $doc["collection"],
						"field" => $doc["field"],
						"deleted" => array('$ne' => true)
					))->getOne($this->collection);
					if(!$check) {
						$this->mongo_db->insert($this->collection, $doc);
					}
				}
			}
			echo json_encode(array("status" => 1, "message" => "Complete"));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}