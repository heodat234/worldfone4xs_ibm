<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Monthly_bad_debt_province_report extends WFF_Controller {

    private $collection = "Monthly_bad_debt_province";

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
        // shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/exportMasterData.py  > /dev/null &');
        $file_path = UPLOAD_PATH . "loan/export/MasterData.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}