<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Telesalelist extends WFF_Controller {

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
			$match = [];
			$match_cdr = [];
			$members = [];
			if(!in_array("viewall", $this->data["permission"]["actions"])) {
                $extension = $this->session->userdata("extension");
                $this->load->model("group_model");
                $members = $this->group_model->members_from_lead($extension);
                $match = ["assign" => ['$in' => $members]];
                $match_cdr = ["userextension" => ['$in' => $members]];
            }
			$response = $this->crud->read($this->collection, $request, [], $match);
			foreach ($response['data'] as &$value) {
				if (!empty($members) && isset($value['updatedBy']) && !in_array($value['updatedBy'], $members) ) {
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
			if(!empty($response)) {
				$dataLibrary = $this->mongo_db->where(array('contract_no' => $response['contract_no']))->getOne(set_sub_collection('Datalibrary'));
				if(!empty($dataLibrary)) {
					$response['dl_assign'] = (!empty($dataLibrary['assign'])) ? $dataLibrary['assign'] : $response['assign'];
					$response['dl_assign_name'] = (!empty($dataLibrary['assign_name'])) ? $dataLibrary['assign_name'] : $response['dl_assign'];
				}
			}
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
			$this->mongo_db->switch_db('_worldfone4xs');
			$user = $this->crud->where(array('extension' => $data['assign']))->getOne($this->user_collection);
			$this->mongo_db->switch_db();
			$data['assign_name'] = $user['agentname'];
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