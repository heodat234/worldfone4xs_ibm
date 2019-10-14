<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Configtype extends CI_Controller {

	private $collection = "ConfigType";

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
		$response = $this->mongo_db->where(array("type" => $type))->select(["call_init_point"])->getOne($this->collection);
		echo json_encode($response);
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$result = $this->mongo_db->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}