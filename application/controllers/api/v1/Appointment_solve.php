<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Appointment_solve extends WFF_Controller {

	private $collection = "Appointment";
	private $ftpFilename = '';

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
        $this->load->library("Excel");
        $this->load->library("csv");
        $this->load->library('mongo_db');
        $this->load->model('ftp_model');
        $this->load->model('user_model');
		$this->collection = set_sub_collection($this->collection);
	}

    function read()
    {
        try {
            $match = array();
            $request = json_decode($this->input->get("q"), TRUE);
            $match['assign'] = $this->session->userdata("extension");
            $response = $this->crud->read($this->collection, $request, [], $match);
            if(!empty($response['data'])) {
                foreach($response['data'] as $key => &$value) {
                    if(!empty($value['cif'])) {
                        $telesaleListInfo = $this->crud->where(array('cif' => $value['cif']))->getOne(set_sub_collection('Telesalelist'));
                        $value['customer_info'] = (!empty($telesaleListInfo)) ? $telesaleListInfo : array();
                    }
                    
                }
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}