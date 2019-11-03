<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Test extends CI_Controller {


	function __construct()
	{
		parent::__construct();
	}

	function index($id = "")
	{
		$this->load->library("session");
		pre($this->session->userdata("role_name"));
	}

	function update()
	{
		$this->load->model("afterlogin_model");
		$data = $this->afterlogin_model->update_user();
	}
}