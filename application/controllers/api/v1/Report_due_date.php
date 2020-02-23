<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Report_due_date extends WFF_Controller {

	private $collection = "Report_due_date";

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
		$message = '';
		$data["createdBy"] = $this->session->userdata("extension");
		$dueDate = explode("T", $data["due_date"]);
		$data['due_date'] = $dueDate[0];
		$data['due_date'] = strtotime($data['due_date']);
		$dueDate1True = false;
		$count = 1;
		while(!$dueDate1True) {
			$nextDay = $count * 86400 + $data["due_date"];
			$checkOffSys = $this->crud->where(array('off_date' => $nextDay))->get(set_sub_collection('Report_off_sys'));
			$dateInWeek = date('w', $nextDay);
			// if(empty($checkOffSys) && !in_array($dateInWeek, array(0, 6))) {
			// 	$data['due_date_add_1'] = $nextDay;
			// 	$dueDate1True = true;
			// }
			$data['due_date_add_1'] = $nextDay;
			$dueDate1True = true;
			$count++;
		}
		if(!empty($data['debt_group']['text'])) {
			$data['debt_group'] = $data['debt_group']['text'];
		}
		if(!empty($data['for_month']['text'])) {
			$data['for_month'] = $data['for_month']['text'];
		}
		if(!empty($data['for_year']['text'])) {
			$data['for_year'] = $data['for_year']['text'];
		}
		$checkExist = $this->mongo_db->where(array('debt_group' => $data['debt_group'], 'for_month' => $data['for_month'], 'for_year' => $data['for_year']))->getOne($this->collection);
		if(!empty($checkExist)) {
			$result = null;
			$message = "Đã tồn tại kỳ due date này.";
		}
		else {
			$result = $this->crud->create($this->collection, $data);
		}
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$data], "message" => $message));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
        $data["updatedBy"] = $this->session->userdata("extension");
        $dueDate = explode("T", $data["due_date"]);
		$data['due_date'] = $dueDate[0];
		$data['due_date'] = strtotime($data['due_date']);
		$dueDate1True = false;
		$count = 1;
		while(!$dueDate1True) {
			$nextDay = $count * 86400 + $data["due_date"];
			$checkOffSys = $this->crud->where(array('off_date' => $nextDay))->get(set_sub_collection('Report_off_sys'));
			$dateInWeek = date('w', $nextDay);
			if(empty($checkOffSys) && $dateInWeek != 0 && $dateInWeek != 6) {
				$data['due_date_add_1'] = $nextDay;
				$dueDate1True = true;
			}
			$count++;
		}
		if(!empty($data['debt_group']['text'])) {
			$data['debt_group'] = $data['debt_group']['text'];
		}
		if(!empty($data['for_month']['text'])) {
			$data['for_month'] = $data['for_month']['text'];
		}
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		echo json_encode(array("status" => $result ? 1 : 0, "data" => [$data]));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}