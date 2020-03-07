<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Daily_prod_each_group_report extends WFF_Controller {

    private $collection = "Daily_prod_each_group";
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
            $data = $this->crud->read($this->collection, $request, array(), $match);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }


    function readProduct() {
        try {
            $data = $this->crud->get(set_sub_collection("Product"));
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function exportExcel() {
        $now = getdate();
        $month = (string)$now['mon'];
        $date = date('d-m-Y',strtotime("-1 days"));

        $request = array('createdAt' => array('$gte' => strtotime($date)));
        $data = $this->crud->where($request)->order_by(array('debt_group' => 'asc', 'due_date_code' => 'asc'))->get($this->collection);
        $product = $this->crud->order_by(array('code' => 'asc'))->get(set_sub_collection('Product'));
        $groupProduct = $this->mongo_private->where(array('tags' => array('group', 'debt', 'product')))->getOne(set_sub_collection("Jsondata"));
        // print_r($data);exit;
        $spreadsheet = new Spreadsheet();
    	$spreadsheet->getProperties()
	    ->setCreator("South Telecom")
	    ->setLastModifiedBy("Thanh Hung")
	    ->setTitle("Daily Each Due Date Each Group Report")
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
        $worksheet->getDefaultColumnDimension()->setWidth(20);

        $worksheet->mergeCells('A1:C1');
        $worksheet->setCellValue('A1', 'month-year');
        $worksheet->getStyle("A1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("A1")->applyFromArray($style);

        $worksheet->mergeCells('D1:D3');
        $worksheet->setCellValue('D1', 'Due date');
        $worksheet->getStyle("D1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("D1")->applyFromArray($style);

        $worksheet->mergeCells('E1:S1');
        $worksheet->setCellValue('E1', $now['mon'].'/'.$now['year']);
        $worksheet->getStyle("E1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("E1")->applyFromArray($style);

        $worksheet->mergeCells('A2:C2');
        $worksheet->setCellValue('A2', 'number.os');
        $worksheet->getStyle("A2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("A2")->applyFromArray($style);

        $worksheet->mergeCells('E2:I2');
        $worksheet->setCellValue('E2', 'Number');
        $worksheet->getStyle("E2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("E2")->applyFromArray($style);

         $worksheet->mergeCells('J2:S2');
        $worksheet->setCellValue('J2', 'Outstanding Balance');
        $worksheet->getStyle("J2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("J2")->applyFromArray($style);

        $worksheet->mergeCells('A3:B3');
        $worksheet->setCellValue('A3', 'Group');
        $worksheet->setCellValue('C3', 'Product');
        $worksheet->getStyle("A3:C3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EDEDED');

        $worksheet->setCellValue('E3', 'Incidence');
        $worksheet->setCellValue('F3', 'Collected');
        $worksheet->setCellValue('G3', 'Remaining');
        $worksheet->setCellValue('H3', 'Flow rate');
        $worksheet->setCellValue('I3', 'Collected rate');
        $worksheet->setCellValue('J3', 'Incidence (outstanding balance at due date)');
        $worksheet->setCellValue('K3', 'Incidence (outstanding principal)');
        $worksheet->setCellValue('L3', 'Actual collected amount (based on oustanding balance at due date)');
        $worksheet->setCellValue('M3', 'Collected principal amount');
        $worksheet->setCellValue('N3', 'Collected  amount (OS at current - OS at due date)');
        $worksheet->setCellValue('O3', "Remaining (OS at current - OS at due date)");
        $worksheet->setCellValue('P3', "Flow rate (OS at current - OS at due date)");
        $worksheet->setCellValue('Q3', "Collected ratio (Actual collected amount)");
        $worksheet->setCellValue('R3', 'Collected ratio (Principal amount)');
        $worksheet->setCellValue('S3', 'Collected ratio (OS at current - OS due date)');

        $worksheet->getStyle("E3:S3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        
        $headerStyle = array(
            'font'          => array(
                'bold'      => true,
            ),
            'alignment'     => array(
                'wrapText'  => true
            )
        );

        $worksheet->getStyle("A1:S3")->applyFromArray($headerStyle);

        $start_row = 4;

        $start_row_debt_group = 4;
        $debt_group = $data[0]['debt_group'];

        $start_row_due_date_code = 4;
        $due_date_code = $data[0]['due_date_code'];

        $start_row_prod = 4;
        $prod_row = $data[0]['product'];

        $start_due_date = 4;
        $due_date = $data[0]['due_date'];
        $due_date_1 = date('d/m/Y',  $data[0]['due_date']);

        // print_r($data);
        foreach($data as $key => $value) {
            
            if ($value['due_date_code'] == '99') {
                $worksheet->mergeCells('B' . $start_row . ':D' . $start_row );
                $worksheet->setCellValue('B' . $start_row, $debt_group);
                $worksheet->getStyle('B' . $start_row. ':S'. $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                $worksheet->setCellValue('B' . $start_row, $value['product']);
            }else if ($value['due_date_code'] == '100') {
                $worksheet->mergeCells('B' . $start_row . ':D' . $start_row );
                $worksheet->setCellValue('B' . $start_row, $debt_group);
                $worksheet->getStyle('B' . $start_row. ':S'. $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                $worksheet->setCellValue('B' . $start_row, $value['product']);
            }
            else{
                $worksheet->setCellValue('B' . $start_row, $value['due_date_code']);
                $worksheet->setCellValue('C' . $start_row, $value['product']);
                $worksheet->setCellValue('D' . $start_row, date('d/m/Y',  $value['due_date']));
            }
            
            $worksheet->setCellValue('E' . $start_row, $value['inci']);
            $worksheet->setCellValue('F' . $start_row, $value['col']);
            $worksheet->setCellValue('G' . $start_row, $value['rem']);
            $worksheet->setCellValue('H' . $start_row, $value['flow_rate']); 
            $worksheet->setCellValue('I' . $start_row, $value['col_rate']); 

            $worksheet->setCellValue('J' . $start_row, $value['inci_amt']);
            $worksheet->setCellValue('K' . $start_row, $value['inci_ob_principal']);
            $worksheet->setCellValue('L' . $start_row, $value['amt']);
            $worksheet->setCellValue('M' . $start_row, $value['col_prici']); 
            $worksheet->setCellValue('N' . $start_row, $value['col_amt']); 
            $worksheet->setCellValue('O' . $start_row, $value['rem_amt']);
            $worksheet->setCellValue('P' . $start_row, $value['flow_rate_amt']);
            $worksheet->setCellValue('Q' . $start_row, $value['actual_ratio']);
            $worksheet->setCellValue('R' . $start_row, $value['princi_ratio']); 
            $worksheet->setCellValue('S' . $start_row, $value['amt_ratio']); 

            if($debt_group != $value['debt_group']) {

                $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row-1) );
                $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                $debt_group = $value['debt_group'];
                $start_row_debt_group = $start_row;
            }



            if ($key == (count($data) - 1)) {
                // if ($value['debt_group'] != 'F') {
                    $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . $start_row);
                    $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                    $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

            
                // }
            }
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:BY".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $worksheet->getStyle('E' ."4:G".$maxCell['row'])->getNumberFormat()->setFormatCode('#,##0');
        $worksheet->getStyle('H' ."4:I".$maxCell['row'])->getNumberFormat()->setFormatCode('0.00%');
        $worksheet->getStyle('J' ."4:O".$maxCell['row'])->getNumberFormat()->setFormatCode('#,##0');
        $worksheet->getStyle('P' ."4:S".$maxCell['row'])->getNumberFormat()->setFormatCode('0.00%');



        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "loan/export/" . 'DailyEachDueDateEachGroup.xlsx';
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