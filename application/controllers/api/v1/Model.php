<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Model extends WFF_Controller {

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
			$fromDepartment = strtoupper($this->input->get("fromDepartment"));
			$toDepartment = strtoupper($this->input->get("toDepartment"));
			$data = $this->mongo_db->where(
				array(
					"collection" => array('$regex' => '^' . $fromDepartment, '$options' => "")
				)
			)->get($this->collection);

			foreach ($data as $doc) {
				if(isset($doc["collection"], $doc["field"])) {
					$origin_collection = str_replace($fromDepartment . "_", "", $doc["collection"]);
					$doc["collection"] = $toDepartment . "_" . $origin_collection;
					$check = $this->mongo_db->where(array(
						"collection" 	=> $doc["collection"],
						"field" 		=> $doc["field"]
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

	function getModel($table)
	{
		$data = $this->mongo_db->where("collection", $table)->select(["field","type"], ["_id"])->get($this->collection);
		$this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($data));
	}
}