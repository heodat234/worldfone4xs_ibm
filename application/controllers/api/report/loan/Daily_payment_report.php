<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Daily_payment_report extends WFF_Controller {

    private $collection = "Daily_payment_report";
    private $ln3206_collection = "LN3206F";
    private $zaccf_collection = "ZACCF";
    private $lnjc05_collection = "LNJC05";
    private $product_collection = "Product";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection = set_sub_collection($this->collection);
        $this->ln3206_collection = set_sub_collection($this->ln3206_collection);
        $this->zaccf_collection = set_sub_collection($this->zaccf_collection);
        $this->lnjc05_collection = set_sub_collection($this->lnjc05_collection);

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

    function saveExport()
    {
      shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveDailyPayment.py  > /dev/null &');
        // $this->crud->delete($this->collection);
        // $request    = array("take" => 10000, "skip" => 0);
        // $response = $this->crud->read($this->ln3206_collection,$request);
        // $data = $response['data'];
        // foreach ($data as &$value) {
        //     if (!isset($value['account'])) {
        //         continue;
        //     }
        //     $zaccf = $this->mongo_db->where("ACC_ID", $value["account"])->select(array('CUS_NM','rpy_prn','RPY_INT','RPY_FEE','PRODGRP_ID'))->getOne($this->zaccf_collection);
        //     $value['name'] = $zaccf['CUS_NM'];
        //     $value['paid_principal'] = $zaccf['rpy_prn'];
        //     $value['paid_interest'] = $zaccf['RPY_INT'];
        //     $value['RPY_FEE'] = $zaccf['RPY_FEE'];

        //     $result = $this->mongo_db->where("account_number", $value["account"])->select(array('due_date','group_id'))->getOne($this->lnjc05_collection);
        //     $value['due_date'] = $result['due_date'];
        //     $value['group'] = $result['group_id'];

        //     $product = $this->mongo_db->where("code", $zaccf["PRODGRP_ID"])->select(array('name'))->getOne($this->product_collection);
        //     $value['product'] = $product['name'];

        //     if (strlen($value['date']) == 5 ) {
        //         $value['date'] = '0'.$value['date'];
        //     }
        //     if (strlen($value['date']) == 6) {
        //         $date = substr($value['date'], -2).'-'.substr($value['date'], 2,2).'-'.substr($value['date'], 0,2);
        //         $value['payment_date'] = strtotime($date);
        //     }
        //     $due_date = date('y-m-d',$value['due_date']);
        //     $interval = date_diff(date_create($due_date), date_create($date));

        //     $value['num_of_overdue_day'] = $interval->format('%a');
        //     $value['pic'] = '';
        //     $value['note'] = '';
        //     $value["created_by"] = $this->session->userdata("extension");
        //     unset($value['branch'],$value['currency'],$value['code'],$value['id'],$value['date']);

        //     $this->crud->create($this->collection, $value);
        // }
        // print_r($data);
    }
}