<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Changepassword extends WFF_Controller {

	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection("");
	}

	function save()
	{
		try {
			$this->load->model("pbx_model");
			$old_password = $this->input->post('old_password');
			$new_password = $this->input->post('new_password');
			$response = $this->pbx_model->change_password($old_password, $new_password);
			if(empty($response["data"])) throw new Exception("No success");
			if($response["data"]["result"] != "success") throw new Exception("No success");
			$extension = $this->session->userdata("extension");
			$this->load->library("mongo_db");
			$agent_signs = $this->mongo_db->where(array("extension" => $extension, "signouttime" => 0))->select(["my_session_id", "session_ids"])->get("{$this->sub}Agent_sign");
			$this->load->library("mongo_private");
			foreach ($agent_signs as $agent_sign) {
				$session_ids = isset($agent_sign["session_ids"]) ? $agent_sign["session_ids"] : [];
				if($session_ids) {
					$last_session_id = $session_ids[count($session_ids) - 1];
					$this->mongo_private->where(array("_id"=> $last_session_id))->update( $this->config->item("session_mongo_collection") , array('$unset' => array("data" => 1)));
				}
			}
			echo json_encode(array("status" => 1, "message" => "Change password success"));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}