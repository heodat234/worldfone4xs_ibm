<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Database extends WFF_Controller {

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->username = $this->config->item("session_mongo_user");
        $this->password = $this->config->item("session_mongo_password");
    }

    function data($database, $collection)
    {
        $this->load->library("mongo_db");
        $this->mongo_db->switch_db($database);
        $request = json_decode($this->input->get("q"), TRUE);

        // Kendo to aggregate
        $this->load->library("kendo_aggregate");
        $this->kendo_aggregate->set_kendo_query($request)->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($collection, $data_aggregate);
        // Result
        $response = array("data" => $data, "total" => $total);

        echo json_encode($response);
    }
}