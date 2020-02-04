<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

Class Daily_assignment_report extends WFF_Controller {

    private $collection             = "Daily_assignment_report";
    private $ln3206_collection      = "LN3206F";
    private $zaccf_collection       = "ZACCF";
    private $lnjc05_collection      = "LNJC05";
    private $product_collection     = "Product";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection           = set_sub_collection($this->collection);
        $this->ln3206_collection    = set_sub_collection($this->ln3206_collection);
        $this->zaccf_collection     = set_sub_collection($this->zaccf_collection);
        $this->lnjc05_collection    = set_sub_collection($this->lnjc05_collection);

    }

    function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            // if (isset($request['filter'])) {
            //   $filters = $request['filter'];
            //   unset($request['filter']);
            //   foreach ($filters['filters'] as $value) {
            //     if ($value['operator'] == 'gte') {
            //       $start = $value['value'];
            //     }
            //     if ($value['operator'] == 'lte') {
            //       $end = $value['value'];
            //     }
            //   }
            //   $match = array('createdAt' => array('$gte' =>strtotime($start), '$lte' => strtotime($end)));
            // }else{
            //     $date = date('d-m-Y');
            //     $match = array('createdAt' => array('$gte' => strtotime($date)));
            // }
            
            $response = $this->crud->read($this->collection, $request,array(),$match);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function save()
    {
      shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveDailyAssignment.py  > /dev/null &');
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
        $file_path = UPLOAD_PATH . "loan/export/DailyAssignment_". $day.$month.$date['year'] .".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}