<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Email extends WFF_Controller {

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->model("email_model");
	}

	function send_from_pending($id = "")
	{
		try {
			if(!$id) throw new Exception("No ID");
			$result = $this->email_model->send_from_pending($id);
			echo json_encode(array("status" => (int) $result, "message" => $result ? "Success" : "Failed"));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function send()
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$result = $this->email_model->send($data);
			echo json_encode(array("status" => (int) $result, "message" => $result ? "Success" : "Failed"));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}