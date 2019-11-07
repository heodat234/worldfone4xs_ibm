<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Daily_product_user_report extends WFF_Controller {

    private $collection = "LNJC05";
    private $model_collection = "Model";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->collection = set_sub_collection($this->collection);

    }

    function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function saveReport()
    {
        $model = $this->crud->build_model($this->collection);
        $this->load->library("kendo_aggregate", $model);
        $this->kendo_aggregate->set_default("sort", null);

        $group = array(
           '$group' => array(
              '_id' => array('code'=>'$tl_code'),
              "name" => array( '$first' => '$tl_name' ),
              'count_appointment' => array('$sum'=> 1),
              'status' => array( '$push'=> '$status' )
           )
        );
        $this->kendo_aggregate->filtering()->adding( $group);
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
    }
}