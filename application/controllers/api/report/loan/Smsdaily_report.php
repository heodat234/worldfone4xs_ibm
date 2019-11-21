<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Smsdaily_report extends WFF_Controller {

    private $collection = "LNJC05";
    private $card_collection = "Account";
    private $model_collection = "Model";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->collection = set_sub_collection($this->collection);
        $this->card_collection = set_sub_collection($this->card_collection);
    }

    function sibs()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            $data = array();
            foreach ($response['data'] as &$value) {
               if ( ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] > 40000) || ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] < 40000 && $value['installment_type'] == 'n') ){
                  array_push($data, $value);
               }
            }
            echo json_encode(array('data'=> $data, 'total' => $response['total']));

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function card()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->card_collection, $request);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function saveAsExcel()
    {
        try {
            //sibs
            $request    = array("take" => 1, "skip" => 0);
            $response = $this->crud->read($this->collection, $request);
            $total = $response['total'];

            $limit = 1000;
            $count = (int)($total/$limit);
            $data = array();

            for ($i=0; $i < $count; $i++) { 
                $request    = array("take" => $limit, "skip" => $i*$limit);
                $response = $this->crud->read($this->collection, $request,['overdue_amount_this_month','advance_balance','installment_type','group_id','account_number','mobile_num','cus_name']);
                foreach ($response['data'] as &$value) {
                   if ( ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] > 40000) || ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] < 40000 && $value['installment_type'] == 'n') ){
                      array_push($data, $value);
                   }
                }
            }
            // var_dump($data);exit;
            if (($total%$limit) > 0) {
                $request    = array("take" => $limit, "skip" => $count*$limit);
                $response = $this->crud->read($this->collection, $request,['overdue_amount_this_month','advance_balance','installment_type','group_id','account_number','mobile_num','cus_name']);
                foreach ($response['data'] as &$value) {
                   if ( ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] > 40000) || ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] < 40000 && $value['installment_type'] == 'n') ){
                      array_push($data, $value);
                   }
                }
            }

            //card
            $request_card    = array("take" => 1, "skip" => 0);
            $response_card = $this->crud->read($this->card_collection, $request_card);
            $total_card = $response_card['total'];

            $limit = 1000;
            $count = (int)($total_card/$limit);
            $data_card = array();

            for ($i=0; $i < $count; $i++) { 
                $request_card    = array("take" => $limit, "skip" => $i*$limit);
                $response_card = $this->crud->read($this->card_collection, $request_card,['mobile_num','cus_name','overdue_amt','current_bal','account_number']);
                foreach ($response_card['data'] as &$value) {
                    array_push($data_card, $value);
                }
            }
            if (($total_card%$limit) > 0) {
                $request_card    = array("take" => $limit, "skip" => $count*$limit);
                $response_card = $this->crud->read($this->card_collection, $request_card,['mobile_num','cus_name','overdue_amt','current_bal','account_number']);
                foreach ($response_card['data'] as &$value) {
                    array_push($data_card, $value);
                }
            }

            $filename = "SMS DAILY SMS REPORT.xlsx";
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
            ->setCreator("South Telecom")
            ->setLastModifiedBy("Thanh Hung")
            ->setTitle("SMS DAILY SMS REPORT")
            ->setSubject("SMS DAILY SMS REPORT")
            ->setDescription("Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Report");

            $worksheet = $spreadsheet->getSheet(0);
            $worksheet->setTitle('SMS SIBS');
            $fieldToCol = array();
            // Title row
            $row = 1;
            $worksheet->setCellValue("A1", "NO");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("B1", "GROUP");
            $worksheet->getColumnDimension('B')->setAutoSize(true);
            $worksheet->setCellValue("C1", "ACC");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("D1", "PHONE");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("E1", "NAME");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("F1", "AMOUNT");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("G1", "SENDING DATE");
            $worksheet->getColumnDimension('A')->setAutoSize(true);

            $worksheet->getStyle("A1:G1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
            $style = array('font' => array('bold' => true), 'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
            $worksheet->getStyle("A1:G1")->applyFromArray($style);
            if($data) {
                $row = 2;
                foreach ($data as $value) {
                    $worksheet->setCellValue("A".$row, $row - 1);
                    $worksheet->setCellValue("B".$row, $value['group_id']);
                    $worksheet->setCellValueExplicit('C' . $row, $value['account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet->setCellValue("D".$row, $value['mobile_num']);
                    $worksheet->setCellValue("E".$row, $value['cus_name']);
                    $worksheet->setCellValue("F".$row, number_format($value['overdue_amount_this_month']));
                    $worksheet->setCellValue("G".$row, date('d/m/Y'));
                    $row++;
                }
            }

            //card
            // $spreadsheet->
            $worksheet_card = $spreadsheet->createSheet(1);
            $worksheet_card->setTitle('SMS CARD');
            $fieldToCol = array();
            // Title row
            $worksheet_card->setCellValue("A1", "NO");
            $worksheet_card->getColumnDimension('A')->setAutoSize(true);
            $worksheet_card->setCellValue("B1", "GROUP");
            $worksheet_card->getColumnDimension('B')->setAutoSize(true);
            $worksheet_card->setCellValue("C1", "ACCOUNT NUMBER");
            $worksheet_card->getColumnDimension('C')->setAutoSize(true);
            $worksheet_card->setCellValue("D1", "PHONE");
            $worksheet_card->getColumnDimension('D')->setAutoSize(true);
            $worksheet_card->setCellValue("E1", "NAME");
            $worksheet_card->getColumnDimension('E')->setAutoSize(true);
            $worksheet_card->setCellValue("F1", "OS");
            $worksheet_card->getColumnDimension('F')->setAutoSize(true);
            $worksheet_card->setCellValue("G1", "AMOUNT");
            $worksheet_card->getColumnDimension('G')->setAutoSize(true);
            $worksheet_card->setCellValue("H1", "SENDING DATE");
            $worksheet_card->getColumnDimension('H')->setAutoSize(true);

            $worksheet_card->getStyle("A1:G1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
            $style = array('font' => array('bold' => true), 'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
            $worksheet_card->getStyle("A1:G1")->applyFromArray($style);
            if($data_card) {
                $row = 2;
                foreach ($data_card as $value) {
                    $worksheet_card->setCellValue("A".$row, $row - 1);
                    $worksheet_card->setCellValue("B".$row, 'GROUP');
                    $worksheet_card->setCellValueExplicit('C' . $row, $value['account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet_card->setCellValue("D".$row, $value['mobile_num']);
                    $worksheet_card->setCellValue("E".$row, $value['cus_name']);
                    $worksheet_card->setCellValue("F".$row, $value['overdue_amt']);
                    $worksheet_card->setCellValue("G".$row, $value['current_bal']);
                    $worksheet_card->setCellValue("H".$row, date('d/m/Y'));
                    $row++;
                }
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $file_path = UPLOAD_PATH . "loan/export/" . $filename;
            $writer->save($file_path);
            echo json_encode(array("status" => 1, "data" => $file_path));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}