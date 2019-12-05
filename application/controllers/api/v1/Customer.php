<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Customer extends WFF_Controller {

	private $collection = "Customer";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function upsert($key_field, $value)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			if( !isset($data[$key_field]) ) throw new Exception("Lack of " . $key_field, 401);
			
			$data["createdBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->where([$key_field => $value])->update($this->collection, ['$set' => $data], ["upsert" => TRUE]);
			$doc = $this->crud->where([$key_field => $value])->getOne($this->collection);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => [$doc]));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}