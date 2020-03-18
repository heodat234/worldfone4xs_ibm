<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Master_data_report extends WFF_Controller {

    private $collection = "Master_data_report";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection = set_sub_collection($this->collection);
        
    }

    function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            // $date = date('d-m-Y');
            
            // $match = array('createdAt' => array('$gte' => strtotime($date)));
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function saveReport()
    {
      shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveMasterData.py  > /dev/null &');
    }

    function exportExcel()
    {
        shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/exportMasterData.py  > /dev/null &');
    }
    function downloadExcel()
    {
        $date = $this->input->post('date');
        $date = getdate(strtotime(str_replace('/', '-', $date)));
        // print_r($date);exit;
        $day = $date['mday'];
        $month = $date['mon'];
        if ($date['mday'] < 10) {
            $day = '0'.(string)$date['mday'];
        }
        if ($date['mon'] < 10) {
            $month = '0'.(string)$date['mon'];
        }
        // sleep(1800);
        $file_path = UPLOAD_PATH . "loan/export/MasterData_". $day.$month.$date['year'] .".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}