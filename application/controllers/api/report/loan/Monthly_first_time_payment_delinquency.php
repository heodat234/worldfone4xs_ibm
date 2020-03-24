<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Monthly_first_time_payment_delinquency extends WFF_Controller {

    private $collection = "First_time_payment_delinqunecy";
    private $collection_columns = "First_time_payment_delinqunecy_columns";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection = set_sub_collection($this->collection);
        $this->collection_columns = set_sub_collection($this->collection_columns);
    }

    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            // print_r($request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getListProductGroup() {
        try {
            $this->load->library("mongo_private");
            $response = $this->mongo_db->order_by(array('group_code' => 1))->get(set_sub_collection('Product_group'));
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getColumns($for_year) {
        try {
            $temp = $this->crud->where(array('for_year' => (string)$for_year))->get($this->collection_columns);
            $response = array_column($temp, 'columns', 'prod_group_code');
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function exportExcel() {
        $year = date("Y");
        $file_path = UPLOAD_PATH . "loan/export/First_time_payment_delinquency_incidence_rate_transition_" . $year . ".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}