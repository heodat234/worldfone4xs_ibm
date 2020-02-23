<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Monthly_delinquent_occurence_transaction extends WFF_Controller {

    private $collection = "Monthly_delinquent_occurence_transition";
    private $collection_total = "Monthly_delinquent_occurence_transition_total";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection = set_sub_collection($this->collection);
        $this->collection_total = set_sub_collection($this->collection_total);
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
            $this->load->library("mongo_private");
            $temp = $this->mongo_private->where(array('tags' => array('product', 'group')))->getOne(set_sub_collection('Jsondata'));
            $response = (!empty($temp['data'])) ? $temp['data'] : array();
            sort($response);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}