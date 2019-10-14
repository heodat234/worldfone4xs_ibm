<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Phone_blacklist extends WFF_Controller {

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->model("pbx_model");
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$result = $this->pbx_model->blacklist();
		$response["data"] = isset($result["data"]) ? $result["data"] : [];
		$response["total"] = count($response["data"]); 
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$result = $this->pbx_model->blacklist("add", $data["number"]);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$data]));
	}

	function update($number)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$result = $this->pbx_model->blacklist("put", $number, $data["status"]);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$data]));
	}

	function delete($number)
	{
		$result = $this->pbx_model->blacklist("delete", $number);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}