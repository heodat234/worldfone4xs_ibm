<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Configtype extends WFF_Controller {

	private $collection = "ConfigType";

	private $fields = ["typename","auto_delete_misscall","auto_delete_followup","acw_duration"];

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$this->load->config("_mongo");
		$_db = $this->config->item("_mongo_db");
		$this->mongo_db->switch_db($_db);
	}

	function detail($type)
	{
		$response = $this->mongo_db->where(array("type" => $type))->select($this->fields)->getOne($this->collection);
		echo json_encode($response);
	}

	function update($id)
	{
		$request = json_decode(file_get_contents('php://input'), TRUE);
		$data = [];
		foreach ($this->fields as $f) {
			if(isset($request[$f])) {
				$data[$f] = $request[$f];
			}
		}
		$data["updatedBy"] = $this->session->userdata("extension");
		$data["updatedAt"] = time();
		$result = $this->mongo_db->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}