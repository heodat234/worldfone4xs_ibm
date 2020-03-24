<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

Class Tendency_delinquent extends WFF_Controller {

    private $collection = "Tendency_delinquent";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection = set_sub_collection($this->collection);
    }

    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            // print_r($request);
            $response = $this->crud->read($this->collection, $request);
            // print_r($request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function read_total() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection_total, $request);
            // print_r($request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getListProductGroup() {
        try {
            $response = $this->mongo_db->order_by(array('group_code' => 1))->get(set_sub_collection('Product_group'));
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function exportExcel() {
        $year = date("Y");
        $file_path = UPLOAD_PATH . "loan/export/Tendency_of_delinquent_loan_occurrence_" . $year . ".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }

    public function stringFromColumnIndex($columnIndex)
    {
		$value = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
		return $value;
    }
}