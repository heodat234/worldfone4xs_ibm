<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Loan_group_report extends WFF_Controller {

    private $zaccf_collection = "ZACCF";
    private $sbv_collection = "SBV";
    private $collection = "Loan_group_report";
    private $group_collection = "Group_card";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->zaccf_collection = set_sub_collection($this->zaccf_collection);
        $this->sbv_collection = set_sub_collection($this->sbv_collection);
        $this->collection = set_sub_collection($this->collection);
        $this->group_collection = set_sub_collection($this->group_collection);
    }
    function weekOfMonth($dateString) {
      list($year, $month, $mday) = explode("-", $dateString);
      $firstWday = date("w",strtotime("$year-$month-1"));
      return floor(($mday + $firstWday - 1)/7) + 1;
    }
    function saveSibs()
    {
        try {

            $now =getdate();
            $week = $this->weekOfMonth(date('Y-m-d'));
            
            // print_r($data);
            $request = json_decode($this->input->get("q"), TRUE);
            $model = $this->crud->build_model($this->zaccf_collection);
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_default("sort", null);

            $match = array(
              '$match' => array('W_ORG' => array('$gt' => 0))
            );
            $group = array(
               '$group' => array(
                  '_id' => array('code'=>'$ODIND_FG'),
                  'total_org' => array('$sum'=> '$W_ORG'),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match,$group);
            
            // Get data
            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->zaccf_collection, $data_aggregate);
            foreach ($data as &$value) {
                $value['year']       = $now['year'];
                $value['month']      = $now['month'];
                $value['weekday']    = $now['weekday'];
                $value['day']        = date('d/m/Y');
                $value['weekOfMonth']       = $week;
                $value['type']        = 'sibs';
                $value["createdBy"]   =   $this->session->userdata("extension");
                $result = $this->crud->create($this->collection, $value);
            }
            print_r($data);
            // echo json_encode(array('data'=> $data, 'total' => $response['total']));

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    
    function saveCard()
    {
        try {

            $now =getdate();
            $week = $this->weekOfMonth(date('Y-m-d'));
            
            $request = json_decode($this->input->get("q"), TRUE);
            $model = $this->crud->build_model($this->sbv_collection);
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_default("sort", null);

            $match = array(
              '$match' => array(
                 '$or' => array(
                    array('ob_principal_sale'=> array( '$gt'=> 0)),
                    array('ob_principal_cash'=> array( '$gt'=> 0))
                 )
              )
            );
            $lookup = array(
               '$lookup' => array(
                  "from" => $this->group_collection,
                   "localField" => "delinquency_group",
                   "foreignField" => "group_number",
                   "as" => "group_detail"
               )
            );
            $group = array(
               '$group' => array(
                  '_id' => array('code'=>'$group_detail.group'),
                  'total_org' => array('$sum'=> ['$ob_principal_sale','$ob_principal_cash']),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($group);
            
            // Get data
            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->sbv_collection, $data_aggregate);
            foreach ($data as &$value) {
                $value['year']       = $now['year'];
                $value['month']      = $now['month'];
                $value['weekday']    = $now['weekday'];
                $value['day']        = date('d/m/Y');
                $value['weekOfMonth']       = $week;
                $value['type']        = 'card';
                $value["createdBy"]   =   $this->session->userdata("extension");
                $result = $this->crud->create($this->collection, $value);
            }
            print_r($data);
            // echo json_encode(array('data'=> $data, 'total' => $response['total']));

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    
}