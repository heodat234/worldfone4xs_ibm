<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallist extends CI_Controller {

	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection("");
	}

	function diallistDetailField($subtype) {
		$request = $_GET;
		$this->load->library("crud");
		$this->crud->select_db($this->config->item("_mongo_db"));
		$request["sort"] = array(array("field" => "index", "dir" => "asc"));
		$match = array("sub_type" => $subtype, "collection" => $this->sub . "Diallist_detail");
		$response = $this->crud->read("Model", $request, ["field", "title", "type"], $match);
		echo json_encode($response);
	}
}