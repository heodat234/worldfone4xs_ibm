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
            // $date = date('d-m-Y',strtotime("-1 days"));
            
            // $match = array('createdAt' => array('$gte' => strtotime($date)));
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function saveReport()
    {
      shell_exec('/usr/local/bin/python3.6 /data/worldfone4xs/cronjob/python/Loan/saveDailyPayment.py  > /dev/null &');
      echo json_encode(array("status" => 1, "data" => []));
    }

    function exportExcel()
    {
        shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/exportDailyPayment.py  > /dev/null &');
    }
    function downloadExcel()
    {
        $date = $this->input->post('date');
        $date = getdate(strtotime(str_replace('/', '-', $date)));
        $day = $date['mday'];
        $month = $date['mon'];
        if ($date['mday'] < 10) {
            $day = '0'.(string)$date['mday'];
        }
        if ($date['mon'] < 10) {
            $month = '0'.(string)$date['mon'];
        }
        $file_path = UPLOAD_PATH . "loan/export/DailyPayment_". $day.$month.$date['year'] .".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}