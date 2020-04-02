<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Reminder_letter_report extends WFF_Controller {

    private $collection = "Reminder_letter_report";
    private $model_collection = "Model";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->load->library("mongo_private");
        $this->collection = set_sub_collection($this->collection);

    }

    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $data = $this->crud->read($this->collection, $request);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }



    function stringFromColumnIndex($columnIndex) {
        return $this->excel->stringFromColumnIndex($columnIndex);
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
        $file_path = UPLOAD_PATH . "loan/export/Reminder Letter Report_". $day.$month.$date['year'] .".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}