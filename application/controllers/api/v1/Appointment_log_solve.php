<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Appointment_log_solve extends WFF_Controller {

	private $collection = "Appointment_log";
	private $ftpFilename = '';

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
        $this->load->library("Excel");
        $this->load->library("csv");
        $this->load->library('mongo_db');
        $this->load->library('pheanstalk');
        $this->load->model('ftp_model');
		$this->collection = set_sub_collection($this->collection);
    }
    
    function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request, [], array('tl_code' => $this->session->userdata("extension")));
            if(!empty($response['data'])) {
                foreach($response['data'] as $key => &$value) {
                    if(!empty($value['id_no'])) {
                        $telesaleListInfo = $this->crud->where(array('id_no' => $value['id_no']))->getOne(set_sub_collection("Telesalelist"));
                        $value['customer_info'] = (!empty($telesaleListInfo)) ? $telesaleListInfo : array();
                    }
                }
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function create() {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        try {
            $this->load->library("crud");
            $data = json_decode(file_get_contents('php://input'), TRUE);
            if(!empty($data['dealer_location']['location'])) {
                $data['dealer_location'] = $data['dealer_location']['location'];
            }
            $data['tl_code'] = $this->session->userdata("extension");
            $data['tl_name'] = $this->session->userdata("agentname");
            $data["created_at"]	= time();
            $data["created_by"]	= $this->session->userdata("extension");
            $data["created_by_name"] = $this->session->userdata("agentname");
            $result = $this->crud->create($this->collection, $data);
            echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function update($id) {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["updated_by"]  =   $this->session->userdata("extension");
            $data["updated_by_name"] = $this->session->userdata("agentname");
            $data['updated_at'] = time();
            $this->load->library("crud");
            if(!empty($data['dealer_location']['location'])) {
                $data['dealer_location'] = $data['dealer_location']['location'];
            }
            $result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
            echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
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