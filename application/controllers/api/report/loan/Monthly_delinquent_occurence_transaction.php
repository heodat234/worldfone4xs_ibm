<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

Class Monthly_delinquent_occurence_transaction extends WFF_Controller {

    private $collection = "Monthly_delinquent_occurence_transition";
    private $collection_total = "Monthly_delinquent_occurence_transition_total";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection = set_sub_collection($this->collection);
        $this->collection_total = set_sub_collection($this->collection_total);
    }

    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            // print_r($request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function read_total() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection_total, $request);
            // print_r($request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getListProductGroup() {
        try {
            $response = $this->mongo_db->order_by(array('group_code' => 1))->get(set_sub_collection('Product_group'));
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function exportExcel() {
        $year = date("Y");
        $filename = 'monthly_delinquent_occurence_transaction.xlsx';
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator("South Telecom")
        ->setTitle("DELINQUENT OCCURRENCE TRANSITION TABLE BY CONDITION")
        ->setSubject("Delinquency occurrence transition table by loan terms")
        ->setDescription("Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Report");

        // Set format for whole excel
        $styleArray = array(
            'alignment' => array(
                'horizontal'    => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'      => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'      => TRUE
            )
        );
        $spreadsheet->getDefaultStyle()->applyFromArray($styleArray);

        // Sheet name
        $worksheet = $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet = $spreadsheet->getSheet(0);
        $worksheet->setTitle($year);

        // Title name
        $worksheet->setCellValue("A1", "Delinquency occurrence transition table by loan terms  (Amount of money)");
        $worksheet->setCellValue("A2", "貸付条件別滞納発生推移表　（金額）");
        $worksheet->getStyle("A1")->getFont()->setName('Arial');
        $worksheet->getStyle("A1")->getFont()->setSize(13);
        $worksheet->getStyle("A2")->getFont()->setName('MS PGothic');
        $worksheet->getStyle("A2")->getFont()->setSize(13);

        $worksheet->getStyle("A1:A2")->getFont()->setBold(true);
        $worksheet->getStyle("A1:A2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $worksheet->getStyle("A1:A2")->getAlignment()->setWrapText(FALSE);

        // Data
        $row = 4;
        $col = 3;

        $group_prod = $this->mongo_db->order_by(array('group_code' => 1))->get(set_sub_collection('Product_group'));

        $list_month_of_year = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

        foreach($list_month_of_year as $month) {
            $row = 4;
            // Set number format
            $worksheet->getStyle($this->stringFromColumnIndex($col))->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle($this->stringFromColumnIndex($col + 1))->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle($this->stringFromColumnIndex($col + 2))->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle($this->stringFromColumnIndex($col + 3))->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle($this->stringFromColumnIndex($col + 4))->getNumberFormat()->setFormatCode('0.00%');
            $worksheet->getStyle($this->stringFromColumnIndex($col + 5))->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle($this->stringFromColumnIndex($col + 6))->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle($this->stringFromColumnIndex($col + 7))->getNumberFormat()->setFormatCode('0.00%');

            // Set number cell alignment
            $worksheet->getStyle($this->stringFromColumnIndex($col))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $worksheet->getStyle($this->stringFromColumnIndex($col + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $worksheet->getStyle($this->stringFromColumnIndex($col + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $worksheet->getStyle($this->stringFromColumnIndex($col + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $worksheet->getStyle($this->stringFromColumnIndex($col + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $worksheet->getStyle($this->stringFromColumnIndex($col + 6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            foreach($group_prod as $key => $value) {
                // Header 1
                $worksheet->mergeCells($this->stringFromColumnIndex($col) . $row . ":" . $this->stringFromColumnIndex($col + 7) . $row);
                $worksheet->setCellValue($this->stringFromColumnIndex($col) . $row, $month . '/' . $year);

                // Header 2
                $worksheet->mergeCells($this->stringFromColumnIndex($col) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 1) . ($row + 1));
                $worksheet->setCellValue($this->stringFromColumnIndex($col) . ($row + 1), 'Total');

                $worksheet->mergeCells($this->stringFromColumnIndex($col + 2) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 4) . ($row + 1));
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 2) . ($row + 1), 'Group 2');

                $worksheet->mergeCells($this->stringFromColumnIndex($col + 5) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 7) . ($row + 1));
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 5) . ($row + 1), 'Group 3 and over');

                // Header 3
                $worksheet->setCellValue($this->stringFromColumnIndex($col) . ($row + 2), 'Total W-ORG');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 1) . ($row + 2), 'No.of Account');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 2) . ($row + 2), 'Overdue W-ORG');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 3) . ($row + 2), 'No.of Account');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 4) . ($row + 2), 'Overdue ratio');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 5) . ($row + 2), 'Overdue W-ORG');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 6) . ($row + 2), 'No.of Account');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 7) . ($row + 2), 'Overdue ratio');
                $worksheet->getStyle($this->stringFromColumnIndex($col) . ($row) . ':' . $this->stringFromColumnIndex($col + 7) . ($row + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Header
                $worksheet->mergeCells("A" . ($row) . ":B" . ($row));
                $worksheet->setCellValue("A" . ($row), "Unit : Mill . VND");
                $worksheet->mergeCells("A" . ($row + 1) . ":B" . ($row + 2));
                $worksheet->setCellValue("A" . ($row + 1), $value["group_name"]);
                $worksheet->getStyle('A' . ($row) . ':A' . ($row + 1))->getFont()->setBold(true);

                // Border
                $worksheet->getStyle('A' . ($row + 1) . ':' . 'B' . ($row + 2))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                if($value['group_code'] != '300') {
                    $prod_group_data = $this->mongo_db->where(array('year' => (int)$year, 'group_code' => $value['group_code']))->order_by(array('int_rate' => 1))->get($this->collection);
                    foreach($prod_group_data as $report_row_key => $report_row_value) {
                        // Int rate col
                        $worksheet->mergeCells("A" . ($row + 3 + $report_row_key) . ":B" . ($row + 3 + $report_row_key));
                        if(is_numeric($report_row_value['int_rate_name'])) {
                            $worksheet->setCellValue("A" . ($row + 3 + $report_row_key), ($report_row_value['int_rate_name'] / 12));
                            $worksheet->getStyle('A')->getNumberFormat()->setFormatCode('0.00%');
                        }
                        else {
                            $worksheet->setCellValue("A" . ($row + 3 + $report_row_key), $report_row_value['int_rate_name']);
                        }

                        // Fill cell data
                        $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col) . ($row + 3 + $report_row_key), (isset($report_row_value['total_w_org_' . $month . '_' . $year])) ? round($report_row_value['total_w_org_' . $month . '_' . $year] / 1000000) : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 1) . ($row + 3 + $report_row_key), (isset($report_row_value['total_acc_count_' . $month . '_' . $year])) ? $report_row_value['total_acc_count_' . $month . '_' . $year] : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 2) . ($row + 3 + $report_row_key), (isset($report_row_value['group_2_w_org_' . $month . '_' . $year])) ? round($report_row_value['group_2_w_org_' . $month . '_' . $year] / 1000000) : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 3) . ($row + 3 + $report_row_key), (isset($report_row_value['group_2_acc_count_' . $month . '_' . $year])) ? $report_row_value['group_2_acc_count_' . $month . '_' . $year] : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $worksheet->setCellValue($this->stringFromColumnIndex($col + 4) . ($row + 3 + $report_row_key), '=' . $this->stringFromColumnIndex($col + 2) . ($row + 3 + $report_row_key) . '/' . $this->stringFromColumnIndex($col) . ($row + 3 + $report_row_key));
                        $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 5) . ($row + 3 + $report_row_key), (isset($report_row_value['group_3_over_w_org_' . $month . '_' . $year])) ? round($report_row_value['group_3_over_w_org_' . $month . '_' . $year] / 1000000) : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 6) . ($row + 3 + $report_row_key), (isset($report_row_value['group_3_over_acc_count_' . $month . '_' . $year])) ? $report_row_value['group_3_over_acc_count_' . $month . '_' . $year] : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        $worksheet->setCellValue($this->stringFromColumnIndex($col + 7) . ($row + 3 + $report_row_key), '=' . $this->stringFromColumnIndex($col + 5) . ($row + 3 + $report_row_key) . '/' . $this->stringFromColumnIndex($col) . ($row + 3 + $report_row_key));
                    }

                    // Set border
                    $worksheet->getStyle('A' . ($row + 3) . ':' . 'B' . ($row + 3 + $report_row_key))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $worksheet->getStyle($this->stringFromColumnIndex($col) . $row . ':' . ($this->stringFromColumnIndex($col + 7)) . ($row + 3 + $report_row_key))->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                    $worksheet->getStyle($this->stringFromColumnIndex($col) . $row . ':' . ($this->stringFromColumnIndex($col + 7)) . ($row + 3 + $report_row_key))->getBorders()->getInside()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $row += (count($prod_group_data) + 4);
                }
            }

            // Card Product
            // Header 1
            $worksheet->mergeCells($this->stringFromColumnIndex($col) . $row . ":" . $this->stringFromColumnIndex($col + 7) . $row);
            $worksheet->setCellValue($this->stringFromColumnIndex($col) . $row, $month . '/' . $year);

            // Header 2
            $worksheet->mergeCells($this->stringFromColumnIndex($col) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 1) . ($row + 1));
            $worksheet->setCellValue($this->stringFromColumnIndex($col) . ($row + 1), 'Total');

            $worksheet->mergeCells($this->stringFromColumnIndex($col + 2) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 4) . ($row + 1));
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 2) . ($row + 1), 'Group 2');

            $worksheet->mergeCells($this->stringFromColumnIndex($col + 5) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 7) . ($row + 1));
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 5) . ($row + 1), 'Group 3 and over');

            // Header 3
            $worksheet->setCellValue($this->stringFromColumnIndex($col) . ($row + 2), 'Total W-ORG');
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 1) . ($row + 2), 'No.of Account');
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 2) . ($row + 2), 'Overdue W-ORG');
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 3) . ($row + 2), 'No.of Account');
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 4) . ($row + 2), 'Overdue ratio');
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 5) . ($row + 2), 'Overdue W-ORG');
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 6) . ($row + 2), 'No.of Account');
            $worksheet->setCellValue($this->stringFromColumnIndex($col + 7) . ($row + 2), 'Overdue ratio');
            $worksheet->getStyle($this->stringFromColumnIndex($col) . ($row) . ':' . $this->stringFromColumnIndex($col + 7) . ($row + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Header
            $worksheet->mergeCells("A" . ($row) . ":B" . ($row));
            $worksheet->setCellValue("A" . ($row), "Unit : Mill . VND");
            $worksheet->mergeCells("A" . ($row + 1) . ":B" . ($row + 2));
            $worksheet->setCellValue("A" . ($row + 1), 'Card');
            $worksheet->getStyle('A' . ($row) . ':A' . ($row + 1))->getFont()->setBold(true);

            // Border
            $worksheet->getStyle('A' . ($row + 1) . ':' . 'B' . ($row + 2))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $prod_group_data_card = $this->mongo_db->where(array('year' => (int)$year, 'group_code' => '300'))->order_by(array('int_rate_name' => -1))->get($this->collection);
            // Fill data
            foreach($prod_group_data_card as $report_row_card_key => $report_row_card_value) {
                // Int rate col
                $worksheet->mergeCells("A" . ($row + 3 + $report_row_card_key) . ":B" . ($row + 3 + $report_row_card_key));
                $worksheet->setCellValue("A" . ($row + 3 + $report_row_card_key), $report_row_card_value['int_rate_name']);
                if(is_numeric($report_row_card_value['int_rate_name'])) {
                    $worksheet->getStyle('A')->getNumberFormat()->setFormatCode('0.00%');
                }

                // Fill cell data
                $worksheet->setCellValue($this->stringFromColumnIndex($col) . ($row + 3 + $report_row_card_key), (isset($report_row_card_value['total_w_org_' . $month . '_' . $year])) ? round($report_row_card_value['total_w_org_' . $month . '_' . $year] / 1000000) : 0);

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 1) . ($row + 3 + $report_row_card_key), (isset($report_row_card_value['total_acc_count_' . $month . '_' . $year])) ? $report_row_card_value['total_acc_count_' . $month . '_' . $year] : 0);

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 2) . ($row + 3 + $report_row_card_key), (isset($report_row_card_value['group_2_w_org_' . $month . '_' . $year])) ? round($report_row_card_value['group_2_w_org_' . $month . '_' . $year] / 1000000) : 0);
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 3) . ($row + 3 + $report_row_card_key), (isset($report_row_card_value['group_2_acc_count_' . $month . '_' . $year])) ? $report_row_card_value['group_2_acc_count_' . $month . '_' . $year] : 0);

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 4) . ($row + 3 + $report_row_card_key), '=' . $this->stringFromColumnIndex($col + 2) . ($row + 3 + $report_row_card_key) . '/' . $this->stringFromColumnIndex($col) . ($row + 3 + $report_row_card_key));

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 5) . ($row + 3 + $report_row_card_key), (isset($report_row_card_value['group_3_over_w_org_' . $month . '_' . $year])) ? round($report_row_card_value['group_3_over_w_org_' . $month . '_' . $year] / 1000000) : 0);

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 6) . ($row + 3 + $report_row_card_key), (isset($report_row_card_value['group_3_over_acc_count_' . $month . '_' . $year])) ? $report_row_card_value['group_3_over_acc_count_' . $month . '_' . $year] : 0);

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 7) . ($row + 3 + $report_row_card_key), '=' . $this->stringFromColumnIndex($col + 5) . ($row + 3 + $report_row_card_key) . '/' . $this->stringFromColumnIndex($col) . ($row + 3 + $report_row_card_key));
            }

            // Set border
            $worksheet->getStyle('A' . ($row + 3) . ':' . 'B' . ($row + 3 + $report_row_card_key))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $worksheet->getStyle($this->stringFromColumnIndex($col) . $row . ':' . ($this->stringFromColumnIndex($col + 7)) . ($row + 3 + $report_row_card_key))->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            $worksheet->getStyle($this->stringFromColumnIndex($col) . $row . ':' . ($this->stringFromColumnIndex($col + 7)) . ($row + 3 + $report_row_card_key))->getBorders()->getInside()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $row += (count($prod_group_data_card) + 4);

            // Do du lieu total
            $worksheet->mergeCells("A" . ($row) . ":B" . ($row));
            $worksheet->setCellValue("A" . ($row), "Unit : Mill . VND");
            $worksheet->mergeCells("A" . ($row + 1) . ":B" . ($row + 3));
            $worksheet->setCellValue("A" . ($row + 1), 'JIVF  TOTAL');

            // Border
            $worksheet->getStyle('A' . ($row + 1) . ':' . 'B' . ($row + 3))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $prod_group_total = $this->mongo_db->where(array('year' => (int)$year))->get($this->collection_total);
            // Fill data
            foreach($prod_group_total as $report_row_key_total => $report_row_value_total) {
                // Header 1
                $worksheet->mergeCells($this->stringFromColumnIndex($col) . $row . ":" . $this->stringFromColumnIndex($col + 7) . $row);
                $worksheet->setCellValue($this->stringFromColumnIndex($col) . $row, $month . '/' . $year);

                // Header 2
                $worksheet->mergeCells($this->stringFromColumnIndex($col) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 1) . ($row + 1));
                $worksheet->setCellValue($this->stringFromColumnIndex($col) . ($row + 1), 'Total');

                $worksheet->mergeCells($this->stringFromColumnIndex($col + 2) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 4) . ($row + 1));
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 2) . ($row + 1), 'Group 2');

                $worksheet->mergeCells($this->stringFromColumnIndex($col + 5) . ($row + 1) . ":" . $this->stringFromColumnIndex($col + 7) . ($row + 1));
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 5) . ($row + 1), 'Group 3 and over');

                // Header 3
                $worksheet->setCellValue($this->stringFromColumnIndex($col) . ($row + 2), 'Total W-ORG');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 1) . ($row + 2), 'No.of Account');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 2) . ($row + 2), 'Overdue W-ORG');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 3) . ($row + 2), 'No.of Account');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 4) . ($row + 2), 'Overdue ratio');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 5) . ($row + 2), 'Overdue W-ORG');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 6) . ($row + 2), 'No.of Account');
                $worksheet->setCellValue($this->stringFromColumnIndex($col + 7) . ($row + 2), 'Overdue ratio'); 

                // Header format
                $worksheet->getStyle($this->stringFromColumnIndex($col) . ($row) . ':' . $this->stringFromColumnIndex($col + 7) . ($row + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Fill data
                $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col) . ($row + 3 + $report_row_key_total), (isset($report_row_value_total['total_w_org_' . $month . '_' . $year])) ? round($report_row_value_total['total_w_org_' . $month . '_' . $year] / 1000000) : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 1) . ($row + 3 + $report_row_key_total), (isset($report_row_value_total['total_acc_count_' . $month . '_' . $year])) ? $report_row_value_total['total_acc_count_' . $month . '_' . $year] : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 2) . ($row + 3 + $report_row_key_total), (isset($report_row_value_total['group_2_w_org_' . $month . '_' . $year])) ? round($report_row_value_total['group_2_w_org_' . $month . '_' . $year] / 1000000) : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 3) . ($row + 3 + $report_row_key_total), (isset($report_row_value_total['group_2_acc_count_' . $month . '_' . $year])) ? $report_row_value_total['group_2_acc_count_' . $month . '_' . $year] : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 4) . ($row + 3 + $report_row_key_total), '=' . $this->stringFromColumnIndex($col + 2) . ($row + 3 + $report_row_key_total) . '/' . $this->stringFromColumnIndex($col) . ($row + 3 + $report_row_key_total), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 5) . ($row + 3 + $report_row_key_total), (isset($report_row_value_total['group_3_over_w_org_' . $month . '_' . $year])) ? round($report_row_value_total['group_3_over_w_org_' . $month . '_' . $year] / 1000000) : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                
                $worksheet->setCellValueExplicit($this->stringFromColumnIndex($col + 6) . ($row + 3 + $report_row_key_total), (isset($report_row_value_total['group_3_over_acc_count_' . $month . '_' . $year])) ? $report_row_value_total['group_3_over_acc_count_' . $month . '_' . $year] : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $worksheet->setCellValue($this->stringFromColumnIndex($col + 7) . ($row + 3 + $report_row_key_total), '=' . $this->stringFromColumnIndex($col + 5) . ($row + 3 + $report_row_key_total) . '/' . $this->stringFromColumnIndex($col) . ($row + 3 + $report_row_key_total), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            }

            // Set width for column
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col))->setWidth(15);
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col + 1))->setWidth(15);
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col + 2))->setWidth(15);
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col + 3))->setWidth(15);
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col + 4))->setWidth(15);
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col + 5))->setWidth(15);
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col + 6))->setWidth(15);
            $worksheet->getColumnDimension($this->stringFromColumnIndex($col + 7))->setWidth(15);

            // Set border
            $worksheet->getStyle($this->stringFromColumnIndex($col) . $row . ':' . ($this->stringFromColumnIndex($col + 7)) . ($row + 3 + $report_row_key_total))->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

            $worksheet->getStyle($this->stringFromColumnIndex($col) . $row . ':' . ($this->stringFromColumnIndex($col + 7)) . ($row + 3 + $report_row_key_total))->getBorders()->getInside()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $col += 8;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $file_path = UPLOAD_PATH . "loan/export/" . $filename;
        $writer->save($file_path);
        echo json_encode(array("status" => 1, "data" => $file_path));
    }

    public function stringFromColumnIndex($columnIndex)
    {
		$value = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
		return $value;
    }
}