<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agentsign extends CI_Controller {

	private $collection = "Agent_sign";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');

		$this->load->library("mongo_private");
		$secret_key = $this->input->get("secret_key");
		
		$config = $this->mongo_private->where(array("secret_key"=> $secret_key))->getOne("ConfigType");
		$this->sub = isset($config["type"]) ? ($config["type"]) . "_" : "";
		$this->collection = $this->sub . $this->collection;
	}

	function getUserOnline()
	{
		try {
			$extension = $this->input->get("extension");
			$time = time();
			$timeAfterNoPing = 60;
			$count = count($this->mongo_private->where(array("extension" => $extension, "lastpingtime" => array('$gt' => $time - $timeAfterNoPing)))->get($this->sub . "User"));
			
			echo json_encode(array("status" => 1, "online" => $count));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}