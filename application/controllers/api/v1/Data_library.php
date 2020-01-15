<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Data_library extends WFF_Controller {

	private $collection = "Datalibrary";
	private $ftpFilename = '';

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
        $this->load->library("Excel");
        $this->load->library('mongo_db');
        $this->load->model('ftp_model');
		$this->collection = set_sub_collection($this->collection);
	}

    function update_import_log($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data['complete_import'] = time();
            $data["updated_by"]  =   $this->session->userdata("extension");
            $data['updated_at'] = time();
            $result = $this->crud->where_id($id)->update(set_sub_collection("Import"), array('$set' => $data));
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}