<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

Class Outsoucing_collection_trend_report extends WFF_Controller {

    private $collection = "Cus_assigned_partner";

    public function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
		$this->load->library("mongo_db");
		$this->load->library("excel");
		$this->load->library("mongo_private");
        $this->collection = set_sub_collection($this->collection);
        $date = date('Y-m-d');
        $sdate = date('Y-01-01');
        $edate = date('Y-m-d');
        $this->date = strtotime($date);
        $this->sdate = strtotime($sdate);
        $this->edate = strtotime($edate);
    }

    public function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "stt", "dir" => "asc"));
            $match = array('created_at' => ['$gte' => $this->sdate, '$lte' => $this->edate]);
            $response = $this->crud->read($this->collection, $request,[],$match);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function saveReport()
    {
        //shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveMasterData.py  > /dev/null &');
    }

    public function exportExcel()
    {

    }

    public function downloadExcel()
    {

    }

}