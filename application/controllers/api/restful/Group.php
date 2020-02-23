<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Group extends WFF_Controller {

	private $collection = "Group";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$response = $this->crud->read($this->collection, $request);
		echo json_encode($response);
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["createdBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->create($this->collection, $data);
		$this->createGroupMappingCampaign($result);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["updatedBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}

	function delete($id)
	{
		$this->deleteGroupMappingCampaign($id);
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}

	function createGroupMappingCampaign($data){
		$param['name'] = $data['name'];
		$param['debt_type'] = $data['debt_type'];
		$param['debt_group'] = $data['debt_group'];
		$this->crud->create('LO_Group_mapping_campaign', $param);
	}

	function deleteGroupMappingCampaign($id){
		$group = $this->crud->where_id($id)->getOne($this->collection);
		$this->mongo_db->where('name', $group['name'])->delete('LO_Group_mapping_campaign');
	}
}