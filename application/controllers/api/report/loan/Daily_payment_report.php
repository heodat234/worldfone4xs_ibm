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

    function save()
    {
      shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveDailyPayment.py  > /dev/null &');
        
    }

    function exportExcel()
    {
        shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/exportDailyPayment.py  > /dev/null &');
    }
    function downloadExcel()
    {
        // shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/exportDailyPayment.py  > /dev/null &');
        $file_path = UPLOAD_PATH . "loan/export/DailyPayment.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}