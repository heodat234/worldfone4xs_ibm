<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Clear_small_report extends WFF_Controller {

    private $collection = "Clear_small_daily_report";
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
            $request['sort'] = array(array("field" => "No", "dir" => "asc"));
            $date = date('d-m-Y',strtotime("today"));
            $match = array('createdAt' => array('$gte' => strtotime($date)));
            $data = $this->crud->read($this->collection, $request,array(),$match);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }   
    }

    function saveReport()
    {
      shell_exec('/usr/local/bin/python3.6 /data/worldfone4xs/cronjob/python/Loan/clearSmallDaily.py  > /dev/null &');
      echo json_encode(array("status" => 1, "data" => []));
    }

    function exportExcel() {
        $now = getdate();
        $date = date('d-m-Y',strtotime("-1 days"));
        // $month = (string)$now['mon'];
        $request = json_decode($this->input->get("q"), TRUE);
        $request = array('createdAt' => array('$gte' => strtotime($date)));
        $data = $this->crud->where($request)->order_by(array('index' => 'asc'))->get($this->collection,array('Account_No','cus_name','Amount','Income','Expense','Group','Product','Empty_column'));
        // print_r($data);exit;
        $spreadsheet = new Spreadsheet();
    	$spreadsheet->getProperties()
	    ->setCreator("South Telecom")
	    ->setLastModifiedBy("Son Vu")
	    ->setTitle("Clear small daily report")
	    ->setSubject("Report")
	    ->setDescription("Office 2007 XLSX, generated using PHP classes.")
	    ->setKeywords("office 2007 openxml php")
        ->setCategory("Report");

        $style = array(
            'alignment'     => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER),
            'allborders'    => array(
                'style'     => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'     => array('rgb' => '000000')
            )
        );

        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);
        $worksheet->getDefaultColumnDimension()->setWidth(12);

        $worksheet->mergeCells('A1:I1');
        $worksheet->setCellValue('A1', 'List of settement request (List yêu cầu xử lý)');
        $worksheet->getStyle("A1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $worksheet->getStyle("A1")->applyFromArray($style);

        $worksheet->mergeCells('A2:A3');
        $worksheet->setCellValue('A2', 'No');
        $worksheet->getStyle("A2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("A2")->applyFromArray($style);

        $worksheet->mergeCells('B2:B3');
        $worksheet->setCellValue('B2', 'Account No.');
        $worksheet->getStyle("B2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("B2")->applyFromArray($style);

        $worksheet->mergeCells('C2:C3');
        $worksheet->setCellValue('C2', 'Customer name');
        $worksheet->getStyle("C2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("C2")->applyFromArray($style);

        $worksheet->mergeCells('D2:D3');
        $worksheet->setCellValue('D2', 'Amount');
        $worksheet->getStyle("D2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("D2")->applyFromArray($style);

        $worksheet->mergeCells('E2:F2');
        $worksheet->setCellValue('E2', 'Accounting entry');
        $worksheet->getStyle("E2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("E2")->applyFromArray($style);

        $worksheet->setCellValue('E3', 'Income');
        $worksheet->getStyle("E3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("E3")->applyFromArray($style);

        $worksheet->setCellValue('F3', 'Expense');
        $worksheet->getStyle("F3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("F3")->applyFromArray($style);

         $worksheet->mergeCells('G2:H2');
        $worksheet->setCellValue('G2', 'Remark');
        $worksheet->getStyle("G2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("G2")->applyFromArray($style);

        $worksheet->setCellValue('G3', 'Group');
        $worksheet->getStyle("G3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("G3")->applyFromArray($style);

        $worksheet->setCellValue('H3', 'Product');
        $worksheet->getStyle("H3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("H3")->applyFromArray($style);

        

        $worksheet->mergeCells('I2:I3');
        $worksheet->setCellValue('I2', '');
        $worksheet->getStyle("I2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EDEDED');

        foreach(range('A','I') as $columnID) {
            $worksheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $headerStyle = array(
            'font'          => array(
                'bold'      => true,
            ),
            'alignment'     => array(
                'wrapText'  => true
            )
        );

        $worksheet->getStyle("A1:I3")->applyFromArray($headerStyle);

        $start_row = 4;
        $index = 1;
        foreach($data as $key => $value) {
                        
            $worksheet->setCellValue('A' . $start_row, $index++);
            $worksheet->setCellValueExplicit('B' . $start_row, $value['Account_No'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue('C' . $start_row, $value['cus_name']);
            $worksheet->setCellValueExplicit('D' . $start_row, $value['Amount'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            // $worksheet->setCellValue('D' . $start_row, number_format($value['Amount'])); 
            $worksheet->setCellValue('E' . $start_row, $value['Income']); 

            $worksheet->setCellValue('F' . $start_row, $value['Expense']);
            $worksheet->setCellValue('G' . $start_row, $value['Group']);
            $worksheet->setCellValue('H' . $start_row, $value['Product']);
            $worksheet->setCellValue('I' . $start_row, $value['Empty_column']);
            
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:I".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "loan/export/" . 'Clear_Small_Daily_Report.xlsx';
		$writer->save($file_path);
		echo json_encode(array("status" => 1, "data" => $file_path));
    }

    function stringFromColumnIndex($columnIndex) {
        return $this->excel->stringFromColumnIndex($columnIndex);
    }

    function debtGroupDueDate() {
		ini_set("display_errors", 1);
		ini_set("display_startup_errors", 1);
		error_reporting(E_ALL);
		$data = array();
		$this->load->library("mongo_private");
		$debtGroupRaw = $this->mongo_private->where(array('tags' => array('debt', 'group')))->getOne($this->sub . "Jsondata");
		$dueDateRaw = $this->mongo_private->where(array('tags' => array('debt', 'duedate')))->getOne($this->sub . "Jsondata");
		if(!empty($debtGroupRaw['data']) && !empty($dueDateRaw['data'])) {
			$tempDebtGroupRaw = $debtGroupRaw['data'];
			$tempDueDateRaw = $dueDateRaw['data'];
			$debtGroup = array_column($tempDebtGroupRaw, 'text');
			$dueDate = array_column($tempDueDateRaw, 'text');
			asort($debtGroup);
			asort($dueDate);
			foreach($debtGroup as $group) {
				foreach($dueDate as $duedate) {
					array_push($data, $group . $duedate);
				}
			}
		}
		return $data;
	}
}