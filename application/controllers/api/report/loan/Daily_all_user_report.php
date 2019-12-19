<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
Class Daily_all_user_report extends CI_Controller {

    private $collection                = "Daily_all_user_report";
    private $lnjc05_collection         = "LNJC05";
    private $zaccf_collection          = "ZACCF";
    private $sbv_collection            = "SBV";
    private $group_collection          = "Group_card";
    private $cdr_collection            = "worldfonepbxmanager";
    private $group_team_collection     = "Group";
    private $user_collection           = "User";
    private $ln3206_collection         = "LN3206F";
    private $duedate_collection        = "Report_due_date";
    private $diallist_collection       = "Diallist";
    private $diallist_detail_collection = "Diallist_detail";
    private $wo_monthly_collection     = "WO_monthly";
    private $wo_all_collection         = "Wo_all_prod";
    private $wo_payment_collection     = "Wo_payment";
    private $account_collection        = "List_of_account_in_collection";
    private $gl_collection             = "Report_input_payment_of_card";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->lnjc05_collection          = set_sub_collection($this->lnjc05_collection);
        $this->zaccf_collection           = set_sub_collection($this->zaccf_collection);
        $this->sbv_collection             = set_sub_collection($this->sbv_collection);
        $this->collection                 = set_sub_collection($this->collection);
        $this->group_collection           = set_sub_collection($this->group_collection);
        $this->cdr_collection             = set_sub_collection($this->cdr_collection);
        $this->group_team_collection      = set_sub_collection($this->group_team_collection);
        $this->user_collection            = set_sub_collection($this->user_collection);
        $this->ln3206_collection          = set_sub_collection($this->ln3206_collection);
        $this->duedate_collection         = set_sub_collection($this->duedate_collection);
        $this->diallist_collection        = set_sub_collection($this->diallist_collection);
        $this->diallist_detail_collection = set_sub_collection($this->diallist_detail_collection);
        $this->wo_monthly_collection      = set_sub_collection($this->wo_monthly_collection);
        $this->wo_all_collection          = set_sub_collection($this->wo_all_collection);
        $this->wo_payment_collection      = set_sub_collection($this->wo_payment_collection);
        $this->account_collection         = set_sub_collection($this->account_collection);
        $this->gl_collection         = set_sub_collection($this->gl_collection);
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
    

   function exportExcel()
   {
      $now = getdate();
      $date = date('d-m-Y',strtotime("-1 days"));

      $request = array('createdAt' => array('$gte' => strtotime($date)));
      $data = $this->mongo_db->where($request)->get($this->collection);
      $filename = "DAILY ALL USER REPORT.xlsx";
      $spreadsheet = new Spreadsheet();
      $spreadsheet->getProperties()
      ->setCreator("South Telecom")
      ->setLastModifiedBy("Thanh Hung")
      ->setTitle("DAILY ALL USER REPORT")
      ->setSubject("DAILY ALL USER REPORT")
      ->setDescription("Office 2007 XLSX, generated using PHP classes.")
      ->setKeywords("office 2007 openxml php")
      ->setCategory("Report");

      $worksheet = $spreadsheet->getSheet(0);
      $worksheet->setTitle('Daily All User');
      $fieldToCol = array();
      // Title row
      $row = 1;
      $worksheet->setCellValue("A1", "No");
      $worksheet->getColumnDimension('A')->setAutoSize(true);
      $worksheet->setCellValue("B1", "Date");
      $worksheet->getColumnDimension('B')->setAutoSize(false)->setWidth(25);
      $worksheet->mergeCells('C1:Y1')->setCellValue("C1", $date);
      $style = array('font' => array('bold' => true), 'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
      $worksheet->getStyle("C1")->applyFromArray($style);



      if($data) {
         $rowGroupA = $rowGroupB =$rowGroupC = $rowGroupD = $rowGroupE = 0;
         foreach ($data as $value) {
            if ($value['group'] == 'A') {
               $rowGroupA++;
            }else if ($value['group'] == 'B') {
               $rowGroupB++;
            }
            else if ($value['group'] == 'C') {
               $rowGroupC++;
            }else if ($value['group'] == 'D') {
               $rowGroupD++;
            }else if ($value['group'] == 'E') {
               $rowGroupE++;
            }
         }
         $rowHeader_1_A = 2;
         $rowHeader_2_A = 3;
         $rowA = 4;
         $rowHeader_1_B = $rowA + $rowGroupA;
         $rowHeader_2_B = $rowA + $rowGroupA + 1;
         $rowB = $rowA + $rowGroupA + 2;
         $rowHeader_1_C = $rowB + $rowGroupB;
         $rowHeader_2_C = $rowB + $rowGroupB + 1;
         $rowC = $rowB + $rowGroupB + 2;
         $rowHeader_1_D = $rowC + $rowGroupC;
         $rowHeader_2_D = $rowC + $rowGroupC + 1;
         $rowD = $rowC + $rowGroupC + 2;
         $rowHeader_1_E = $rowD + $rowGroupD;
         $rowHeader_2_E = $rowD + $rowGroupD + 1;
         $rowE = $rowD + $rowGroupD + 2;
         $rowHeader_1_F = $rowE + $rowGroupE;
         $rowHeader_2_F = $rowE + $rowGroupE + 1;
         $rowF = $rowE + $rowGroupE + 2;

         $team = $i = 1;
         foreach ($data as $value) {
            if ($value['group'] == 'A') {
               $rowHeader_1 = $rowHeader_1_A;
               $rowHeader_2 = $rowHeader_2_A;
               $row = $rowA;
               $rowA++;
            }else if ($value['group'] == 'B') {
               $rowHeader_1 = $rowHeader_1_B;
               $rowHeader_2 = $rowHeader_2_B;
               $row = $rowB;
               $rowB++;
            }
            else if ($value['group'] == 'C') {
               $rowHeader_1 = $rowHeader_1_C;
               $rowHeader_2 = $rowHeader_2_C;
               $row = $rowC;
               $rowC++;
            }else if ($value['group'] == 'D') {
               $rowHeader_1 = $rowHeader_1_D;
               $rowHeader_2 = $rowHeader_2_D;
               $row = $rowD;
               $rowD++;
            }else if ($value['group'] == 'E') {
               $rowHeader_1 = $rowHeader_1_E;
               $rowHeader_2 = $rowHeader_2_E;
               $row = $rowE;
               $rowE++;
            }else if ($value['group'] == 'F') {
               $rowHeader_1 = $rowHeader_1_F;
               $rowHeader_2 = $rowHeader_2_F;
               $row = $rowF;
               $rowF++;
            }
            $worksheet->mergeCells("A$rowHeader_1:B$rowHeader_2")->setCellValue("A$rowHeader_1", $value['group']." GROUP");
            $worksheet->mergeCells("C$rowHeader_1:C$rowHeader_2")->setCellValue("C$rowHeader_1", "Total handled accounts");
            $worksheet->getColumnDimension('C')->setAutoSize(true);
            $worksheet->mergeCells("D$rowHeader_1:D$rowHeader_2")->setCellValue("D$rowHeader_1", "Unwork accounts");
            $worksheet->getColumnDimension('D')->setAutoSize(true);
            $worksheet->mergeCells("E$rowHeader_1:E$rowHeader_2")->setCellValue("E$rowHeader_1", "Talk time (minutes)");
            $worksheet->getColumnDimension('E')->setAutoSize(true);
            $worksheet->mergeCells("F$rowHeader_1:G$rowHeader_1")->setCellValue("F$rowHeader_1", "Contacted");
            $worksheet->setCellValue("F$rowHeader_2", "No.of accounts");
            $worksheet->setCellValue("G$rowHeader_2", "No.of amount");
            $worksheet->getColumnDimension('F')->setAutoSize(true);
            $worksheet->getColumnDimension('G')->setAutoSize(true);
            $worksheet->mergeCells("H$rowHeader_1:I$rowHeader_1")->setCellValue("H$rowHeader_1", "Spin");
            $worksheet->setCellValue("H$rowHeader_2", "No.of accounts");
            $worksheet->setCellValue("I$rowHeader_2", "No.of amount");
            $worksheet->getColumnDimension('H')->setAutoSize(true);
            $worksheet->getColumnDimension('I')->setAutoSize(true);
            $worksheet->mergeCells("J$rowHeader_1:K$rowHeader_1")->setCellValue("J$rowHeader_1", "Promise to pay");
            $worksheet->setCellValue("J$rowHeader_2", "No.of accounts");
            $worksheet->setCellValue("K$rowHeader_2", "No.of amount");
            $worksheet->getColumnDimension('J')->setAutoSize(true);
            $worksheet->getColumnDimension('K')->setAutoSize(true);
            $worksheet->mergeCells("L$rowHeader_1:M$rowHeader_1")->setCellValue("L$rowHeader_1", "Connected");
            $worksheet->setCellValue("L$rowHeader_2", "No.of accounts");
            $worksheet->setCellValue("M$rowHeader_2", "No.of amount");
            $worksheet->getColumnDimension('L')->setAutoSize(true);
            $worksheet->getColumnDimension('M')->setAutoSize(true);
            $worksheet->mergeCells("N$rowHeader_1:Q$rowHeader_1")->setCellValue("N$rowHeader_1", "Paid");
            $worksheet->setCellValue("N$rowHeader_2", "No.of accounts");
            $worksheet->setCellValue("O$rowHeader_2", "Actual Amount received");
            $worksheet->setCellValue("P$rowHeader_2", "No.of accounts (keep promise to pay)");
            $worksheet->setCellValue("Q$rowHeader_2", "Actual Amount received (keep promise to pay)");
            $worksheet->setCellValue("R$rowHeader_1", "Spin rate");
            $worksheet->setCellValue("R$rowHeader_2", "Account");
            $worksheet->getColumnDimension('N')->setAutoSize(true);
            $worksheet->getColumnDimension('O')->setAutoSize(true);
            $worksheet->getColumnDimension('P')->setAutoSize(true);
            $worksheet->getColumnDimension('Q')->setAutoSize(true);
            $worksheet->getColumnDimension('R')->setAutoSize(true);
            $worksheet->mergeCells("S$rowHeader_1:V$rowHeader_1")->setCellValue("S$rowHeader_1", "PTP rate");
            $worksheet->setCellValue("S$rowHeader_2", "PTP rate (Promised accounts)");
            $worksheet->setCellValue("T$rowHeader_2", "PTP rate (PromisedAmount)");
            $worksheet->setCellValue("U$rowHeader_2", "PTP rate (total paid accounts)");
            $worksheet->setCellValue("V$rowHeader_2", "PTP rate (total paid amount)");
            $worksheet->setCellValue("W$rowHeader_1", "Connected rate");
            $worksheet->setCellValue("W$rowHeader_2", "Account");
            $worksheet->getColumnDimension('S')->setAutoSize(true);
            $worksheet->getColumnDimension('T')->setAutoSize(true);
            $worksheet->getColumnDimension('U')->setAutoSize(true);
            $worksheet->getColumnDimension('V')->setAutoSize(true);
            $worksheet->getColumnDimension('W')->setAutoSize(true);
            $worksheet->mergeCells("X$rowHeader_1:Y$rowHeader_1")->setCellValue("X$rowHeader_1", "Collected ratio");
            $worksheet->setCellValue("X$rowHeader_2", "Account");
            $worksheet->setCellValue("Y$rowHeader_2", "Amount");
            $worksheet->getColumnDimension('Y')->setAutoSize(true);

            $worksheet->getStyle("A$rowHeader_1:Y$rowHeader_2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
            $style = array('font' => array('bold' => true), 'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
            $worksheet->getStyle("A$rowHeader_1:Y$rowHeader_2")->applyFromArray($style);



            if (isset($value['team_lead'])) {
               $worksheet->getStyle("A"."$row".":Y"."$row")->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('FCE4D6');
                  $team = $value['team'];
                  $i = 1;
               $worksheet->mergeCells("A$row:B$row")->setCellValue("A".$row, $value['name']);
            }else if($value['team'] == $team || $value['group'] !='F'){
               $worksheet->setCellValue("A".$row, $i);
               $i++;
               $worksheet->setCellValue("B".$row, $value['name']);
            }else if ($value['group'] == 'F') {
              $i = 1;
              $worksheet->setCellValue("A".$row, $i);
               $i++;
               $worksheet->setCellValue("B".$row, $value['name']);
            }
            $worksheet->setCellValue("C".$row, $value['count_data']);
            $worksheet->setCellValue("D".$row, $value['unwork']);
            $worksheet->setCellValue("E".$row, isset($value['talk_time']) ? $value['talk_time'] : 0);
            $worksheet->setCellValue("F".$row, isset($value['total_call']) ? $value['total_call'] : 0);
            $worksheet->setCellValue("G".$row, isset($value['total_amount']) ? $value['total_amount'] : 0);
            $worksheet->setCellValue("H".$row, isset($value['count_spin']) ? $value['count_spin'] : 0);
            $worksheet->setCellValue("I".$row, isset($value['spin_amount']) ? $value['spin_amount'] : 0);
            $worksheet->setCellValue("J".$row, isset($value['count_ptp']) ? $value['count_ptp'] : 0);
            $worksheet->setCellValue("K".$row, isset($value['ptp_amount']) ? $value['ptp_amount'] : 0);
            $worksheet->setCellValue("L".$row, isset($value['count_conn']) ? $value['count_conn'] : 0);
            $worksheet->setCellValue("M".$row, isset($value['conn_amount']) ? $value['conn_amount'] : 0);
            $worksheet->setCellValue("N".$row, isset($value['count_paid']) ? $value['count_paid'] : 0);
            $worksheet->setCellValue("O".$row, isset($value['paid_amount']) ? $value['paid_amount'] : 0);
            $worksheet->setCellValue("P".$row, isset($value['count_paid_promise']) ? $value['count_paid_promise'] : 0);
            $worksheet->setCellValue("Q".$row, isset($value['paid_amount_promise']) ? $value['paid_amount_promise'] : 0);
            $worksheet->setCellValue("R".$row, isset($value['spin_rate']) ? $value['spin_rate'] : 0);
            $worksheet->setCellValue("S".$row, isset($value['ptp_rate_acc']) ? $value['ptp_rate_acc'] : 0);
            $worksheet->setCellValue("T".$row, isset($value['ptp_rate_amt']) ? $value['ptp_rate_amt'] : 0);
            $worksheet->setCellValue("U".$row, isset($value['paid_rate_acc']) ? $value['paid_rate_acc'] : 0);
            $worksheet->setCellValue("V".$row, isset($value['paid_rate_amt']) ? $value['paid_rate_amt'] : 0);
            $worksheet->setCellValue("W".$row, isset($value['conn_rate']) ? $value['conn_rate'] : 0);
            $worksheet->setCellValue("X".$row, isset($value['collect_ratio_acc']) ? $value['collect_ratio_acc'] : 0);
            $worksheet->setCellValue("Y".$row, isset($value['collect_ratio_amt']) ? $value['collect_ratio_amt'] : 0);
            $row++;


         }
      }
      $total_row = count($data)+13;
      $worksheet->getStyle("A1:Y$total_row")->getBorders()
      ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $file_path = UPLOAD_PATH . "loan/export/" . $filename;
      $writer->save($file_path);
      // print_r($file_path);
      echo json_encode(array("status" => 1, "data" => $file_path));

   }

    function downloadExcel()
    {
        // $file_path = $this->exportExcel();
        $file_path = UPLOAD_PATH . "loan/export/DAILY ALL USER REPORT.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}