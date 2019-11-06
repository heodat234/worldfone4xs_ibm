<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Daily_balance_report extends WFF_Controller {

    private $collection = "Lawsuit";
    private $model_collection = "Model";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
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
    function readExcel()
    {
        try {
            
            $filename = "export.xlsx";
            $file_template = "templateLawsuit.xlsx";

            $rowDataRaw = $this->excel->read(UPLOAD_PATH . "excel/" . $filename, 50, 1);
            
            echo json_encode($rowDataRaw);
            // var_dump($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}