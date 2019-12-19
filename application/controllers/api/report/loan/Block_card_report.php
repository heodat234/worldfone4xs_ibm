<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Block_card_report extends WFF_Controller {

    private $collection = "Block_card_report";
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
            $date = date('d-m-Y',strtotime("-1 days"));
            $match = array('createdAt' => array('$gte' => strtotime($date)));
            $data = $this->crud->read($this->collection, $request,array(),$match);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function exportExcel() {
        $now = getdate();
        $date = date('d-m-Y',strtotime("-1 days"));
        // $month = (string)$now['mon'];
        $request = json_decode($this->input->get("q"), TRUE);
        $request = array('createdAt' => array('$gte' => strtotime($date)));
        $data = $this->crud->where($request)->order_by(array('index' => 'asc'))->get($this->collection,array('index','account_number','name','block','accl','sibs','group','createdAt'));
        // print_r($data);exit;
        $spreadsheet = new Spreadsheet();
    	$spreadsheet->getProperties()
	    ->setCreator("South Telecom")
	    ->setLastModifiedBy("Thanh Hung")
	    ->setTitle("Block Card Report")
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

        $worksheet->mergeCells('A1:A2');
        $worksheet->setCellValue('A1', 'STT');
        $worksheet->getStyle("A1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("A1")->applyFromArray($style);

        $worksheet->mergeCells('B1:B2');
        $worksheet->setCellValue('B1', 'Số hợp đồng');
        $worksheet->getStyle("B1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("B1")->applyFromArray($style);

        $worksheet->mergeCells('C1:C2');
        $worksheet->setCellValue('C1', 'Tên khách hàng');
        $worksheet->getStyle("C1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("C1")->applyFromArray($style);

        $worksheet->mergeCells('D1:E1');
        $worksheet->setCellValue('D1', 'Nội dung xử lý');
        $worksheet->getStyle("D1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("D1")->applyFromArray($style);

        $worksheet->setCellValue('D2', 'Unblock card');
        $worksheet->getStyle("D2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("D2")->applyFromArray($style);

        $worksheet->setCellValue('E2', 'Block card');
        $worksheet->getStyle("E2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("E2")->applyFromArray($style);

         $worksheet->mergeCells('F1:H1');
        $worksheet->setCellValue('F1', 'Remark');
        $worksheet->getStyle("F1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("F1")->applyFromArray($style);

        $worksheet->setCellValue('F2', 'ACCL');
        $worksheet->getStyle("F2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("F2")->applyFromArray($style);

        $worksheet->setCellValue('G2', 'SIBS');
        $worksheet->getStyle("G2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("G2")->applyFromArray($style);

        $worksheet->setCellValue('H2', 'Group');
        $worksheet->getStyle("H2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("H2")->applyFromArray($style);

        $worksheet->mergeCells('I1:I2');
        $worksheet->setCellValue('I1', 'Ngày xuất báo cáo');
        $worksheet->getStyle("I1")->getFill()
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

        $worksheet->getStyle("A1:I2")->applyFromArray($headerStyle);

        $start_row = 3;

        foreach($data as $key => $value) {
                        
            $worksheet->setCellValue('A' . $start_row, $value['index']);
            $worksheet->setCellValue('B' . $start_row, $value['account_number']);
            $worksheet->setCellValue('C' . $start_row, $value['name']);
            $worksheet->setCellValue('D' . $start_row, 'No'); 
            $worksheet->setCellValue('E' . $start_row, ($value['block'] == 'true')? 'Yes' : 'No'); 

            $worksheet->setCellValue('F' . $start_row, $value['accl']);
            $worksheet->setCellValue('G' . $start_row, $value['sibs']);
            $worksheet->setCellValue('H' . $start_row, $value['group']);
            $worksheet->setCellValue('I' . $start_row, date('d/m/Y',$value['createdAt']) );
            
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:I".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "loan/export/" . 'BlockCardReport.xlsx';
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