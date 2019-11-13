<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Loan_group_report extends WFF_Controller {

    private $zaccf_collection = "ZACCF";
    private $sbv_collection = "SBV";
    private $collection = "Loan_group_report";
    private $group_collection = "Group_card";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->zaccf_collection = set_sub_collection($this->zaccf_collection);
        $this->sbv_collection = set_sub_collection($this->sbv_collection);
        $this->collection = set_sub_collection($this->collection);
        $this->group_collection = set_sub_collection($this->group_collection);
    }

    function weekOfMonth($dateString) {
      list($year, $month, $mday) = explode("-", $dateString);
      $firstWday = date("w",strtotime("$year-$month-1"));
      return floor(($mday + $firstWday - 1)/7) + 1;
    }

    function save()
    {
        try {
            $now =getdate();
            $week = $this->weekOfMonth(date('Y-m-d'));
            
            //sibs
            $request = json_decode($this->input->get("q"), TRUE);
            $model = $this->crud->build_model($this->zaccf_collection);
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_default("sort", null);

            $match = array(
              '$match' => array('W_ORG' => array('$gt' => 0))
            );
            $group = array(
               '$group' => array(
                  '_id' => '$ODIND_FG',
                  'total_org' => array('$sum'=> '$W_ORG'),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match,$group);
            
            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->zaccf_collection, $data_aggregate);
            $sum_org = $sum_acc = $sum_org_g2 = $sum_acc_g2 = $sum_org_g3 = $sum_acc_g3 = 0;
            foreach ($data as $value) {
                if ($value['_id'] != null) {
                    $sum_org += $value['total_org'];
                    $sum_acc += $value['count_data'];
                    if ($value['_id'] != 'A') {
                        $sum_org_g2 += $value['total_org'];
                        $sum_acc_g2 += $value['count_data'];
                    }
                    if ($value['_id'] != 'A' || $value['_id'] != 'B') {
                        $sum_org_g3 += $value['total_org'];
                        $sum_acc_g3 += $value['count_data'];
                    }
                }
            }
            foreach ($data as &$value) {
                switch ($value['_id']) {
                    case 'A':
                        $value['group']      = '1';
                        break;
                    case 'B':
                        $value['group']      = '2';
                        break;
                    case 'C':
                        $value['group']      = '3';
                        break;
                    case 'D':
                        $value['group']      = '4';
                        break;
                    case 'E':
                        $value['group']      = '5';
                        break;
                    default:
                        break;
                }
                $value['ratio']      = (int)$value['total_org']/$sum_org;
                $value['year']       = (string)$now['year'];
                $value['month']      = $now['month'];
                $value['weekday']    = $now['weekday'];
                $value['day']        = date('d/m/Y');
                $value['weekOfMonth']       = $week;
                $value['type']        = 'sibs';
                $value["createdBy"]   =   $this->session->userdata("extension");
                unset($value['_id']);
                $result = $this->crud->create($this->collection, $value);
                
            }
            $insertTotal = array(
                'year'          => (string)$now['year'],
                'month'         => $now['month'],
                'weekday'       => $now['weekday'], 
                'day'           => date('d/m/Y'),
                'weekOfMonth'   => $week,
                'type'          => 'sibs',
                'createdBy'     => $this->session->userdata("extension"),
            );
            $insertTotal['group']       = 'Total';
            $insertTotal['total_org']   = $sum_org;
            $insertTotal['count_data']  = $sum_acc;
            $this->crud->create($this->collection, $insertTotal);
            $insertTotal['group']       = 'G2';
            $insertTotal['total_org']   = $sum_org_g2;
            $insertTotal['count_data']  = $sum_acc_g2;
            $this->crud->create($this->collection, $insertTotal);
            $insertTotal['group']       = 'G3';
            $insertTotal['total_org']   = $sum_org_g3;
            $insertTotal['count_data']  = $sum_acc_g3;
            $this->crud->create($this->collection, $insertTotal);

            
            //card
            $request = json_decode($this->input->get("q"), TRUE);
            $model = $this->crud->build_model($this->sbv_collection);
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_default("sort", null);

            $match = array(
              '$match' => array(
                 '$or' => array(
                    array('ob_principal_sale'=> array( '$gt'=> 0)),
                    array('ob_principal_cash'=> array( '$gt'=> 0))
                 )
              )
            );
            $group = array(
               '$group' => array(
                  '_id' => '$first_due_group',
                  'total_ob_principal_sale' => array('$sum'=> '$ob_principal_sale'),
                  'total_ob_principal_cash' => array('$sum'=> '$ob_principal_cash'),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match,$group);
            
            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->sbv_collection, $data_aggregate);
            $sum_org = $sum_acc = $sum_org_g2 = $sum_acc_g2 = $sum_org_g3 = $sum_acc_g3 = 0;
            foreach ($data as &$value) {
                if (isset($value['_id'][0])) {
                    $value['total_org']  = $value['total_ob_principal_sale'] + $value['total_ob_principal_cash'];
                    $sum_org += $value['total_org'];
                    $sum_acc += $value['count_data'];
                    if ($value['_id'] != '1') {
                        $sum_org_g2 += $value['total_org'];
                        $sum_acc_g2 += $value['count_data'];
                    }
                    if ($value['_id'] != '1' || $value['_id'] != '2') {
                        $sum_org_g3 += $value['total_org'];
                        $sum_acc_g3 += $value['count_data'];
                    }
                }
            }
            foreach ($data as &$value) {
                $value['group']      = isset($value['_id'][0]) ? $value['_id'][0] : 0;
                $value['ratio']      = (int)$value['total_org']/$sum_org;
                $value['year']       = (string)$now['year'];
                $value['month']      = $now['month'];
                $value['weekday']    = $now['weekday'];
                $value['day']        = date('d/m/Y');
                $value['weekOfMonth']       = $week;
                $value['type']        = 'card';
                $value["createdBy"]   =   $this->session->userdata("extension");
                unset($value['_id'],$value['total_ob_principal_sale'],$value['total_ob_principal_cash']);
                if ($value['group'] != 0) {
                    $result = $this->crud->create($this->collection, $value);
                }
            }
            $insertTotal = array(
                'year'          => (string)$now['year'],
                'month'         => $now['month'],
                'weekday'       => $now['weekday'], 
                'day'           => date('d/m/Y'),
                'weekOfMonth'   => $week,
                'type'          => 'card',
                'createdBy'     => $this->session->userdata("extension"),
            );
            $insertTotal['group']       = 'Total';
            $insertTotal['total_org']   = $sum_org;
            $insertTotal['count_data']  = $sum_acc;
            $this->crud->create($this->collection, $insertTotal);
            $insertTotal['group']       = 'G2';
            $insertTotal['total_org']   = $sum_org_g2;
            $insertTotal['count_data']  = $sum_acc_g2;
            $this->crud->create($this->collection, $insertTotal);
            $insertTotal['group']       = 'G3';
            $insertTotal['total_org']   = $sum_org_g3;
            $insertTotal['count_data']  = $sum_acc_g3;
            $this->crud->create($this->collection, $insertTotal);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    
    function exportExcel()
    {
        $startDay = date('Y-m-1');
        $getDate = getdate();
        $month = $getDate['mon'];
        $response = $this->crud->read($this->collection, array('take' => 1000,'skip' => 0),'', array('month' => $getDate['month'] ));
        if (isset($response['data'])) {
            $data = $response['data'];
        }
        $filename = "CARD_LOAN_GROUP_REPORT_DAILY.xlsx";
        $file_template = "CARD_LOAN_GROUP_REPORT_DAILY_TEMPLATE.xlsx";

        //  Tiến hành đọc file excel
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(UPLOAD_PATH . "loan/template/" . $file_template);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $excelWorkbook = $reader->load(UPLOAD_PATH . "loan/template/" . $file_template);

        //sibs
        $worksheet = $excelWorkbook->setActiveSheetIndex(0);
        for ($i=0; $i <= 31; $i++) { 
            $cenvertedTime = date('Y-m-d',strtotime('+'.$i.' day',strtotime($startDay)));
            $getNextDate = getdate(strtotime($cenvertedTime));
            if ($getNextDate['mon'] != $month) {
                break;
            }
            $weekday = $getNextDate['weekday'];
            $weekOfMonth = $this->weekOfMonth($cenvertedTime);
            $getColRow  = $this->getColRow($weekOfMonth,$weekday);
            $col        = $getColRow['col'];
            $row        = $getColRow['row'];
            $nextDate = date('d/m/Y',strtotime($cenvertedTime));
            $worksheet->setCellValue($col . $row, $nextDate);
        }
        foreach ($data as $doc) {
            if ($doc['type'] == 'sibs') {
                $worksheet->setCellValueExplicit("A2", $doc['month'].'-'.$doc['year'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $getColRow  = $this->getColRow($doc['weekOfMonth'],$doc['weekday']);
                $col        = $getColRow['col'];
                $row        = $getColRow['row'];
                $colIndex   = $getColRow['colIndex'];
                $worksheet->setCellValueExplicit($col . $row, $doc['day'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                if ($doc['group'] == '1') {
                    $row += 1;
                }else if ($doc['group'] == '2') {
                    $row += 2;
                }else if ($doc['group'] == '3') {
                    $row += 3;
                }else if ($doc['group'] == '4') {
                    $row += 4;
                }else if ($doc['group'] == '5') {
                    $row += 5;
                }else if ($doc['group'] == 'Total') {
                    $row += 6;
                }else if ($doc['group'] == 'G2') {
                    $row += 7;
                }else if ($doc['group'] == 'G3') {
                    $row += 8;
                }
                $worksheet->setCellValueExplicit($col . $row, $doc['total_org'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $colNo      = $this->excel->stringFromColumnIndex($colIndex+1);
                $colRatio   = $this->excel->stringFromColumnIndex($colIndex+2);
                $worksheet->setCellValueExplicit($colNo . $row, $doc['count_data'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                if (isset($doc['ratio'])) {
                    $worksheet->setCellValueExplicit($colRatio . $row, $doc['ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                }
            }
            
        }

        //card
        $worksheetCard = $excelWorkbook->setActiveSheetIndex(1);

        for ($i=0; $i <= 31; $i++) { 
            $cenvertedTime = date('Y-m-d',strtotime('+'.$i.' day',strtotime($startDay)));
            $getNextDate = getdate(strtotime($cenvertedTime));
            if ($getNextDate['mon'] != $month) {
                break;
            }
            $weekday = $getNextDate['weekday'];
            $weekOfMonth = $this->weekOfMonth($cenvertedTime);
            $getColRow  = $this->getColRow($weekOfMonth,$weekday);
            $col        = $getColRow['col'];
            $row        = $getColRow['row'];
            $nextDate = date('d/m/Y',strtotime($cenvertedTime));
            $worksheetCard->setCellValue($col . $row,$nextDate);
        }
        foreach ($data as $doc) {
            if ($doc['type'] == 'card') {
                $worksheetCard->setCellValueExplicit("A2", $doc['month'].'-'.$doc['year'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $getColRow  = $this->getColRow($doc['weekOfMonth'],$doc['weekday']);
                $col        = $getColRow['col'];
                $row        = $getColRow['row'];
                $colIndex   = $getColRow['colIndex'];
                $worksheetCard->setCellValueExplicit($col . $row, $doc['day'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                if ($doc['group'] == '1') {
                    $row += 1;
                }else if ($doc['group'] == '2') {
                    $row += 2;
                }else if ($doc['group'] == '3') {
                    $row += 3;
                }else if ($doc['group'] == '4') {
                    $row += 4;
                }else if ($doc['group'] == '5') {
                    $row += 5;
                }else if ($doc['group'] == 'Total') {
                    $row += 6;
                }else if ($doc['group'] == 'G2') {
                    $row += 7;
                }else if ($doc['group'] == 'G3') {
                    $row += 8;
                }
                $worksheetCard->setCellValueExplicit($col . $row, $doc['total_org'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $colNo      = $this->excel->stringFromColumnIndex($colIndex+1);
                $colRatio   = $this->excel->stringFromColumnIndex($colIndex+2);
                $worksheetCard->setCellValueExplicit($colNo . $row, $doc['count_data'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                if (isset($doc['ratio'])) {
                    $worksheetCard->setCellValueExplicit($colRatio . $row, $doc['ratio'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                }
            }
            
        }
        $file_path = UPLOAD_PATH . "loan/export/" . $filename;
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelWorkbook, $inputFileType);
        $objWriter->save($file_path);
        echo json_encode(array("status" => 1, "data" => $file_path));



    }

    function test()
    {
        $startDay = date('Y-m-1');
        $getDate = getdate();
        $month = $getDate['mon'];
        for ($i=0; $i <= 31; $i++) { 
            $cenvertedTime = date('Y-m-d',strtotime('+'.$i.' day',strtotime($startDay)));
            $getNextDate = getdate(strtotime($cenvertedTime));
            if ($getNextDate['mon'] != $month) {
                break;
            }
            $weekday = $getNextDate['weekday'];
            $weekOfMonth = $this->weekOfMonth($cenvertedTime);
            $getColRow  = $this->getColRow($weekOfMonth,$weekday);
            $col        = $getColRow['col'];
            $row        = $getColRow['row'];
            $nextDate = date('d/m/Y',strtotime($cenvertedTime));
            print_r($getColRow);
        }
        
    }
    function getColRow($weekOfMonth,$weekday)
    {
        $row = $colIndex = 0;
        $col = '';
        switch ($weekOfMonth) {
            case  1:
                $row = 4;
                break;
            case  2:
                $row = 13;
                break;
            case  3:
                $row = 22;
                break;
            case  4:
                $row = 31;
                break;
            case  5:
                $row = 40;
                break;
            default:
                break;
        }
        switch ($weekday) {
            case  'Saturday':
                $col = 'C';
                $colIndex = 3;
                break;
            case  'Sunday':
                $col = 'F';
                $colIndex = 6;
                break;
            case  'Monday':
                $col = 'I';
                $colIndex = 9;
                break;
            case  'Tuesday':
                $col = 'L';
                $colIndex = 12;
                break;
            case  'Wednesday':
                $col = 'O';
                $colIndex = 15;
                break;
            case  'Thursday':
                $col = 'R';
                $colIndex = 18;
                break;
            case  'Friday':
                $col = 'U';
                $colIndex = 21;
                break;
            default:
                break;
        }
        $data = array('col' => $col, 'colIndex' => $colIndex, 'row' => $row );
        return $data;
    }
    
}