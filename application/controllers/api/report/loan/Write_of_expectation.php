<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Write_of_expectation extends WFF_Controller {

    private $collection = "Write_of_report";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
        $date = date('d-m-Y',strtotime("-1 days"));
        $this->date = strtotime($date);
    }

    function write_of()
    {
        try {
            
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "stt", "dir" => "asc"));
            $match = array('createdAt' => ['$gte' => $this->date]);
            $response = $this->crud->read($this->collection, $request,[],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function card()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "stt", "dir" => "asc"));
            $match = array('type' => 'card','createdAt' => array('$gte' => $this->date));
            $response = $this->crud->read($this->collection, $request,[],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function save()
    {
        shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/savewriteOf.py  > /dev/null &');
    }
    function exportWriteOf()
    {
        try {
            //sibs
         
            $response = $this->crud->read($this->collection);
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


            $filename = "WRITE OF EXPECTATION REPOR.xlsx";
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
            ->setCreator("South Telecom")
            ->setLastModifiedBy("Son Vu")
            ->setTitle("WRITE OF EXPECTATION REPORT")
            ->setSubject("WRITE OF EXPECTATION REPORT")
            ->setDescription("Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Report");

            $worksheet = $spreadsheet->getSheet(0);
            $worksheet->setTitle('WRITE OF EXPECTATION');
            $fieldToCol = array();
            // Title row
            $row = 1;
            
            $worksheet->setCellValue("B1", "GROUP");
            $worksheet->getColumnDimension('B')->setAutoSize(true);
            $worksheet->setCellValue("C1", "NAME");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("D1", "DUE DATE");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("E1", "RELEASE DATE");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("F1", "RELEASE AMOUNT");
            $worksheet->getColumnDimension('A')->setAutoSize(true);
            $worksheet->setCellValue("G1", "INTEREST RATE");
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
                 
                    $worksheet->setCellValue("B".$row, $value['Group']);
                    $worksheet->setCellValue("C".$row, $value['Name']);
                    $worksheet->setCellValueExplicit('D' . $row, $value['Account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet->setCellValue("E".$row, $value['Release_date']);
                    $worksheet->setCellValue("F".$row, $value['Interest_rate']);
                    $worksheet->setCellValue("G".$row, number_format($value['Loan_Term']));
                    $worksheet->setCellValue("H".$row, $value['Off_balance']);
                    $row++;
                }
            }
            if (($total%$limit) > 0) {
                $request    = array("take" => $limit, "skip" => $count*$limit);
                $response = $this->crud->read($this->collection, $request,[],$match);
                foreach ($response['data'] as &$value) {
                    
                    $worksheet->setCellValue("B".$row, $value['Group']);
                    $worksheet->setCellValue("C".$row, $value['Name']);
                    $worksheet->setCellValueExplicit('D' . $row, $value['Account_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet->setCellValue("E".$row, $value['Release_date']);
                    $worksheet->setCellValue("F".$row, $value['Interest_rate']);
                    $worksheet->setCellValue("G".$row, number_format($value['Loan_Term']));
                    $worksheet->setCellValue("H".$row, $value['Off_balance']);
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