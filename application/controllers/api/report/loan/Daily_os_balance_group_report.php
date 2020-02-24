<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Daily_os_balance_group_report extends WFF_Controller {

    private $collection = "Os_balance_group_report";
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
            $date = date('d-m-Y');
            
            $match = array('createdAt' => array('$gte' => strtotime($date)));
            $data = $this->crud->read($this->collection, $request, array(), $match);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }


    function exportExcel() {
        $today      = date('Y-m-d');
        $last6Month = strtotime(date("Y-m-d", strtotime($today)) . " -6 month");
        

        $request = array('createdAt' => array('$gte' => $last6Month), 'type' => 'SIBS');
        $data = $this->crud->where($request)->order_by(array('debt_group' => 'asc', 'due_date' => 'asc','createdAt' => 'asc'))->get($this->collection);
        // print_r($data);exit;



        $spreadsheet = new Spreadsheet();
    	$spreadsheet->getProperties()
	    ->setCreator("South Telecom")
	    ->setLastModifiedBy("Thanh Hung")
	    ->setTitle("DailyReportOfOSBlanceOfGroupBCDE")
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

        $start_col = 3;
        $start_row = 3;
        $debt_group = $data[0]['debt_group'];
        $for_month = $data[0]['for_month'];
        $month_column = $this->stringFromColumnIndex($start_col);

        foreach ($data as $key => $value) {
            $column         = $this->stringFromColumnIndex($start_col);
            $next_column    = $this->stringFromColumnIndex($start_col+1);
            $getDate        = getdate($value['created_at']);

            if (isset($value['check_due_date'])) {
                $col_duedate = $column;
            }

            if ($value['debt_group'] != $debt_group) {
                $start_row += 18;
                $debt_group = $value['debt_group'];
                $start_col = 3;
            }

            if ($value['for_month'] != $for_month) {
                $endMonthColumn = $this->stringFromColumnIndex($start_col -1);
                $worksheet->mergeCells($month_column."1:".$endMonthColumn."1");
                $worksheet->setCellValue($month_column."1", $getDate['month']);
                $worksheet->getStyle($month_column."1")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('8EA9DB');


                $month_column = $this->stringFromColumnIndex($start_col);

            }
            if ($key == (count($data) - 1)) {
                $endMonthColumn = $this->stringFromColumnIndex($start_col);
                $worksheet->mergeCells($month_column."1:".$endMonthColumn."1");
                $worksheet->setCellValue($month_column."1", $getDate['month']);
                $worksheet->getStyle($month_column."1")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('8EA9DB');
            }

            $worksheet->setCellValue('A'.$start_row, $debt_group);
            $worksheet->setCellValue('B'.$start_row, $value['target']);

            $worksheet->mergeCells("A".($start_row+1).":A".($start_row+2));
            $worksheet->setCellValue('A'.($start_row+1), 'START');
            $worksheet->setCellValue('B'.($start_row+1), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+2), 'No.');
            $worksheet->getStyle("A".($start_row+1).":B".($start_row+2))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('8EA9DB');

            $worksheet->mergeCells("A".($start_row+3).":A".($start_row+4));
            $worksheet->setCellValue('A'.($start_row+3), 'TARGET OF COLLECTION');
            $worksheet->setCellValue('B'.($start_row+3), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+4), 'No.');
            $worksheet->getStyle("A".($start_row+3).":B".($start_row+4))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('C6E0B4');

            $worksheet->mergeCells("A".($start_row+5).":A".($start_row+6));
            $worksheet->setCellValue('A'.($start_row+5), 'DAILY');
            $worksheet->setCellValue('B'.($start_row+5), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+6), 'No.');
            $worksheet->getStyle("A".($start_row+5).":B".($start_row+6))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

            $worksheet->mergeCells("A".($start_row+7).":A".($start_row+8));
            $worksheet->setCellValue('A'.($start_row+7), 'RESULT END OF DAY');
            $worksheet->setCellValue('B'.($start_row+7), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+8), 'No.');
            $worksheet->getStyle("A".($start_row+7).":B".($start_row+8))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('C6E0B4');

            $worksheet->mergeCells("A".($start_row+9).":A".($start_row+10));
            $worksheet->setCellValue('A'.($start_row+9), 'ACCUMULATED');
            $worksheet->setCellValue('B'.($start_row+9), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+10), 'No.');
            $worksheet->getStyle("A".($start_row+9).":B".($start_row+10))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

            $worksheet->mergeCells("A".($start_row+11).":A".($start_row+12));
            $worksheet->setCellValue('A'.($start_row+11), 'RATIO (vs target)');
            $worksheet->setCellValue('B'.($start_row+11), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+12), 'No.');
            $worksheet->getStyle("A".($start_row+11).":B".($start_row+12))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

            $worksheet->mergeCells("A".($start_row+13).":A".($start_row+14));
            $worksheet->setCellValue('A'.($start_row+13), 'RATIO (vs start)');
            $worksheet->setCellValue('B'.($start_row+13), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+14), 'No.');
            $worksheet->getStyle("A".($start_row+13).":B".($start_row+14))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

            $worksheet->mergeCells("A".($start_row+15).":A".($start_row+17));
            $worksheet->setCellValue('A'.($start_row+15), 'FINAL No');
            $worksheet->setCellValue('B'.($start_row+15), 'OS BL');
            $worksheet->setCellValue('B'.($start_row+16), 'No.');
            $worksheet->setCellValue('B'.($start_row+17), 'Principal');
            $worksheet->getStyle("A".($start_row+15).":B".($start_row+17))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);


            

            $worksheet->setCellValueByColumnAndRow($start_col, 2, $value['day']);

            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 1, $value['start_os_bl']);
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 2, $value['start_no']);
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 3, $value['taget_of_col_os_bl']);
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 4, $value['taget_of_col_no']);
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 5, $value['daily_os_bl']);
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 6, $value['daily_no']);
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 7, "=".$column.($start_row+5)."-".$next_column.($start_row+5) );
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 8, "=".$column.($start_row+6)."-".$next_column.($start_row+6) );
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 9, "=".$col_duedate.($start_row+1)."-".$next_column.($start_row+5) );
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 10, "=".$col_duedate.($start_row+2)."-".$next_column.($start_row+6) );
            $worksheet->getStyle($column.($start_row + 1).":".$column.($start_row + 10))
              ->getNumberFormat()
              ->setFormatCode('#,##0');

            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 11, "=".$column.($start_row+9)."/".$col_duedate.($start_row+3) );
            $worksheet->getStyle($column.($start_row + 11))
              ->getNumberFormat()
              ->setFormatCode('0%');
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$column.($col_duedate+4)."-".$column.($start_row+10) );
            $worksheet->getStyle($column.($start_row + 12))
              ->getNumberFormat()
              ->setFormatCode('#,##0');

            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 13, "=".$column.($start_row+9)."/".$col_duedate.($start_row+1) );
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 14, "=".$column.($start_row+10)."/".$col_duedate.($start_row+2));
            $worksheet->getStyle($column.($start_row + 13).":".$column.($start_row + 14))
              ->getNumberFormat()
              ->setFormatCode('0%');


            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 15, isset($value['final_os_bl']) ? $value['final_os_bl'] : '' );
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 16, isset($value['final_no']) ? $value['final_no'] : '' );
            $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 17, isset($value['final_principal']) ? $value['final_principal'] : '' );
            $worksheet->getStyle($column.($start_row + 15).":".$column.($start_row + 17))
              ->getNumberFormat()
              ->setFormatCode('#,##0');




            $start_col ++;
        }










        $maxCell = $worksheet->getHighestRowAndColumn();
        $headerStyle = array(
            'font'          => array(
                'bold'      => true,
            ),
            'alignment'     => array(
                'wrapText'  => true
            )
        );

        $worksheet->getStyle("A1:".$maxCell['column'].$maxCell['row'])->applyFromArray($headerStyle);
        
        $worksheet->getStyle("A1:".$maxCell['column'].$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "loan/export/" . 'DailyReportOfOSBlanceOfGroupBCDE.xlsx';
		$writer->save($file_path);
		echo json_encode(array("status" => 1, "data" => $file_path));
    }

    function stringFromColumnIndex($columnIndex) {
        return $this->excel->stringFromColumnIndex($columnIndex);
    }

    
}