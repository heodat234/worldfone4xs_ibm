<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Scheduler extends WFF_Controller {

	private $collection = "Scheduler";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function read()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$response = $this->crud->read($this->collection, $request);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function LO_read()
	{
		try {
			$data = [];
			$due_dates = $this->mongo_db->get("LO_Report_due_date");
			foreach ($due_dates as $doc) {
				$row = [];
				$row["title"] = $row["shift"] = "Due date (Group {$doc["debt_group"]})";
				$row["start"] = $row["end"] = date("c", $doc["due_date"]);
				$row["isAllDay"] = TRUE;
				$data[] = $row;
				$row = [];
				$row["title"] = $row["shift"] = "Due date +1";
				$row["start"] = $row["end"] = date("c", $doc["due_date_add_1"]);
				$row["isAllDay"] = TRUE;
				$data[] = $row;
			}
			$off_dates = $this->mongo_db->get("LO_Report_off_sys");
			foreach ($off_dates as $doc) {
				$row = [];
				$row["title"] = $row["shift"] = "Off date";
				$row["start"] = $row["end"] = date("c", $doc["off_date"]);
				$row["isAllDay"] = TRUE;
				$data[] = $row;
			}
			echo json_encode(["data"=>$data,"total"=>count($data)]);
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
			$result = $this->crud->create($this->collection, $data);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
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
			echo json_encode(array("status" => $result ? 1 : 0, "data" => [$data]));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function delete($id)
	{
		try {
			$result = $this->crud->where_id($id)->delete($this->collection);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}