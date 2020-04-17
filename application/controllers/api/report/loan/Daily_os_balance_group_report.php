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
            $request['sort'] = array(array("field" => "debt_group", "dir" => "asc"), array("field" => "year", "dir" => "asc"), array("field" => "for_month", "dir" => "asc"), array("field" => "day", "dir" => "asc"));
            
            $today      = date('Y-m-d');
            $last6Month = strtotime(date("Y-m-d", strtotime($today)) . " -6 month");
            $match = array('createdAt' => array('$gte' => $last6Month), 'type' => 'SIBS');
            $data = $this->crud->read($this->collection, $request, array(), $match);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function readCard() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "debt_group", "dir" => "asc"), array("field" => "due_date", "dir" => "asc"), array("field" => "year", "dir" => "asc"), array("field" => "for_month", "dir" => "asc"), array("field" => "day", "dir" => "asc"));
            
            $today      = date('Y-m-d');
            $last6Month = strtotime(date("Y-m-d", strtotime($today)) . " -6 month");
            $match = array('createdAt' => array('$gte' => $last6Month), 'type' => 'CARD');
            $data = $this->crud->read($this->collection, $request, array(), $match);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function saveReport()
    {
      shell_exec('/usr/local/bin/python3.6 /data/worldfone4xs/cronjob/python/Loan/saveDailyReportOfOSBalanceOfGroupABCDE.py  > /dev/null &');
      echo json_encode(array("status" => 1, "data" => []));
    }
    
    function exportExcel()
    {
        $file_path = UPLOAD_PATH . "loan/export/DailyReportOfOSBlanceOfGroupBCDE.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }

  //   function exportExcel() {
  //       $today      = date('Y-m-d');
  //       $last6Month = strtotime(date("Y-m-d", strtotime($today)) . " -1 month");
  //       // print_r($today);exit;

  //       $request = array('createdAt' => array('$gte' => $last6Month), 'type' => 'SIBS');
  //       $data = $this->crud->where($request)->order_by(array('debt_group' => 'asc', 'year' => 'asc','for_month' => 'asc','day' => 'asc'))->get($this->collection);


  //       $request = array('createdAt' => array('$gte' => $last6Month), 'type' => 'CARD');
  //       $dataCard = $this->crud->where($request)->order_by(array('debt_group' => 'asc', 'year' => 'asc','for_month' => 'asc','day' => 'asc'))->get($this->collection);

  //       $spreadsheet = new Spreadsheet();
  //   	$spreadsheet->getProperties()
	 //    ->setCreator("South Telecom")
	 //    ->setLastModifiedBy("Thanh Hung")
	 //    ->setTitle("DailyReportOfOSBlanceOfGroupBCDE")
	 //    ->setSubject("Report")
	 //    ->setDescription("Office 2007 XLSX, generated using PHP classes.")
	 //    ->setKeywords("office 2007 openxml php")
  //       ->setCategory("Report");

  //       $style = array(
  //           'alignment'     => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER),
  //           'allborders'    => array(
  //               'style'     => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
  //               'color'     => array('rgb' => '000000')
  //           )
  //       );

        
  //       $worksheet = $spreadsheet->getSheet(0);
  //       $worksheet->setTitle('Bike & BL');


  //       $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);
  //       $worksheet->getDefaultColumnDimension()->setWidth(20);

  //       $start_col = 3;
  //       $start_row = 3;
  //       $debt_group     = $data[0]['debt_group'];
  //       $fix_debt_group = $data[0]['debt_group'];
  //       $for_month      = $data[0]['for_month'];
  //       $getDate        = getdate($data[0]['created_at']);
  //       $month_column   = $this->stringFromColumnIndex($start_col);

  //       $rowGroup = $borderThick = array();
  //       $randomColor = 1;

  //       foreach ($data as $key => $value) {
  //           $column         = $this->stringFromColumnIndex($start_col);
  //           $next_column    = $this->stringFromColumnIndex($start_col+1);
  //           $getValueDate   = getdate($value['created_at']);
  //           $group          = substr($value['debt_group'], 0, 1);

  //           if (isset($value['check_due_date'])) { //kiem tra co phai la ngay due date
  //               $col_duedate = $column;
  //           }

  //           if ($value['debt_group'] != $debt_group) {
  //               $start_row += 17;
  //               $debt_group = $value['debt_group'];
  //               $start_col = 3;
  //           }

  //           if ($value['for_month'] != $for_month && $value['debt_group'] == $fix_debt_group) {
  //               // doi thang, doi mau
  //               if ($randomColor%2) {
  //                   $color = '8EA9DB';
  //               }else{
  //                   $color = 'FFFF00';
  //               }
  //               $endMonthColumn = $this->stringFromColumnIndex($start_col-1);
  //               $worksheet->mergeCells($month_column."1:".$endMonthColumn."1");
  //               $worksheet->setCellValue($month_column."1", $getDate['month']);
  //               $worksheet->getStyle($month_column."1")->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB($color);

  //               $getDate        = getdate($value['created_at']);
  //               $month_column = $this->stringFromColumnIndex($start_col);
  //               $for_month      = $value['for_month'];
  //               $randomColor++;

  //               array_push($borderThick, array('col'=>$endMonthColumn, 'row'=> 1));
  //           }

            

  //           if ($key == (count($data) - 1)) {
  //               //thang cuoi cung
  //               if ($randomColor%2) {
  //                   $color = '8EA9DB';
  //               }else{
  //                   $color = 'FFFF00';
  //               }
  //               $endMonthColumn = $this->stringFromColumnIndex($start_col);
  //               $worksheet->mergeCells($month_column."1:".$endMonthColumn."1");
  //               $worksheet->setCellValue($month_column."1", $getDate['month']);
  //               $worksheet->getStyle($month_column."1")->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB($color);
  //           }
  //           // ten group va target
  //           $worksheet->setCellValue('A'.$start_row, $debt_group);
  //           $worksheet->setCellValue('B'.$start_row, 'Principal');
  //           // $worksheet->getStyle('B'.$start_row)->getNumberFormat()->setFormatCode('0.00%');
  //           array_push($rowGroup, $start_row);

  //           $worksheet->mergeCells("A".($start_row+1).":A".($start_row+2));
  //           $worksheet->setCellValue('A'.($start_row+1), 'START');
  //           $worksheet->setCellValue('B'.($start_row+1), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+2), 'No.');
  //           $worksheet->getStyle("A".($start_row+1).":B".($start_row+2))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('8EA9DB');

  //           $worksheet->mergeCells("A".($start_row+3).":A".($start_row+4));
  //           $worksheet->setCellValue('A'.($start_row+3), 'TARGET OF COLLECTION');
  //           $worksheet->setCellValue('B'.($start_row+3), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+4), 'No.');
  //           $worksheet->getStyle("A".($start_row+3).":B".($start_row+4))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('C6E0B4');

  //           $worksheet->mergeCells("A".($start_row+5).":A".($start_row+6));
  //           $worksheet->setCellValue('A'.($start_row+5), 'DAILY');
  //           $worksheet->setCellValue('B'.($start_row+5), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+6), 'No.');
  //           $worksheet->getStyle("A".($start_row+5).":B".($start_row+6))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

  //           $worksheet->mergeCells("A".($start_row+7).":A".($start_row+8));
  //           $worksheet->setCellValue('A'.($start_row+7), 'RESULT END OF DAY');
  //           $worksheet->setCellValue('B'.($start_row+7), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+8), 'No.');
  //           $worksheet->getStyle("A".($start_row+7).":B".($start_row+8))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');

  //           $worksheet->mergeCells("A".($start_row+9).":A".($start_row+10));
  //           $worksheet->setCellValue('A'.($start_row+9), 'ACCUMULATED');
  //           $worksheet->setCellValue('B'.($start_row+9), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+10), 'No.');
  //           $worksheet->getStyle("A".($start_row+9).":B".($start_row+10))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

  //           $worksheet->mergeCells("A".($start_row+11).":A".($start_row+12));
  //           $worksheet->setCellValue('A'.($start_row+11), 'RATIO (vs target)');
  //           $worksheet->setCellValue('B'.($start_row+11), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+12), 'No.');
  //           $worksheet->getStyle("A".($start_row+11).":B".($start_row+12))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

  //           $worksheet->mergeCells("A".($start_row+13).":A".($start_row+14));
  //           $worksheet->setCellValue('A'.($start_row+13), 'RATIO (vs start)');
  //           $worksheet->setCellValue('B'.($start_row+13), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+14), 'No.');
  //           $worksheet->getStyle("A".($start_row+13).":B".($start_row+14))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

  //           $worksheet->mergeCells("A".($start_row+15).":A".($start_row+16));
  //           $worksheet->setCellValue('A'.($start_row+15), 'FINAL No');
  //           $worksheet->setCellValue('B'.($start_row+15), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+16), 'No.');
  //           // $worksheet->setCellValue('B'.($start_row+17), 'Principal');
  //           $worksheet->getStyle("A".($start_row+15).":B".($start_row+16))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);


            

  //           $worksheet->setCellValueByColumnAndRow($start_col, 2, $value['day']);
  //           $worksheet->setCellValueByColumnAndRow($start_col,$start_row, (isset($value['principal'])) ? $value['principal'] : '' );
  //           $colPrincipal = $this->stringFromColumnIndex($start_col);
  //           $worksheet->getStyle($colPrincipal.$start_row)->getNumberFormat()->setFormatCode('#,##0');

  //           if (isset($value['check_due_date'])) {
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 1, $value['start_os_bl']);
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 2, $value['start_no']);
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 3, $value['target_of_col_os_bl']);
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 4, $value['target_of_col_no']);

  //               $worksheet->setCellValueByColumnAndRow($start_col+2, $start_row + 1, 'Target');
  //               $worksheet->setCellValueByColumnAndRow($start_col+3, $start_row + 1, ($value['target']/100));

  //               $colTargetTitle = $this->stringFromColumnIndex($start_col+2);
  //               $colTarget      = $this->stringFromColumnIndex($start_col+3);

  //               $worksheet->setCellValueByColumnAndRow($start_col+2, $start_row + 3, $value['start_no']-$value['target_of_col_no']);
  //               $worksheet->setCellValueByColumnAndRow($start_col+2, $start_row + 4, $value['start_os_bl']-$value['target_of_col_os_bl']);

  //               $worksheet->getStyle($column.($start_row+1).":".$column.($start_row+2))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('8EA9DB');
  //               $worksheet->getStyle($column.($start_row+3).":".$column.($start_row+4))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('C6E0B4');

  //               $worksheet->getStyle($colTarget. ($start_row + 1))->getNumberFormat()->setFormatCode('0.00%');
  //               $headerStyle1 = array(
  //                   'font'          => array(
  //                       'color'     => array('rgb' => 'FF0000'),
  //                   )
  //               );
  //               $worksheet->getStyle($colTargetTitle.($start_row + 1).":".$colTarget.($start_row + 1))->applyFromArray($headerStyle1);
  //               $worksheet->getStyle($column.($start_row + 1).":".$next_column.($start_row + 1))->getNumberFormat()->setFormatCode('#,##0');
  //               $worksheet->getStyle($colTargetTitle.($start_row + 3).":".$colTargetTitle.($start_row + 4))->getNumberFormat()->setFormatCode('#,##0');
  //           }

  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 5, $value['daily_os_bl']);
  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 6, $value['daily_no']);

  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 7, "=".$column.($start_row+5)."-".$next_column.($start_row+5) );
  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 8, "=".$column.($start_row+6)."-".$next_column.($start_row+6) );

  //           if (isset($col_duedate)) { //ngay due date cong 1
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 9, "=".$col_duedate.($start_row+1)."-".$next_column.($start_row+5) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 10, "=".$col_duedate.($start_row+2)."-".$next_column.($start_row+6) );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 11, "=".$column.($start_row+9)."/".$col_duedate.($start_row+3) );
  //               if ($group == 'A') {
  //                   $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$col_duedate.($start_row+4)."-".$column.($start_row+10) );
  //               }else{
  //                   $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$column.($start_row+10)."/".$col_duedate.($start_row+4) );
  //               }
                
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 13, "=".$column.($start_row+9)."/".$col_duedate.($start_row+1) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 14, "=".$column.($start_row+10)."/".$col_duedate.($start_row+2));
  //           }
  //           if (isset($value['final_os_bl'])) { //ngay due date, ngay final
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 7, $value['daily_os_bl'] - $value['final_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 8, $value['daily_no'] - $value['final_no'] );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 9, $value['start_os_bl'] - $value['final_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 10, $value['start_no'] - $value['final_no'] );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 11, "=".$column.($start_row+9)."/".$value['target_of_col_os_bl'] );
                
  //               if ($group == 'A') {
  //                   $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$value['target_of_col_no']."-".$column.($start_row+10) );
  //               }else{
  //                   $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$column.($start_row+10)."/".$value['target_of_col_no'] );
  //               }
                
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 13, "=".$column.($start_row+9)."/".$value['start_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 14, "=".$column.($start_row+10)."/".$value['start_no']);

  //               array_push($borderThick, array('col'=>$column, 'row'=>$start_row));

  //               $column_wo_title    = $this->stringFromColumnIndex($start_col - 4);
  //               $column_wo          = $this->stringFromColumnIndex($start_col - 3);

  //               $worksheet->setCellValueByColumnAndRow($start_col-4, $start_row + 15, 'Write Of OS BL' );
  //               $worksheet->setCellValueByColumnAndRow($start_col-4, $start_row + 16, 'Write Of No.' );
  //               $worksheet->getStyle($column_wo_title.($start_row+15).":".$column_wo.($start_row+16))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('cb42f5');


  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 15, "=".$value['final_os_bl'].'+'.$column_wo.($start_row+15) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 16, "=".$value['final_no'].'+'.$column_wo.($start_row+16) );
  //               // $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 17, isset($value['final_principal']) ? $value['final_principal'] : '' );
  //               $worksheet->getStyle($column.($start_row + 15).":".$column.($start_row + 16))->getNumberFormat()->setFormatCode('#,##0');
  //               $headerStyle1 = array(
  //                   'font'          => array(
  //                       'color'     => array('rgb' => 'FF0000'),
  //                   )
  //               );
  //               $worksheet->getStyle($column.($start_row + 15).":".$column.($start_row + 16))->applyFromArray($headerStyle1);

  //           }else{
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 9, "=".$value['start_os_bl']."-".$next_column.($start_row+5) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 10, "=".$value['start_no']."-".$next_column.($start_row+6) );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 11, "=".$column.($start_row+9)."/".$value['target_of_col_os_bl'] );
                
  //               if ($group == 'A') {
  //                   $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$value['target_of_col_no']."-".$column.($start_row+10) );
  //               }else{
  //                   $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$column.($start_row+10)."/".$value['target_of_col_no'] );
  //               }
                
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 13, "=".$column.($start_row+9)."/".$value['start_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 14, "=".$column.($start_row+10)."/".$value['start_no'] );
  //           }
            

  //           $worksheet->getStyle($column.($start_row+7).":".$column.($start_row+8))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');

  //           $worksheet->getStyle($column.($start_row + 1).":".$column.($start_row + 10))->getNumberFormat()->setFormatCode('#,##0');
  //           $worksheet->getStyle($column.($start_row + 11))->getNumberFormat()->setFormatCode('0.00%');
  //           if ($group == 'A') {
  //               $worksheet->getStyle($column.($start_row + 12))->getNumberFormat()->setFormatCode('#,##0');
  //           }else{
  //               $worksheet->getStyle($column.($start_row + 12))->getNumberFormat()->setFormatCode('0.00%');
  //           }
  //           $worksheet->getStyle($column.($start_row + 13).":".$column.($start_row + 14))->getNumberFormat()->setFormatCode('0.00%');


  //           $headerStyle1 = array(
  //               'font'          => array(
  //                   'color'     => array('rgb' => '0000FF'),
  //               )
  //           );
  //           $worksheet->getStyle($column.($start_row + 12))->applyFromArray($headerStyle1);

  //           if ($getValueDate['wday'] == 0 || $getValueDate['wday'] == 6) {
  //               $column_weekday         = $this->stringFromColumnIndex($start_col);
  //               $worksheet->getStyle($column_weekday.($start_row+1).":".$column_weekday.($start_row+17))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('D9D9D9');
  //           }



  //           $start_col ++;
  //       }



  //       $maxCell = $worksheet->getHighestRowAndColumn();
  //       $headerStyle1 = array(
  //           'font'          => array(
  //               'bold'      => false,
  //           ),
  //           'alignment'     => array(
  //               'wrapText'  => true
  //           )
  //       );
  //       $maxCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCell['column']);
  //       $maxCol = $this->stringFromColumnIndex($maxCol -1);
  //       $worksheet->getStyle("A1:".$maxCol.$maxCell['row'])->applyFromArray($headerStyle1);
        
  //       $worksheet->getStyle("A1:".$maxCol.$maxCell['row'])->getBorders()
  //       ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


  //       $headerStyle = array(
  //               'font'          => array(
  //                   'bold'      => true,
  //                   'size'      => 16
  //               ),
  //               'alignment'     => array(
  //                   'wrapText'  => false
  //               )
  //           );
  //       foreach ($rowGroup as $value) {
  //           $worksheet->getStyle("A".$value.":B".$value)->applyFromArray($headerStyle);
  //           $worksheet->getStyle("A".$value.":".$maxCol.$value)->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
  //       }


  //       foreach ($borderThick as $value) {
  //           if ($value['row'] == 1) {
  //               //to dam border cuoi thang
  //               $worksheet->getStyle($value['col']."1:".$value['col'].$maxCell['row'])->getBorders()
  //               ->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
  //           }else{
  //               //to dam border cuoi ki due
  //               $worksheet->getStyle($value['col'].($value['row']+1).":".$value['col'].($value['row']+17))->getBorders()
  //               ->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
  //           }
  //       }
  //       $worksheet->getStyle("B1:B".$maxCell['row'])->getBorders()
  //               ->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        








  //       // CARD
  //       $worksheet = $spreadsheet->createSheet(1);
  //       $worksheet->setTitle('Card');

  //       $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);
  //       $worksheet->getDefaultColumnDimension()->setWidth(20);

  //       $start_col = 3;
  //       $start_row = 3;
  //       $debt_group     = $data[0]['debt_group'];
  //       $fix_debt_group = $data[0]['debt_group'];
  //       $for_month      = $data[0]['for_month'];
  //       $day            = $data[0]['day'];
  //       $getDate        = getdate($data[0]['created_at']);
  //       $month_column   = $this->stringFromColumnIndex($start_col);

  //       $rowGroup = $borderThick = array();
  //       $randomColor = 1;

  //       foreach ($dataCard as $key => $value) {
  //           $column         = $this->stringFromColumnIndex($start_col);
  //           $next_column    = $this->stringFromColumnIndex($start_col+1);
  //           $getValueDate   = getdate($value['created_at']);


  //           if (isset($value['check_due_date'])) { //kiem tra co phai la ngay due date
  //               $col_duedate = $column;
  //               // $colTargetNo = $next_column;
  //           }

  //           if ($value['debt_group'] != $debt_group) {
  //               $start_row += 15;
  //               $debt_group = $value['debt_group'];
  //               if ($value['day'] == $day) {
  //                   $start_col = $start_col-1;
  //               }else{
  //                   $start_col = 3;
  //               }
                
  //           }
  //           $day = $value['day'];

  //           if ($value['for_month'] != $for_month && $value['debt_group'] == $fix_debt_group) {
  //               // doi thang, doi mau
  //               if ($randomColor%2) {
  //                   $color = '8EA9DB';
  //               }else{
  //                   $color = 'FFFF00';
  //               }
  //               $endMonthColumn = $this->stringFromColumnIndex($start_col-1);
  //               $worksheet->mergeCells($month_column."1:".$endMonthColumn."1");
  //               $worksheet->setCellValue($month_column."1", $getDate['month']);
  //               $worksheet->getStyle($month_column."1")->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB($color);

  //               $getDate        = getdate($value['created_at']);
  //               $month_column = $this->stringFromColumnIndex($start_col);
  //               $for_month      = $value['for_month'];
  //               $randomColor++;

  //               array_push($borderThick, array('col'=>$endMonthColumn, 'row'=> 1));
  //           }

            

  //           if ($key == (count($data) - 1)) {
  //               //thang cuoi cung
  //               if ($randomColor%2) {
  //                   $color = '8EA9DB';
  //               }else{
  //                   $color = 'FFFF00';
  //               }
  //               $endMonthColumn = $this->stringFromColumnIndex($start_col);
  //               $worksheet->mergeCells($month_column."1:".$endMonthColumn."1");
  //               $worksheet->setCellValue($month_column."1", $getDate['month']);
  //               $worksheet->getStyle($month_column."1")->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB($color);
  //           }
  //           // ten group va target
  //           $worksheet->setCellValue('A'.$start_row, $debt_group);
  //           $worksheet->setCellValue('B'.$start_row, 'Principal');
  //           // $worksheet->getStyle('B'.$start_row)->getNumberFormat()->setFormatCode('0.00%');
  //           array_push($rowGroup, $start_row);

  //           $worksheet->mergeCells("A".($start_row+1).":A".($start_row+2));
  //           $worksheet->setCellValue('A'.($start_row+1), 'START');
  //           $worksheet->setCellValue('B'.($start_row+1), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+2), 'No.');
  //           $worksheet->getStyle("A".($start_row+1).":B".($start_row+2))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('8EA9DB');

  //           $worksheet->mergeCells("A".($start_row+3).":A".($start_row+4));
  //           $worksheet->setCellValue('A'.($start_row+3), 'DAILY');
  //           $worksheet->setCellValue('B'.($start_row+3), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+4), 'No.');
  //           $worksheet->getStyle("A".($start_row+3).":B".($start_row+4))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

  //           $worksheet->mergeCells("A".($start_row+5).":A".($start_row+6));
  //           $worksheet->setCellValue('A'.($start_row+5), 'RESULT END OF DAY');
  //           $worksheet->setCellValue('B'.($start_row+5), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+6), 'No.');
  //           $worksheet->getStyle("A".($start_row+5).":B".($start_row+6))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');

  //           $worksheet->mergeCells("A".($start_row+7).":A".($start_row+8));
  //           $worksheet->setCellValue('A'.($start_row+7), 'ACCUMULATED');
  //           $worksheet->setCellValue('B'.($start_row+7), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+8), 'No.');
  //           $worksheet->getStyle("A".($start_row+7).":B".($start_row+8))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');

  //           $worksheet->mergeCells("A".($start_row+9).":A".($start_row+10));
  //           $worksheet->setCellValue('A'.($start_row+9), 'Collection ratio');
  //           $worksheet->setCellValue('B'.($start_row+9), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+10), 'No.');
  //           $worksheet->getStyle("A".($start_row+9).":B".($start_row+10))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');

  //           $worksheet->mergeCells("A".($start_row+11).":A".($start_row+12));
  //           $worksheet->setCellValue('A'.($start_row+11), 'To the Target');
  //           $worksheet->setCellValue('B'.($start_row+11), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+12), 'No.');
  //           $worksheet->getStyle("A".($start_row+11).":B".($start_row+12))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);

  //           $worksheet->mergeCells("A".($start_row+13).":A".($start_row+14));
  //           $worksheet->setCellValue('A'.($start_row+13), 'FINAL No');
  //           $worksheet->setCellValue('B'.($start_row+13), 'OS BL');
  //           $worksheet->setCellValue('B'.($start_row+14), 'No.');
  //           // $worksheet->setCellValue('B'.($start_row+15), 'Principal');
  //           $worksheet->getStyle("A".($start_row+13).":B".($start_row+14))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);


            

  //           $worksheet->setCellValueByColumnAndRow($start_col, 2, $value['day']);
  //           $worksheet->setCellValueByColumnAndRow($start_col,$start_row, (isset($value['principal'])) ? $value['principal'] : '' );
  //           $colPrincipal = $this->stringFromColumnIndex($start_col);
  //           $worksheet->getStyle($colPrincipal.$start_row)->getNumberFormat()->setFormatCode('#,##0');

  //           if (isset($value['check_due_date'])) {
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 1, $value['start_os_bl']);
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 2, $value['start_no']);
  //               $worksheet->setCellValueByColumnAndRow($start_col+2, $start_row + 1, 'Target');
  //               $worksheet->setCellValueByColumnAndRow($start_col+3, $start_row + 1, ($value['target']/100));


  //               $colTargetTitle = $this->stringFromColumnIndex($start_col+2);
  //               $colTarget      = $this->stringFromColumnIndex($start_col+3);
  //               $worksheet->setCellValueByColumnAndRow($start_col+1, $start_row + 1, "=".$column.($start_row + 2)."-(".$column.($start_row + 2)."*".$colTarget.($start_row + 1).")");

  //               $worksheet->getStyle($column.($start_row+1).":".$column.($start_row+2))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('8EA9DB');
                
  //               $worksheet->getStyle($colTarget. ($start_row + 1))->getNumberFormat()->setFormatCode('0.00%');
  //               $headerStyle1 = array(
  //                   'font'          => array(
  //                       'color'     => array('rgb' => 'FF0000'),
  //                   )
  //               );
  //               $worksheet->getStyle($colTargetTitle.($start_row + 1).":".$colTarget.($start_row + 1))->applyFromArray($headerStyle1);
  //               $worksheet->getStyle($column.($start_row + 1).":".$next_column.($start_row + 1))->getNumberFormat()->setFormatCode('#,##0');
  //           }

  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 3, $value['daily_os_bl']);
  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 4, $value['daily_no']);

  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 5, "=".$column.($start_row+3)."-".$next_column.($start_row+3) );
  //           $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 6, "=".$column.($start_row+4)."-".$next_column.($start_row+4) );

  //           if (isset($col_duedate)) { //ngay due date cong 1
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 7, "=".$col_duedate.($start_row+1)."-".$next_column.($start_row+3) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 8, "=".$col_duedate.($start_row+2)."-".$next_column.($start_row+4) );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 9, "=".$column.($start_row+7)."/".$col_duedate.($start_row+1) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 10, "=".$col_duedate.($start_row+8)."/".$column.($start_row+2) );
                
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$next_column.($start_row+4)."-".$next_column.($start_row+1));
  //           }
  //           if (isset($value['final_os_bl'])) { //ngay due date, ngay final
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 5, $value['daily_os_bl'] - $value['final_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 6, $value['daily_no'] - $value['final_no'] );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 7, $value['start_os_bl'] - $value['final_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 8, $value['start_no'] - $value['final_no'] );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 9, "=".$column.($start_row+7)."/".$value['start_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 10, "=".$column.($start_row+8)."/".$value['start_no'] );
                
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$value['final_no']."-".$value['start_no']."+(".$value['start_no']."*".($value['target']/100).")");


  //               $column_wo_title    = $this->stringFromColumnIndex($start_col - 4);
  //               $column_wo          = $this->stringFromColumnIndex($start_col - 3);

  //               $worksheet->setCellValueByColumnAndRow($start_col-4, $start_row + 13, 'Write Of OS BL' );
  //               $worksheet->setCellValueByColumnAndRow($start_col-4, $start_row + 14, 'Write Of No.' );
  //               $worksheet->getStyle($column_wo_title.($start_row+13).":".$column_wo.($start_row+14))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('cb42f5');


  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 13, "=".$value['final_os_bl'].'+'.$column_wo.($start_row+13) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 14, "=".$value['final_no'].'+'.$column_wo.($start_row+14) );
  //               // $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 15, $value['final_principal'] );

  //               $worksheet->getStyle($column.($start_row + 13).":".$column.($start_row + 14))->getNumberFormat()->setFormatCode('#,##0');
  //               $headerStyle1 = array(
  //                   'font'          => array(
  //                       'bold'      => true,
  //                       'color'     => array('rgb' => 'FF0000'),
  //                   )
  //               );
  //               $worksheet->getStyle($column.($start_row + 13).":".$column.($start_row + 14))->applyFromArray($headerStyle1);
  //               $worksheet->getStyle($column.($start_row + 13).":".$column.($start_row + 14))->getNumberFormat()->setFormatCode('#,##0');

  //               array_push($borderThick, array('col'=>$column, 'row'=>$start_row));


  //           }else{
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 7, "=".$value['start_os_bl']."-".$next_column.($start_row+3) );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 8, "=".$value['start_no']."-".$next_column.($start_row+4) );

  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 9, "=".$column.($start_row+7)."/".$value['start_os_bl'] );
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 10, "=".$column.($start_row+8)."/".$value['start_no'] );
                
  //               $worksheet->setCellValueByColumnAndRow($start_col, $start_row + 12, "=".$next_column.($start_row+6)."-".$value['start_no']."+(".$value['start_no']."*".($value['target']/100).")");
  //           }
            

  //           $worksheet->getStyle($column.($start_row+5).":".$column.($start_row+6))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCFFCC');

  //           $worksheet->getStyle($column.($start_row + 2).":".$column.($start_row + 8))->getNumberFormat()->setFormatCode('#,##0');
  //           $worksheet->getStyle($column.($start_row + 9).":".$column.($start_row + 10))->getNumberFormat()->setFormatCode('0.00%');
  //           $worksheet->getStyle($column.($start_row + 12))->getNumberFormat()->setFormatCode('#,##0');
  //           $headerStyle1 = array(
  //               'font'          => array(
  //                   'color'     => array('rgb' => 'FF0000'),
  //               )
  //           );
  //           $worksheet->getStyle($column.($start_row + 12))->applyFromArray($headerStyle1);

  //           $headerStyle1 = array(
  //               'font'          => array(
  //                   'color'     => array('rgb' => '0000FF'),
  //               )
  //           );
  //           $worksheet->getStyle($column.($start_row + 10))->applyFromArray($headerStyle1);

            
  //           if ($getValueDate['wday'] == 0 || $getValueDate['wday'] == 6) {
  //               $column_weekday         = $this->stringFromColumnIndex($start_col);
  //               $worksheet->getStyle($column_weekday.($start_row+1).":".$column_weekday.($start_row+17))->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
  //               ->getStartColor()->setRGB('D9D9D9');
  //           }



  //           $start_col ++;
  //       }



  //       $maxCell = $worksheet->getHighestRowAndColumn();
  //       $headerStyle1 = array(
  //           'font'          => array(
  //               'bold'      => false,
  //           ),
  //           'alignment'     => array(
  //               'wrapText'  => true
  //           )
  //       );
  //       $maxCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCell['column']);
  //       $maxCol = $this->stringFromColumnIndex($maxCol -1);
  //       $worksheet->getStyle("A1:".$maxCol.$maxCell['row'])->applyFromArray($headerStyle1);
        
  //       $worksheet->getStyle("A1:".$maxCol.$maxCell['row'])->getBorders()
  //       ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


  //       $headerStyle = array(
  //               'font'          => array(
  //                   'bold'      => true,
  //                   'size'      => 16
  //               ),
  //               'alignment'     => array(
  //                   'wrapText'  => false
  //               )
  //           );
  //       foreach ($rowGroup as $value) {
  //           $worksheet->getStyle("A".$value.":B".$value)->applyFromArray($headerStyle);
  //           $worksheet->getStyle("A".$value.":".$maxCol.$value)->getFill()
  //               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
  //       }


  //       foreach ($borderThick as $value) {
  //           if ($value['row'] == 1) {
  //               //to dam border cuoi thang
  //               $worksheet->getStyle($value['col']."1:".$value['col'].$maxCell['row'])->getBorders()
  //               ->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
  //           }else{
  //               //to dam border cuoi ki due
  //               $worksheet->getStyle($value['col'].($value['row']+1).":".$value['col'].($value['row']+17))->getBorders()
  //               ->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
  //           }
  //       }
  //       $worksheet->getStyle("B1:B".$maxCell['row'])->getBorders()
  //               ->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        






  //       $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
  //   	$file_path = UPLOAD_PATH . "loan/export/" . 'DailyReportOfOSBlanceOfGroupBCDE.xlsx';
		// $writer->save($file_path);
		// echo json_encode(array("status" => 1, "data" => $file_path));
  //   }

  //   function stringFromColumnIndex($columnIndex) {
  //       return $this->excel->stringFromColumnIndex($columnIndex);
  //   }

}