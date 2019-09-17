<?php

class CheckEmailTicket extends CI_Controller 
{
	function __construct() {
		parent::__construct();
		header('Content-type: application/json');
	}

	function run()
	{
		try {
			$this->load->model("emailticket_model");
			$result = $this->emailticket_model->runCheckEmail();
			echo json_encode(array("status" => (int) $result));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}
