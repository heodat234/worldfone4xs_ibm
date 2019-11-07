<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Daily_balance_report extends WFF_Controller {

    private $collection = "LNJC05";
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
    // function readExcel()
    // {
    //     try {
            
    //         $filename = "export.xlsx";
    //         $file_template = "templateLawsuit.xlsx";

    //         $rowDataRaw = $this->excel->read(UPLOAD_PATH . "excel/" . $filename, 50, 1);
            
    //         echo json_encode($rowDataRaw);
    //         // var_dump($response);
    //     } catch (Exception $e) {
    //         echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    //     }
    // }
    function saveExportSIBS()
    {
        $now = (int)date('d');

        $model = $this->crud->build_model($this->collection);
        $this->load->library("kendo_aggregate", $model);
        $this->kendo_aggregate->set_default("sort", null);

        $group = array(
           '$group' => array(
              '_id' => array('group'=>'$group_id'),
              "due_date" => array( '$first' => '$due_date' ),
              'start_bl' => array('$sum'=> '$current_balance'),
              'start_no' => array( '$sum'=> 1 )
           )
        );
        
        $this->kendo_aggregate->filtering()->adding( $group);
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
        // foreach ($data as &$value) {
        //     $value['group'] = $value['_id']['group'];
        //     $due_date = getdate($value['due_date']); 
        //     if ($now == $due_date['mday'] + 1) {
        //         $value['month'] = $due_date['month'];
        //         $value['daily_bl'] = $value['start_bl'];
        //         $value['daily_no'] = $value['start_no'];
        //         $value['result_end_bl'] = 0;
        //         $value['result_end_no'] = 0;
        //         $value['accumulated_bl'] = 0;
        //         $value['accumulated_no'] = 0;
        //         $value['ratio_with_target_bl'] = '0.0%';
        //         $value['ratio_with_target_no'] = '0.0%';
        //         $value['ratio_with_start_bl'] = '0.0%';
        //         $value['ratio_with_start_no'] = '0.0%';
        //     }else{
        //         $value['month'] = $due_date['month'];
        //         $value['daily_bl'] = $value['start_bl'];
        //         $value['month'] = $due_date['month'];
        //         $value['daily_bl'] = $value['start_bl'];
        //         $value['daily_no'] = $value['start_no'];
        //         $value['result_end_bl'] = 0;
        //         $value['result_end_no'] = 0;
        //         $value['accumulated_bl'] = 0;
        //         $value['accumulated_no'] = 0;
        //         $value['ratio_with_target_bl'] = '0.0%';
        //         $value['ratio_with_target_no'] = '0.0%';
        //         $value['ratio_with_start_bl'] = '0.0%';
        //         $value['ratio_with_start_no'] = '0.0%';
        //     }
        //     $value['date'] = $now;
            
            
        //     unset($value['_id'],$value['due_date']);
        // }
        print_r($data);
    }
}