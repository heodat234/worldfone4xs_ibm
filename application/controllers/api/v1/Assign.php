<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Assign extends WFF_Controller {

	private $collection = "Telesalelist";
	private $user_collection = "User";
	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->user_collection = set_sub_collection($this->user_collection);
	}

	function changeAssign()
	{
		try {
			$post = $this->input->post();
			$assign = $post['assign'];
			$this->mongo_db->switch_db('_worldfone4xs');
            $user = $this->mongo_db->where(array('extension' => (string)$assign  ))->select(array('extension','agentname'))->getOne($this->user_collection);
            $this->mongo_db->switch_db();
			$data = array('assign' => $assign, 'assign_name' => $user['agentname'],'createdBy' => 'ByAdmin');
			foreach ($post['select'] as $row) {
				$this->crud->where_id($row)->update($this->collection, array('$set' => $data));
			}
			echo json_encode(array("status" => 1, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}