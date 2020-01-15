<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Interactive extends WFF_Controller {

	private $collection = "Interactive";
	private $cdr_collection = "worldfonepbxmanager";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->cdr_collection = set_sub_collection($this->cdr_collection);
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$match = array();
		// print_r($this->data["permission"]["actions"]);
        if(!in_array("viewall", $this->data["permission"]["actions"])) {
            $extension = $this->session->userdata("extension");
            $this->load->model("group_model");
            $members = $this->group_model->members_from_lead($extension);
            $match["userextension"] = ['$in' => $members];
        }
        $cdr = $this->crud->read($this->cdr_collection, $request, ['calluuid'], $match);
        $calluuid = array();
        foreach ($cdr['data'] as $row) {
        	array_push($calluuid, $row['calluuid']);
       	}
       	// print_r($match);exit;
        $match_1["other_id"] = ['$in' => $calluuid];
		$response = $this->crud->read($this->collection, array(), [], $match_1);
		echo json_encode($response);
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["updatedBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function delete($id)
	{
		exit();
		try {
			$result = $this->crud->where_id($id)->delete($this->collection);
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}