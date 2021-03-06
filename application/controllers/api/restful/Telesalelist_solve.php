<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Telesalelist_solve extends WFF_Controller {

	private $collection = "Telesalelist";
	private $user_collection = "User";
	private $call_collection = "worldfonepbxmanager";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->user_collection = set_sub_collection($this->user_collection);
		$this->call_collection = set_sub_collection($this->call_collection);
	}

	function read()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$extension = $this->session->userdata("extension");
			$where = array('assign' => $extension);
			$response = $this->crud->read($this->collection, $request,'',$where);
			foreach ($response['data'] as &$value) {
				if ( isset($value['updatedBy']) && $value['updatedBy'] != $extension )  {
					$value['is_potential'] = false;
					$value['result'] = '';
				}
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function detail($id)
	{
		try {
			$response = $this->crud->where_id($id)->getOne($this->collection);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function create()
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["createdBy"]	=	$this->session->userdata("extension");
			if(!empty($data["calluuid"])) {
				$callInfo = $this->mongo_db->where(array('calluuid' => $data['calluuid']))->getOne($this->call_collection);
				$data['starttime_call'] = (!empty($callInfo['starttime'])) ? $callInfo['starttime'] : null;
			}
			$result = $this->crud->create($this->collection, $data);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function create_many()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$results = array();
		$extension = $this->session->userdata("extension");
		if($data) {
			foreach ($data as $index => $doc) {
				$doc["createdBy"]	=	$extension;
				$results = $this->crud->create($this->collection, $doc);
			}
		}
		echo json_encode(array("status" => !in_array(FALSE, $results) ? 1 : 0));
	}

	function update($id)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			unset($data['assigning']);
			unset($data['assigning_name']);
			if(!empty($data["calluuid"])) {
				$callInfo = $this->mongo_db->where(array('calluuid' => $data['calluuid']))->getOne($this->call_collection);
				$data['starttime_call'] = (!empty($callInfo['starttime'])) ? $callInfo['starttime'] : null;
			}
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			// Write log update
			$data["createdBy"]  =	$this->session->userdata("extension");
			$this->crud->create($this->collection . "_log", $data);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function delete($id)
	{
		try {
			$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}