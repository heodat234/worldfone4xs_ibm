<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Telesalelist_solve extends WFF_Controller {

	private $collection = "Telesalelist";

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

	function exportExcel() {
		ini_set('max_execution_time', 0);
		$this->load->library('mongo_db');
		$request = $this->input->get();
		$match = array('assign' => $this->session->userdata("extension"));
		$model = $this->crud->build_model($this->collection);
		$this->load->library("kendo_aggregate", $model);
		$this->kendo_aggregate->set_kendo_query($request);
		$this->kendo_aggregate->filtering();
		$data_aggregate = $this->kendo_aggregate->get_data_aggregate();
		// echo(json_encode($data_aggregate));
		$pythonCron = FCPATH . 'cronjob/python/Telesales/saveTelesaleList.py';
		$exportLog = array(
			'start'			=> time(),
			'filter'		=> json_encode($request),
			'status'		=> 2,
			'python_cron'   => $pythonCron,
			'filter'		=> json_encode($data_aggregate),
			'created_at'	=> time(),
			'created_by'	=>$this->session->userdata("extension")
		);
		$exportLogId = $this->crud->create(set_sub_collection('Export'), $exportLog);
		if (in_array(ENVIRONMENT, array('UAT'))) {
			$command = escapeshellcmd('/usr/local/bin/python3.6 ' . $pythonCron . ' ' . $exportLogId['id']) . ' > /dev/null &';
		}
		else {
			$command = escapeshellcmd('/usr/local/bin/python3.6 ' . $pythonCron . ' ' . $exportLogId['id']) . ' > /dev/null &';
		}
		$output = shell_exec($command);
		echo json_encode(array('status' => 2, "message" => "Xuất dữ liệu... Xin vui lòng chờ trong giây lát"));
	}

	function updateByCif($cif) {
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			if(!empty($data["calluuid"])) {
				$callInfo = $this->mongo_db->where(array('calluuid' => $data['calluuid']))->getOne(set_sub_collection('worldfonepbxmanager'));
				$data['starttime_call'] = (!empty($callInfo['starttime'])) ? $callInfo['starttime'] : null;
			}
			$result = $this->crud->where(array('cif' => $cif))->update($this->collection, array('$set' => $data));
			// Write log update
			$data["createdBy"]  =	$this->session->userdata("extension");
			$this->crud->create($this->collection . "_log", $data);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}