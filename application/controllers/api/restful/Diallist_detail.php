<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallist_detail extends WFF_Controller {

	private $collection = "Diallist_detail";

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
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$match = [];
			if(isset($request["diallist_id"])) {
				$match["diallist_id"] = new MongoDB\BSON\ObjectId($request["diallist_id"]);
			}
			$response = $this->crud->read($this->collection, $request, [], $match);

			foreach ($response['data'] as $key => &$value) {
				$last_dial_detail = $this->mongo_db->
				where("account_number", $value['account_number'])->
				where(array("createdAt" => array('$lte' => strtotime("today midnight"))))->
				order_by(array('_id' => -1))->get('LO_Diallist_detail');
				if(count($last_dial_detail) >0){
					$last_dial_detail = $last_dial_detail[0];
					$value['last_assign'] = isset($last_dial_detail["assign"]) ? $last_dial_detail['assign'] : '';
					
				}				

				if(isset($value['PRODGRP_ID'])){
					$temp = $this->mongo_db->where('code', $value['PRODGRP_ID'])->getOne('LO_Product');
					if(!empty($temp)){
						$value['PRODGRP_ID'] = $temp['name'];
					}
				}
			
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		if(isset($response['action_code'])) unset($response['action_code']);
		echo json_encode($response);
	}

	function create()
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$total = count($this->crud->get($this->collection, ["_id"], FALSE));
			$data["index"]	= $total;
			$data["createdBy"] = $this->session->userdata("extension");
			$result = $this->crud->create($this->collection, $data);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function create_many()
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$last_doc = $this->crud->order_by(array("index" => -1))->getOne($this->collection, ["index"], FALSE);
			$next_index = $last_doc ? $last_doc["index"] + 1 : 0; 
			$results = array();
			$extension = $this->session->userdata("extension");
			if($data) {
				foreach ($data as $index => $doc) {
					$doc["index"] = $next_index + $index;
					$doc["createdBy"] = $extension;
					$results = $this->crud->create($this->collection, $doc);
				}
			}
			echo json_encode(array("status" => !in_array(FALSE, $results) ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function update($id)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			if(isset($data["phone"]))
				$this->mongo_db->where("diallistdetail_id", $id)->set("phone", $data["phone"])->update("LO_Dial_queue");
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function delete($id)
	{
		try {
			$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
			$this->mongo_db->where("diallistdetail_id", $id)->delete("LO_Dial_queue");
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}