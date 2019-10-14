<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Sms extends WFF_Controller {

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
	}

	function send_from_pending($id = "")
	{
		try {
			if(!$id) throw new Exception("No ID");
			$this->load->model("sms_model");
			$this->sms_model->send($id);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}