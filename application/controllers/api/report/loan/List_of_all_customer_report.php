<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class List_of_all_customer_report extends WFF_Controller {

    private $collection = "List_of_all_customer_report";
    private $product = "LO_Product";
    private $data_total = "LO_List_of_all_customer_total_report";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
        $date = date('d-m-Y');
        $sdate = date('1-m-Y');
        $edate = date('t-m-Y');
        $this->date = strtotime($date);
        $this->sdate = strtotime($sdate);
        $this->edate = strtotime($edate);
    }

    function all_loan_group()
    {
        try {
            
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "stt", "dir" => "asc"));
            $match = array('createdAt' => ['$gte' => $this->sdate, '$lte' => $this->edate]);
            $response = $this->crud->read($this->collection, $request,[],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function product()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "code", "dir" => "asc"));
            $match = array();
            $response = $this->crud->read($this->product, $request,['code','name'],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function data()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "index", "dir" => "asc"));
            $match = array('createdAt' => ['$gte' => $this->sdate, '$lte' => $this->edate]);
            $response = $this->crud->read($this->data_total, $request,[],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function save()
    {
        shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveSmsDaily.py  > /dev/null &');
    }

    function saveReport()
    {
      shell_exec('/usr/local/bin/python3.6 /data/worldfone4xs/cronjob/python/Loan/saveListOfAllCustomer.py  > /dev/null &');
      echo json_encode(array("status" => 1, "data" => []));
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

            // for ($i=0; $i < $count; $i++) { 
            //     $request    = array("take" => $limit, "skip" => $i*$limit);
            //     $response = $this->crud->read($this->collection, $request,[]);
            //     foreach ($response['data'] as &$value) {
            //           array_push($data, $value);
            //     }
            // }
            // if (($total%$limit) > 0) {
            //     $request    = array("take" => $limit, "skip" => $count*$limit);
            //     $response = $this->crud->read($this->collection, $request,[]);
            //     foreach ($response['data'] as &$value) {
            //           array_push($data, $value);
            //     }
            // }


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

            $row = 2;
            $match = array('type' => 'sibs');
            for ($i=0; $i < $count; $i++) { 
                $request    = array("take" => $limit, "skip" => $i*$limit);
                $response = $this->crud->read($this->collection, $request,[],$match);
                foreach ($response['data'] as &$value) {
                    $worksheet->setCellValue("A".$row, $value['stt']);
                    $worksheet->setCellValue("B".$row, $value['group']);
                    $worksheet->setCellValueExplicit('C' . $row, $value['account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet->setCellValue("D".$row, $value['mobile_num']);
                    $worksheet->setCellValue("E".$row, $value['cus_name']);
                    $worksheet->setCellValue("F".$row, number_format($value['amount']));
                    $worksheet->setCellValue("G".$row, $value['sending_date']);
                    $row++;
                }
            }
            if (($total%$limit) > 0) {
                $request    = array("take" => $limit, "skip" => $count*$limit);
                $response = $this->crud->read($this->collection, $request,[],$match);
                foreach ($response['data'] as &$value) {
                    $worksheet->setCellValue("A".$row, $value['stt']);
                    $worksheet->setCellValue("B".$row, $value['group']);
                    $worksheet->setCellValueExplicit('C' . $row, $value['account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet->setCellValue("D".$row, $value['mobile_num']);
                    $worksheet->setCellValue("E".$row, $value['cus_name']);
                    $worksheet->setCellValue("F".$row, number_format($value['amount']));
                    $worksheet->setCellValue("G".$row, $value['sending_date']);
                    $row++;
                }
            }
            

            //card
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

            $row = 2;
            $match = array('type' => 'card');
            for ($i=0; $i < $count; $i++) { 
                $request    = array("take" => $limit, "skip" => $i*$limit);
                $response = $this->crud->read($this->collection, $request,[],$match);
                foreach ($response['data'] as &$value) {
                    $worksheet_card->setCellValue("A".$row, $value['stt']);
                    $worksheet_card->setCellValue("B".$row, $value['group']);
                    $worksheet_card->setCellValueExplicit('C' . $row, $value['account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet_card->setCellValue("D".$row, $value['mobile_num']);
                    $worksheet_card->setCellValue("E".$row, $value['cus_name']);
                    $worksheet_card->setCellValue("F".$row, $value['overdue_amt']);
                    $worksheet_card->setCellValue("G".$row, $value['current_bal']);
                    $worksheet_card->setCellValue("H".$row, $value['sending_date']);
                    $row++;
                }
            }
            if (($total%$limit) > 0) {
                $request    = array("take" => $limit, "skip" => $count*$limit);
                $response = $this->crud->read($this->collection, $request,[],$match);
                foreach ($response['data'] as &$value) {
                    $worksheet_card->setCellValue("A".$row,  $value['stt']);
                    $worksheet_card->setCellValue("B".$row, $value['group']);
                    $worksheet_card->setCellValueExplicit('C' . $row, $value['account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet_card->setCellValue("D".$row, $value['mobile_num']);
                    $worksheet_card->setCellValue("E".$row, $value['cus_name']);
                    $worksheet_card->setCellValue("F".$row, $value['os']);
                    $worksheet_card->setCellValue("G".$row, $value['amount']);
                    $worksheet_card->setCellValue("H".$row, $value['sending_date']);
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

    function downloadExcel()
    {
        $dmonth =str_replace('/','',$_POST['month']);
        // $date = getdate();
        // $day = $date['mday'];
        // $month = $date['mon'];
        // if ($date['mday'] < 10) {
        //     $day = '0'.(string)$date['mday'];
        // }
        // if ($date['mon'] < 10) {
        //     $month = '0'.(string)$date['mon'];
        // }
        $file_path = UPLOAD_PATH . "loan/export/ListofallcustomerReport_". $dmonth .".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}



