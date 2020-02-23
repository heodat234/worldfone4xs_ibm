<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Telesalelist_assigning extends WFF_Controller {

	private $collection = "Telesalelist_assigning";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->load->library("mongo_db");
		$this->collection = set_sub_collection($this->collection);
	}

	function requestAssign() {
		try {
			$request = json_decode(file_get_contents('php://input'), TRUE);
			$request['assigning'] = $this->session->userdata("extension");
			$request['assigning_name'] = $this->session->userdata("agentname");
			$request['request_time'] = time();
			$request['cus_id'] = $request['id'];
			$checkExistRequest = $this->mongo_db->where(array('id_no' => $request['id_no'], 'assigning' => $this->session->userdata("extension")))->getOne($this->collection);
			if(empty($checkExistRequest)) {
				$this->mongo_db->insert($this->collection, $request);
				$this->mongo_db->where(array('id_no' => $request['id_no']))->set(array('assigning' => $this->session->userdata("extension"), 'assigning_name' => $this->session->userdata("agentname")))->update_all(set_sub_collection('Telesalelist'));
				$this->mongo_db->where(array('id_no' => $request['id_no']))->set(array('assigning' => $this->session->userdata("extension"), 'assigning_name' => $this->session->userdata("agentname")))->update_all(set_sub_collection('Datalibrary'));
				echo json_encode(array('status' => 1, 'message' => 'Yêu cầu dữ liệu thành công. Xin vui lòng refesh lại popup'));
			}
			else {
				echo json_encode(array('status' => 0, 'message' => $checkExistRequest['assigning_name'] . ' - Chờ duyệt. Xin vui lòng refesh lại popup'));
			}
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function reject() {
		try {
			$request = json_decode(file_get_contents('php://input'), TRUE);
			$request['request_time'] = strtotime($request['request_time']);
			$request['status'] = 'reject';
			$request['updated_by'] = $this->session->userdata('extension');
			$request['updated_at'] = time();
			$this->mongo_db->insert(set_sub_collection('Telesalelist_assigning_log'), $request);
			$this->mongo_db->where_id($request['id'])->delete_all($this->collection);
			$this->mongo_db->where_id($request['cus_id'])->set(array('assigning' => '', 'assigning_name' => ''))->update(set_sub_collection('Telesalelist'));
			$this->mongo_db->where_id($request['cus_id'])->set(array('assigning' => '', 'assigning_name' => ''))->update(set_sub_collection('Datalibrary'));
			$notifiction = array(
				'title'			=> 'Reject request',
				'icon'			=> 'fa fa-times',
				'color'			=> 'text-danger',
				'to'			=> array($request['assigning']),
				'link'			=> '',
				'active'		=> true,
				'createdBy' 	=> $this->session->userdata("extension"),
				'content'		=> 'Admin đã reject yêu cầu xin Khách hàng ' . $request['id_no'] . ' - ' . $request['name'] . ' - của bạn. Vui lòng liên hệ Admin để biết thêm chi tiết',
				"notifyDate"	=> new MongoDB\BSON\UTCDateTime(time() * 1000),
				"createdBy" 	=> "System",
				"createdAt" 	=> time()
			);
			$this->mongo_db->insert(set_sub_collection('Notification'), $notifiction);
			echo json_encode(array('status' => 1, 'data' => $request));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function approve() {
		try {
			$request = json_decode(file_get_contents('php://input'), TRUE);
			$request['request_time'] = strtotime($request['request_time']);
			$request['status'] = 'approve';
			$request['updated_by'] = $this->session->userdata('extension');
			$request['updated_at'] = time();
			$this->mongo_db->insert(set_sub_collection('Telesalelist_assigning_log'), $request);
			$this->mongo_db->where_id($request['id'])->delete_all($this->collection);
			$callingListInfo = $this->mongo_db->where_id($request['cus_id'])->get(set_sub_collection('Telesalelist'));
			if(!empty($callingListInfo)) {
				$this->mongo_db->where_id($request['cus_id'])->set(array('assigning' => '', 'assigning_name' => '', 'assign' => $request['assigning'], 'assign_name' => $request['assigning_name'], 'is_data_library_list' => false))->update_all(set_sub_collection('Telesalelist'));
			}
			else {
				$this->mongo_db->where_id($request['cus_id'])->set(array('assigning' => '', 'assigning_name' => '', 'assign' => $request['assigning'], 'assign_name' => $request['assigning_name'], 'is_data_library_list' => false))->update_all(set_sub_collection('Datalibrary'));
				$dataLibrary = $this->mongo_db->where_id($request['cus_id'])->getOne(set_sub_collection('Datalibrary'));
				$dataLibrary['name'] = $dataLibrary['customer_name'];
				$dataLibrary['phone'] = $dataLibrary['mobile_phone_no'];
				unset($dataLibrary['createdBy']);
				unset($dataLibrary['createdAt']);
				unset($dataLibrary['updatedAt']);
				unset($dataLibrary['updatedBy']);
				$dataLibrary['createdBy'] = 'From DataLib';
				$dataLibrary['createdAt'] = time();
				$telesaleInfo = $this->crud->create(set_sub_collection('Telesalelist'), $dataLibrary);
				$this->mongo_db->where(array('calluuid' => $request['calluuid']))->set(array('customer' => $telesaleInfo))->update(set_sub_collection('worldfonepbxmanager'));
			}
			$notifiction = array(
				'title'			=> 'Approve request',
				'icon'			=> 'fa fa-check',
				'color'			=> 'text-success',
				'to'			=> array($request['assigning']),
				'link'			=> '/manage/telesalelist/solve',
				'active'		=> true,
				'createdBy' 	=> $this->session->userdata("extension"),
				'content'		=> 'Khách hàng ' . $request['id_no'] . ' - ' . $request['name'] . ' đã được Admin approve. Vui lòng check thêm thông tin trong Danh sách Calling List.)',
				"notifyDate"=> new MongoDB\BSON\UTCDateTime(time() * 1000),
				"createdBy" => "System",
				"createdAt" => time()
			);
			$this->mongo_db->insert(set_sub_collection('Notification'), $notifiction);
			echo json_encode(array('status' => 1, 'data' => $request));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}