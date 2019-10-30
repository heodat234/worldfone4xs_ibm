<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Test extends CI_Controller {


	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->model("pbx_model");
		$data = $this->pbx_model->list_queues();
	    pre($data);
	}

	function update($id)
	{
		
	}
}