<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
Class Daily_working_days_report extends WFF_Controller {

    private $collection             = "Daily_prod_working_day";
   

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->load->library("mongo_db");
        $this->load->library("mongo_private");
        $this->collection           = set_sub_collection($this->collection);
        
    }

    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $date = date('d-m-Y',strtotime("today"));
            
            $match = array('updated_at' => array('$gte' => strtotime($date)));
            $response = $this->crud->read($this->collection, $request,array(),$match);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function save()
    {
        $this->mongo_db->switch_db('_worldfone4xs');
        $groupProducts = $this->mongo_db->where(array('tags'=> ['group', 'debt', 'product'] ))->get($this->jsonData_collection);
        $this->mongo_db->switch_db();
        print_r($groupProducts);
    }

    // function exportExcel() {
    //     ini_set("display_errors", 1);
    //     ini_set("display_startup_errors", 1);
    //     error_reporting(E_ALL);
    //     $request = json_decode($this->input->get("q"), TRUE);
    //     // $request = array();
    //     $date = date('d-m-Y',strtotime("today"));
    //     $match = array('updated_at' => array('$gte' => strtotime($date)));
    //     $data = $this->crud->where($match)->order_by(array('debt_group' => 'asc', 'due_date_code' => 'asc', 'due_date' => 'asc', 'product' => 'desc', 'team' => 'asc'))->get($this->collection);
    //     // print_r($data);
    //     $product = $this->crud->order_by(array('code' => 'asc'))->get(set_sub_collection('Product'));
    //     $groupProduct = $this->mongo_private->where(array('tags' => array('group', 'debt', 'product')))->getOne(set_sub_collection("Jsondata"));
    //     $this->crud->read($this->collection, $request);

    //     $spreadsheet = new Spreadsheet();
    //     $spreadsheet->getProperties()
    //     ->setCreator("South Telecom")
    //     ->setLastModifiedBy("Tri Dung")
    //     ->setTitle("Report")
    //     ->setSubject("Report")
    //     ->setDescription("Office 2007 XLSX, generated using PHP classes.")
    //     ->setKeywords("office 2007 openxml php")
    //     ->setCategory("Report");
        
    //     $style = array(
    //         'alignment'     => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER),
    //         'allborders'    => array(
    //             'style'     => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //             'color'     => array('rgb' => '000000')
    //         )
    //     );

    //     $worksheet = $spreadsheet->getActiveSheet();

    //     $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);
    //     $worksheet->getDefaultColumnDimension()->setWidth(15);
    //     // $worksheet->setCellValue('A1', 'A GROUP');
    //     $worksheet->getStyle("A1")->getFill()
    //         ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //         ->getStartColor()->setRGB('FFFF00');

    //     $worksheet->mergeCells('F1:G1');
    //     $worksheet->setCellValue('F1', 'Start');
    //     $worksheet->mergeCells('H1:I1');
    //     $worksheet->setCellValue('H1', 'Target');
    //     $worksheet->setCellValue('A2', 'Group');
    //     $worksheet->setCellValue('B2', 'Due');
    //     $worksheet->setCellValue('C2', 'Product');
    //     $worksheet->setCellValue('D2', 'Due date');
    //     $worksheet->setCellValue('E2', 'GROUP');
    //     $worksheet->setCellValue('F2', 'Accounts');
    //     $worksheet->setCellValue('G2', 'Amount');
    //     $worksheet->setCellValue('H2', 'Accounts');
    //     $worksheet->setCellValue('I2', 'Amount');
    //     $worksheet->setCellValue('J2', 'Day');
    //     $worksheet->getColumnDimension('J')->setWidth(40);
    //     $startNumber = 10;
    //     for ($i=1; $i <=23 ; $i++) { 
    //         $startNumber ++;
    //         $worksheet->setCellValue($this->stringFromColumnIndex($startNumber) . '2', $i);
    //     }
    //     $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1) . '2', 'Final number');

    //     $worksheet->getStyle("A2:E2")->getFill()
    //         ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //         ->getStartColor()->setRGB('FFFF00');
    //     $worksheet->getStyle("F1:I2")->getFill()
    //     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //     ->getStartColor()->setRGB('FFC000');
    //     $worksheet->getStyle("J2:".$this->stringFromColumnIndex($startNumber + 1) . '2')->getFill()
    //         ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //         ->getStartColor()->setRGB('FFFF00');
        
    //     foreach(range('C','AH') as $columnID) {
    //         $worksheet->getColumnDimension($columnID)->setAutoSize(true);
    //     }
        
    //     $headerStyle = array(
    //         'font'          => array(
    //             'bold'      => true,
    //         ),
    //         'alignment'     => array(
    //             'wrapText'  => true
    //         )
    //     );
    //     $worksheet->getStyle("A1:" . $this->stringFromColumnIndex($startNumber + 1) . '2')->applyFromArray($headerStyle);
        
    //     $start_row = 3;

    //     $start_row_day = 3;
    //     $day = $data[0]['day'];

    //     // $start_row_due_date_code = 3;
    //     $due = $data[0]['due'];

    //     $start_row_prod = 3;
    //     $prod_row = $data[0]['product'];

    //     $start_due_date = 3;
    //     $due_date = $data[0]['due_date'];

    //     $start_month = 3;
    //     $month = $data[0]['month'];
    //     // $due_date_1 = date('d/m/Y',  $data[0]['due_date']);
    //     $start_group = 3;
    //     $group = $data[0]['group'];

    //     print_r('TEST');

    //     foreach($data as $key => $value) {
            
    //         if($group != $value['group']) {
    //             $worksheet->mergeCells('A' . $start_group . ':A' . ($start_row - 1));
    //             $worksheet->setCellValue('A' . $start_group, $group);
                
    //             $worksheet->mergeCells('B' . $start_due_date . ':B' . ($start_row - 1) );
    //             $worksheet->setCellValue('B' . $start_due_date, $due);
    //             $worksheet->mergeCells('D' . $start_due_date . ':D' . ($start_row - 1));
    //             $worksheet->setCellValue('D' . $start_due_date, $due_date);
                
    //             $due = $value['due'];
    //             $due_date = $value['due_date'];
    //             $start_due_date = $start_row;

    //             $group = $value['group'];
    //             $start_group = $start_row;
    //         }

    //         if($due_date != $value['due_date']) {
    //             $worksheet->mergeCells('B' . $start_due_date . ':B' . ($start_row - 1) );
    //             $worksheet->setCellValue('B' . $start_due_date, $due);
    //             $worksheet->mergeCells('D' . $start_due_date . ':D' . ($start_row - 1));
    //             $worksheet->setCellValue('D' . $start_due_date, $due_date);
                
    //             $due = $value['due'];
    //             $due_date = $value['due_date'];
    //             $start_due_date = $start_row;
    //         }

    //         if($prod_row != $value['product'] ) {
    //             // $worksheet->mergeCells('B' . $start_row_prod . ':B' . ($start_row - 1) );
    //             // $worksheet->setCellValue('B' . $start_row_prod, $due);
    //             $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row - 1) );
    //             $worksheet->setCellValue('C' . $start_row_prod, $prod_row);
    //             // $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row - 1) );
    //             // $worksheet->setCellValue('D' . $start_row_prod, $due_date);

    //             // $due = $value['due'];
    //             $prod_row = $value['product'];
    //             // $due_date = $value['due_date'];
    //             $start_row_prod = $start_row;
    //         }
    
    //         if ($day != $value['day']) {
    //             $worksheet->mergeCells('J' . $start_row_day . ':J' .($start_row - 1) );
    //             $worksheet->setCellValue('J' . $start_row_day, $day);

    //             $day = $value['day'];
    //             $start_row_day = $start_row ;
    //         }

    //         if(!empty($value['team_name'])) {
    //             if(strpos($value['team_name'], 'G1') !== false || strpos($value['team_name'], 'G3') !== false) {
    //                 continue;
    //             }
    
    //             if(strpos($value['team_name'], 'G2') !== false) {
    //                 $groupName = explode("/G2", $value['team_name']);
    //                 $value['team_name'] = $groupName[0];
    //             }
    //         }

    //         $worksheet->setCellValue('A' . $start_row, $value['month']);
    //         $worksheet->setCellValue('E' . $start_row, (!empty($value['team_name']) ? $value['team_name'] : 0));
    //         $worksheet->getStyle('E' . $start_row)->getAlignment()->setHorizontal('left');
    //         $worksheet->setCellValue('F' . $start_row, (!empty($value['start_acc']) ? $value['start_acc'] : 0));
    //         $worksheet->getStyle('F' . $start_row)->getNumberFormat()
    //         ->setFormatCode('#,##0');
    //         $worksheet->getStyle('F' . $start_row)->getAlignment()->setHorizontal('right');
    //         $worksheet->setCellValue('G' . $start_row, (!empty($value['start_amt']) ? $value['start_amt'] : 0));
    //         $worksheet->getStyle('G' . $start_row)->getNumberFormat()
    //         ->setFormatCode('#,##0');
    //         $worksheet->getStyle('G' . $start_row)->getAlignment()->setHorizontal('right');
    //         $worksheet->setCellValue('H' . $start_row, (!empty($value['tar_acc']) ? $value['tar_acc'] : 0));
    //         $worksheet->getStyle('H' . $start_row)->getNumberFormat()
    //         ->setFormatCode('#,##0');
    //         $worksheet->getStyle('H' . $start_row)->getAlignment()->setHorizontal('right');
    //         $worksheet->setCellValue('I' . $start_row, (!empty($value['tar_amt']) ? $value['tar_amt'] : 0));
    //         $worksheet->getStyle('I' . $start_row)->getNumberFormat()
    //         ->setFormatCode('#,##0');
    //         $worksheet->getStyle('I' . $start_row)->getAlignment()->setHorizontal('right');
    //         // $worksheet->setCellValue('J' . $start_row, (!empty($value['day']) ? $value['day'] : 0));
    //         $worksheet->getStyle("F".$start_row.":I".$start_row)->getFill()
    //             ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //             ->getStartColor()->setRGB('FFC000');

    //         $startIndex = 10;
    //         for ($i=1; $i <=23 ; $i++) { 
    //             $startIndex ++;
    //             $worksheet->setCellValue($this->stringFromColumnIndex($startIndex) . $start_row, (!empty($value['index_'.$i]) ? $value['index_'.$i] : 0));
    //             $worksheet->getStyle($this->stringFromColumnIndex($startIndex) . $start_row)->getNumberFormat()
    //             ->setFormatCode('#,##0');
    //             $worksheet->getStyle($this->stringFromColumnIndex($startIndex) . $start_row)->getAlignment()->setHorizontal('right');
    //         }
    //         $worksheet->setCellValue($this->stringFromColumnIndex($startIndex + 1) . $start_row, (!empty($value['final_num']) ? $value['final_num'] : 0));


    //         if ($value['team_name'] == 'TOTAL') {
    //             $worksheet->getStyle('E' . $start_row)->getFill()
    //             ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //             ->getStartColor()->setRGB('E2EFDA');
    //             $worksheet->getStyle("F".$start_row.":I".$start_row)->getFill()
    //             ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //             ->getStartColor()->setRGB('FFC000');
    //             $worksheet->getStyle('K'. $start_row.":AH".$start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC000');
    //         }

    //         if ($key == (count($data) - 1)) {
    //             $worksheet->mergeCells('A' . $start_group . ':A' . $start_row  );
    //             $worksheet->setCellValue('A' . $start_group, $group);
    //             $worksheet->mergeCells('B' . $start_due_date . ':B' . $start_row );
    //             $worksheet->setCellValue('B' . $start_due_date, $due);
    //             $worksheet->mergeCells('C' . $start_row_prod . ':C' . $start_row );
    //             $worksheet->setCellValue('C' . $start_row_prod, $prod_row);
    //             $worksheet->mergeCells('D' . $start_due_date . ':D' . $start_row );
    //             $worksheet->setCellValue('D' . $start_due_date, $due_date);
    //             $worksheet->mergeCells('J' . $start_row_day . ':J' .$start_row  );
    //             $worksheet->setCellValue('J' . $start_row_day, $day);

                
    //         }
    //         $start_row += 1;

    //     }

    //     $maxCell = $worksheet->getHighestRowAndColumn();
    //     $worksheet->getStyle("A1:AH".$maxCell['row'])->getBorders()
    //     ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    //     $file_path = UPLOAD_PATH . "loan/export/" . 'Daily_working_days.xlsx';
    //     $writer->save($file_path);
    //     echo json_encode(array("status" => 1, "data" => $file_path));
    // }

    function exportExcel() {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        $request = json_decode($this->input->get("q"), TRUE);
        // $request = array();
        $date = date('d-m-Y',strtotime("today"));
        $match = array('updated_at' => array('$gte' => strtotime($date)));
        $data = $this->crud->where($match)->order_by(array('debt_group' => 'asc', 'due_date_code' => 'asc', 'due_date' => 'asc', 'product' => 'desc', 'team' => 'asc'))->get($this->collection);
        // print_r($data);
        $product = $this->crud->order_by(array('code' => 'asc'))->get(set_sub_collection('Product'));
        $groupProduct = $this->mongo_private->where(array('tags' => array('group', 'debt', 'product')))->getOne(set_sub_collection("Jsondata"));
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator("South Telecom")
        ->setLastModifiedBy("Tri Dung")
        ->setTitle("Report")
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
        $worksheet->getDefaultColumnDimension()->setWidth(15);
        // $worksheet->setCellValue('A1', 'A GROUP');
        $worksheet->getStyle("A1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');

        $worksheet->mergeCells('F1:G1');
        $worksheet->setCellValue('F1', 'Start');
        $worksheet->mergeCells('H1:I1');
        $worksheet->setCellValue('H1', 'Target');
        $worksheet->setCellValue('A2', 'Group');
        $worksheet->setCellValue('B2', 'Due');
        $worksheet->setCellValue('C2', 'Product');
        $worksheet->setCellValue('D2', 'Due date');
        $worksheet->setCellValue('E2', 'GROUP');
        $worksheet->setCellValue('F2', 'Accounts');
        $worksheet->setCellValue('G2', 'Amount');
        $worksheet->setCellValue('H2', 'Accounts');
        $worksheet->setCellValue('I2', 'Amount');
        $worksheet->setCellValue('J2', 'Day');
        $worksheet->getColumnDimension('J')->setWidth(40);
        $startNumber = 10;
        for ($i=1; $i <=23 ; $i++) { 
            $startNumber ++;
            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber) . '2', $i);
        }
        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1) . '2', 'Final number');

        $worksheet->getStyle("A2:E2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("F1:I2")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("J2:".$this->stringFromColumnIndex($startNumber + 1) . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        
        foreach(range('C','AH') as $columnID) {
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
        $worksheet->getStyle("A1:" . $this->stringFromColumnIndex($startNumber + 1) . '2')->applyFromArray($headerStyle);
        
        $start_row = 3;

        $start_row_day = 3;
        $day = $data[0]['day'];

        // $start_row_due_date_code = 3;
        $due = $data[0]['due'];

        $start_row_prod = 3;
        $prod_row = $data[0]['product'];

        $start_due_date = 3;
        $due_date = $data[0]['due_date'];

        $start_month = 3;
        $month = $data[0]['month'];
        // $due_date_1 = date('d/m/Y',  $data[0]['due_date']);
        $start_group = 3;
        $group = $data[0]['group'];

        foreach($data as $key => $value) {
            if($group != $value['group']) {
                $worksheet->mergeCells('A' . $start_group . ':A' . ($start_row - 1));
                $worksheet->setCellValue('A' . $start_group, $group);
                
                $worksheet->mergeCells('B' . $start_due_date . ':B' . ($start_row - 1) );
                $worksheet->setCellValue('B' . $start_due_date, $due);
                $worksheet->mergeCells('D' . $start_due_date . ':D' . ($start_row - 1));
                $worksheet->setCellValue('D' . $start_due_date, $due_date);
                
                $due = $value['due'];
                $due_date = $value['due_date'];
                $start_due_date = $start_row;

                $group = $value['group'];
                $start_group = $start_row;
            }

            if($due_date != $value['due_date']) {
                $worksheet->mergeCells('B' . $start_due_date . ':B' . ($start_row - 1) );
                $worksheet->setCellValue('B' . $start_due_date, $due);
                $worksheet->mergeCells('D' . $start_due_date . ':D' . ($start_row - 1));
                $worksheet->setCellValue('D' . $start_due_date, $due_date);
                
                $due = $value['due'];
                $due_date = $value['due_date'];
                $start_due_date = $start_row;
            }

            if($prod_row != $value['product'] ) {
                // $worksheet->mergeCells('B' . $start_row_prod . ':B' . ($start_row - 1) );
                // $worksheet->setCellValue('B' . $start_row_prod, $due);
                $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row - 1) );
                $worksheet->setCellValue('C' . $start_row_prod, $prod_row);
                // $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row - 1) );
                // $worksheet->setCellValue('D' . $start_row_prod, $due_date);

                // $due = $value['due'];
                $prod_row = $value['product'];
                // $due_date = $value['due_date'];
                $start_row_prod = $start_row;
            }
    
            if ($day != $value['day']) {
                $worksheet->mergeCells('J' . $start_row_day . ':J' .($start_row - 1) );
                $worksheet->setCellValue('J' . $start_row_day, $day);

                $day = $value['day'];
                $start_row_day = $start_row ;
            }

            if(!empty($value['team_name'])) {
                if(strpos($value['team_name'], 'G1') !== false || strpos($value['team_name'], 'G3') !== false) {
                    continue;
                }
    
                if(strpos($value['team_name'], 'G2') !== false) {
                    $groupName = explode("/G2", $value['team_name']);
                    $value['team_name'] = $groupName[0];
                }
            }

            $worksheet->setCellValue('A' . $start_row, $value['month']);
            $worksheet->setCellValue('E' . $start_row, (!empty($value['team_name']) ? $value['team_name'] : 0));
            $worksheet->getStyle('E' . $start_row)->getAlignment()->setHorizontal('left');
            $worksheet->setCellValue('F' . $start_row, (!empty($value['start_acc']) ? $value['start_acc'] : 0));
            $worksheet->getStyle('F' . $start_row)->getNumberFormat()
            ->setFormatCode('#,##0');
            $worksheet->getStyle('F' . $start_row)->getAlignment()->setHorizontal('right');
            $worksheet->setCellValue('G' . $start_row, (!empty($value['start_amt']) ? $value['start_amt'] : 0));
            $worksheet->getStyle('G' . $start_row)->getNumberFormat()
            ->setFormatCode('#,##0');
            $worksheet->getStyle('G' . $start_row)->getAlignment()->setHorizontal('right');
            $worksheet->setCellValue('H' . $start_row, (!empty($value['tar_acc']) ? $value['tar_acc'] : 0));
            $worksheet->getStyle('H' . $start_row)->getNumberFormat()
            ->setFormatCode('#,##0');
            $worksheet->getStyle('H' . $start_row)->getAlignment()->setHorizontal('right');
            $worksheet->setCellValue('I' . $start_row, (!empty($value['tar_amt']) ? $value['tar_amt'] : 0));
            $worksheet->getStyle('I' . $start_row)->getNumberFormat()
            ->setFormatCode('#,##0');
            $worksheet->getStyle('I' . $start_row)->getAlignment()->setHorizontal('right');
            // $worksheet->setCellValue('J' . $start_row, (!empty($value['day']) ? $value['day'] : 0));
            $worksheet->getStyle("F".$start_row.":I".$start_row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFC000');

            $startIndex = 10;
            for ($i=1; $i <=23 ; $i++) { 
                $startIndex ++;
                $valueByIndex = (!empty($value['index_'.$i])) ? $value['index_'.$i] : '';
                // print_r($valueByIndex);
                $worksheet->setCellValue($this->stringFromColumnIndex($startIndex) . $start_row, $valueByIndex);
                $worksheet->getStyle($this->stringFromColumnIndex($startIndex) . $start_row)->getNumberFormat()->setFormatCode('#,##0');
                $worksheet->getStyle($this->stringFromColumnIndex($startIndex) . $start_row)->getAlignment()->setHorizontal('right');
            }

            $worksheet->setCellValue($this->stringFromColumnIndex($startIndex + 1) . $start_row, (!empty($value['final_num']) ? $value['final_num'] : 0));

            if ($value['team_name'] == 'TOTAL') {
                $worksheet->getStyle('E' . $start_row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2EFDA');
                $worksheet->getStyle("F".$start_row.":I".$start_row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFC000');
                $worksheet->getStyle('K'. $start_row.":AH".$start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC000');
            }

            if ($key == (count($data) - 1)) {
                $worksheet->mergeCells('A' . $start_group . ':A' . $start_row  );
                $worksheet->setCellValue('A' . $start_group, $group);
                $worksheet->mergeCells('B' . $start_due_date . ':B' . $start_row );
                $worksheet->setCellValue('B' . $start_due_date, $due);
                $worksheet->mergeCells('C' . $start_row_prod . ':C' . $start_row );
                $worksheet->setCellValue('C' . $start_row_prod, $prod_row);
                $worksheet->mergeCells('D' . $start_due_date . ':D' . $start_row );
                $worksheet->setCellValue('D' . $start_due_date, $due_date);
                $worksheet->mergeCells('J' . $start_row_day . ':J' .$start_row  );
                $worksheet->setCellValue('J' . $start_row_day, $day);

                
            }
            
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:AH".$maxCell['row'])->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $file_path = UPLOAD_PATH . "loan/export/" . 'Daily_working_days.xlsx';
        $writer->save($file_path);
        echo json_encode(array("status" => 1, "data" => $file_path));
        // print_r('TEST');
    }

    function stringFromColumnIndex($columnIndex) {
        return $this->excel->stringFromColumnIndex($columnIndex);
    }

}