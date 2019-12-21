<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Monthly_japanese_report extends WFF_Controller {

    private $collection_total = "Collection_factors_total";
    private $collection_detail = "Collection_factors_detail";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection_total = set_sub_collection($this->collection_total);
        $this->collection_detail = set_sub_collection($this->collection_detail);
    }

    function read_total() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection_total, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function read_detail() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection_detail, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}