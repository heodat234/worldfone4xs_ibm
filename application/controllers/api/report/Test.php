<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Test extends WFF_Controller {

	private $collection = "Activity_log";
	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection();
		$this->load->library("crud");
		$this->load->model("language_model");
	}

	function add()
	{
		$this->load->model("pbx_model");
		$result = $this->pbx_model->add_queue_member("2000", "999");
		pre($result);
	}
}