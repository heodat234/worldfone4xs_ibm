<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Assign extends WFF_Controller {

	private $collection = "Telesalelist";
	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function changeAssign()
	{
		try {
			$post = $this->input->post();
			$assign = $post['assign'];
			$data = array('assign' => $assign, 'assigned_by' => 'ByAdmin');
			foreach ($post['select'] as $row) {
				$this->crud->where_id($row)->update($this->collection, array('$set' => $data));
			}
			echo json_encode(array("status" => 1, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}