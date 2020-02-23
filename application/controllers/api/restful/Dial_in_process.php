<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Dial_in_process extends CI_Controller {

	private $collection = "Dial_in_process";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$response = $this->crud->read($this->collection, $request);
		echo json_encode($response);
	}
}