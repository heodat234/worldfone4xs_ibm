<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Last_past_year_report extends WFF_Controller {

    private $collection = "Last_past_year_arrears_occurrence_report";

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
            $today      = date('Y-m-d');
            $today        = date("Y-m-t",strtotime(date("Y-m-d", strtotime($today)) . " -1 month"));
            $request    = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "type", "dir" => "asc"),array("field" => "index", "dir" => "asc"));

            
            $getDate    = getdate(strtotime($today));
            $match      =  array('for_month' => $getDate['mon'] );
            $response   = $this->crud->read($this->collection, $request,array(),$match);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }



    function exportExcel()
    {
        $today      = date('Y-m-d');
        // $getDate    = getdate(strtotime($today));
        // $month_end = date('Y-m-t');
        // $getmonthEndDate    = getdate(strtotime($month_end));
        // if ($getDate['mday'] < $getmonthEndDate['mday']) {
        $today        = date("Y-m-t",strtotime(date("Y-m-d", strtotime($today)) . " -1 month"));
        // }
        

        $filename = "Last past year,arrears occurrence table.xlsx";
        $file_template = "Last past year,arrears occurrence table_template.xlsx";

        //  Tiến hành đọc file excel
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(UPLOAD_PATH . "loan/template/" . $file_template);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $excelWorkbook = $reader->load(UPLOAD_PATH . "loan/template/" . $file_template);

        

        $product_code = array('Bike', 'PL','Electro', 'Auto', 'Total');
        foreach ($product_code as $key => $value) {

            $getDate    = getdate(strtotime($today));
            $month      = $getDate['mon'];
            $request    = array('type' => $value, 'for_month' => $month, 'year' => $getDate['year'] );
            $dataBike   = $this->crud->where($request)->order_by(array('index' => 'asc'))->get($this->collection);
            
            $lats_6_month        = strtotime(date("Y-m-t", strtotime($today)) . " -6 month");
            $getlast6Month       = getdate($lats_6_month);
            $month_last_6_month  = $getlast6Month['mon'];
            $request             = array('type' => $value, 'for_month' => $month_last_6_month, 'year' => $getlast6Month['year'] );
            $dataBike6Month   = $this->crud->where($request)->order_by(array('index' => 'asc'))->get($this->collection);

            $last_year              = strtotime(date("Y-m-t", strtotime($today)) . " -1 year");
            $getlastYear            = getdate($last_year);
            $month_last_1_year      = $getlastYear['mon'];
            $request                = array('type' => $value, 'for_month' => $month_last_1_year, 'year' => $getlastYear['year'] );
            $dataBike1Year          = $this->crud->where($request)->order_by(array('index' => 'asc'))->get($this->collection);

            
            $worksheet = $excelWorkbook->setActiveSheetIndex($key);
            
            $worksheet->setCellValueExplicit('A1', 'Base on '. $getDate['mday'] .' '. $getDate['month']. ' '. $getDate['year']. ' data' , \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit('A5', 'Total '. $getDate['year']. '/'. $getDate['mon']. ' data' , \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $rowBike = 5;
            foreach ($dataBike as $doc) {            
                $worksheet->setCellValueExplicit('C' . $rowBike, $doc['sales_period'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                $worksheet->setCellValueExplicit('D' . $rowBike, $doc['total_w_org'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('E' . $rowBike, $doc['total_account'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('F' . $rowBike, $doc['w_org_group_b'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('G' . $rowBike, $doc['account_group_b'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('H' . $rowBike, $doc['group_b_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('I' . $rowBike, $doc['w_org_group_c'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('J' . $rowBike, $doc['account_group_c'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('K' . $rowBike, $doc['group_c_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('L' . $rowBike, $doc['w_org_group_c_over'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('M' . $rowBike, $doc['account_group_c_over'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('N' . $rowBike, $doc['group_c_over_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);            
                $rowBike ++;
            }


            // last_6_month
            $worksheet->setCellValueExplicit('A11', 'Base on '. $getlast6Month['mday'] .' '. $getlast6Month['month']. ' '. $getlast6Month['year']. ' data' , \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit('A12', 'Total '. $getlast6Month['year']. '/'. $getlast6Month['mon']. ' data' , \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $rowBikeLast6Month = 12;
            foreach ($dataBike6Month as $doc) {            
                $worksheet->setCellValueExplicit('C' . $rowBikeLast6Month, $doc['sales_period'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                $worksheet->setCellValueExplicit('D' . $rowBikeLast6Month, $doc['total_w_org'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('E' . $rowBikeLast6Month, $doc['total_account'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('F' . $rowBikeLast6Month, $doc['w_org_group_b'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('G' . $rowBikeLast6Month, $doc['account_group_b'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('H' . $rowBikeLast6Month, $doc['group_b_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('I' . $rowBikeLast6Month, $doc['w_org_group_c'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('J' . $rowBikeLast6Month, $doc['account_group_c'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('K' . $rowBikeLast6Month, $doc['group_c_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('L' . $rowBikeLast6Month, $doc['w_org_group_c_over'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('M' . $rowBikeLast6Month, $doc['account_group_c_over'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('N' . $rowBikeLast6Month, $doc['group_c_over_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);            
                $rowBikeLast6Month ++;
            }



            // last_year
            $worksheet->setCellValueExplicit('A18', 'Base on '. $getlastYear['mday'] .' '. $getlastYear['month']. ' '. $getlastYear['year']. ' data' , \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit('A19', 'Total '. $getlastYear['year']. '/'. $getlastYear['mon']. ' data' , \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $rowBikeLastYear = 19;
            foreach ($dataBike1Year as $doc) {            
                $worksheet->setCellValueExplicit('C' . $rowBikeLastYear, $doc['sales_period'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                $worksheet->setCellValueExplicit('D' . $rowBikeLastYear, $doc['total_w_org'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('E' . $rowBikeLastYear, $doc['total_account'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('F' . $rowBikeLastYear, $doc['w_org_group_b'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('G' . $rowBikeLastYear, $doc['account_group_b'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('H' . $rowBikeLastYear, $doc['group_b_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('I' . $rowBikeLastYear, $doc['w_org_group_c'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('J' . $rowBikeLastYear, $doc['account_group_c'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('K' . $rowBikeLastYear, $doc['group_c_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('L' . $rowBikeLastYear, $doc['w_org_group_c_over'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('M' . $rowBikeLastYear, $doc['account_group_c_over'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet->setCellValueExplicit('N' . $rowBikeLastYear, $doc['group_c_over_ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);            
                $rowBikeLastYear ++;
            }
        }




        $file_path = UPLOAD_PATH . "loan/export/" . $filename;
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelWorkbook, $inputFileType);
        $objWriter->save($file_path);
        return $file_path;



    }

    
    function downloadExcel()
    {
        $file_path = $this->exportExcel();
        // $file_path = UPLOAD_PATH . "loan/export/CARD_LOAN_GROUP_REPORT_DAILY.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}