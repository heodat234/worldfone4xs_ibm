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
    function index()
    {
        try {
            $request    = json_decode($this->input->get("q"), TRUE);
            $getDate    = getdate(strtotime("-1 days"));
            $match      = array('month' => $getDate['month'] );
            $response   = $this->crud->read($this->collection, $request,array(),$match);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function weekOfMonth($dateString) {
      list($year, $month, $mday) = explode("-", $dateString);
      $firstWday = date("w",strtotime("$year-$month-1"));
      return floor(($mday + $firstWday - 1)/7) + 1;
    }


    function exportExcel()
    {
        $startDay = date('Y-m-1');
        $getDate = getdate();
        $month = $getDate['month'];
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
                $worksheetCard->setCellValue($col . $row, $doc['total_org']);
                $colNo      = $this->excel->stringFromColumnIndex($colIndex+1);
                $colRatio   = $this->excel->stringFromColumnIndex($colIndex+2);
                $worksheetCard->setCellValue($colNo . $row, $doc['count_data']);
                if (isset($doc['ratio'])) {
                    $worksheetCard->setCellValue($colRatio . $row, $doc['ratio']);
                }
            }

        }
        $file_path = UPLOAD_PATH . "loan/export/" . $filename;
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelWorkbook, $inputFileType);
        $objWriter->save($file_path);
        return $file_path;



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

    function downloadExcel()
    {
        $file_path = $this->exportExcel();
        // $file_path = UPLOAD_PATH . "loan/export/CARD_LOAN_GROUP_REPORT_DAILY.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}