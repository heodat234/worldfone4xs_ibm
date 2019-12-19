<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

Class Daily_prod_user_group_report extends WFF_Controller {

    private $collection = "Daily_prod_each_user_group";
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
        $data = $this->crud->where($request)->order_by(array('debt_group' => 'asc', 'due_date_code' => 'asc', 'product' => 'desc', 'team' => 'asc'))->get($this->collection);
        $groupProduct = $this->mongo_private->where(array('tags' => array('group', 'debt', 'product')))->getOne(set_sub_collection("Jsondata"));

        $spreadsheet = new Spreadsheet();
    	$spreadsheet->getProperties()
	    ->setCreator("South Telecom")
	    ->setLastModifiedBy("Thanh Hưng")
	    ->setTitle("Daily productivity report -each user and group")
	    ->setSubject("Report")
	    ->setDescription("Office 2007 XLSX, generated using PHP classes.")
	    ->setKeywords("office 2007 openxml php")
        ->setCategory("Report");

        $style = array(
            'alignment'     => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER),
            'allborders'    => array(
                'style'     => \PhpOffice\PhpSpreadsheet\Style\BORDER::BORDER_THIN,
                'color'     => array('rgb' => '000000')
            )
        );

        $dataGroup1 = $dataGroup2 = $dataGroup3 =  array();
        foreach($data as $key => $value) {
            if ($value['debt_group'] == 'A') {
                array_push($dataGroup1, $value);
            }
            if ($value['debt_group'] == 'B' || $value['debt_group'] == 'C') {
                array_push($dataGroup2, $value);
            }
            if ($value['debt_group'] == 'D' || $value['debt_group'] == 'E' || $value['debt_group'] == 'F') {
                array_push($dataGroup3, $value);
            }
        }

        //GROUP A
        //Sheet 1
        $worksheet = $spreadsheet->getSheet(0);
        $worksheet->setTitle('Group A');
        $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);

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

        $worksheet->mergeCells('E1:E3');
        $worksheet->setCellValue('E1', 'GROUP');
        $worksheet->getStyle("E1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("E1")->applyFromArray($style);

        $worksheet->mergeCells('F1:H2');
        $worksheet->setCellValue('F1', 'Target');
        $worksheet->getStyle("F1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("F1")->applyFromArray($style);

        $worksheet->mergeCells('I1:S1');
        $worksheet->setCellValue('I1', $now['mon'].'/'.$now['year']);
        $worksheet->getStyle("I1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("I1")->applyFromArray($style);

        $worksheet->mergeCells('A2:C2');
        $worksheet->setCellValue('A2', 'number.os');
        $worksheet->getStyle("A2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("A2")->applyFromArray($style);

        $worksheet->mergeCells('I2:M2');
        $worksheet->setCellValue('I2', 'Number');
        $worksheet->getStyle("I2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("I2")->applyFromArray($style);

        $worksheet->mergeCells('N2:S2');
        $worksheet->setCellValue('N2', 'Outstanding Balance');
        $worksheet->getStyle("N2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("N2")->applyFromArray($style);

        $worksheet->mergeCells('A3:B3');
        $worksheet->setCellValue('A3', 'Group');
        $worksheet->setCellValue('C3', 'Product');
        $worksheet->getStyle("A3:C3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EDEDED');
        $worksheet->getStyle("A3:C3")->applyFromArray($style);

        $worksheet->setCellValue('F3', 'Percentage');
        $worksheet->setCellValue('G3', 'Account');
        $worksheet->setCellValue('H3', 'Gap (account)');
        $worksheet->getStyle("F3:H3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("F3:H3")->applyFromArray($style);

        $worksheet->setCellValue('I3', 'Total Incidence');
        $worksheet->setCellValue('J3', 'Total Collected');
        $worksheet->getStyle("I3:J3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F4B084');
        $worksheet->getStyle("I3:J3")->applyFromArray($style);

        $worksheet->setCellValue('K3', 'Remaining');
        $worksheet->setCellValue('L3', 'Flow rate');
        $worksheet->setCellValue('M3', 'Collected Ratio');
        $worksheet->getStyle("K3:M3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("K3:M3")->applyFromArray($style);

        $worksheet->setCellValue('N3', 'Total outstanding balance at due date');
        $worksheet->setCellValue('O3', 'Total Collected amount (actual amount)');
        $worksheet->getStyle("N3:O3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F4B084');
        $worksheet->getStyle("N3:O3")->applyFromArray($style);

        $worksheet->setCellValue('P3', 'Payment amount received');
        $worksheet->setCellValue('Q3', 'Remaining (Actual amount)');
        $worksheet->setCellValue('R3', 'Collected Ratio (Actual amount)');
        $worksheet->setCellValue('S3', 'Flow rate');
        $worksheet->getStyle("P3:S3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("P3:S3")->applyFromArray($style);

        foreach(range('C','U') as $columnID) {
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

        $worksheet->getStyle("A1:S3")->applyFromArray($headerStyle);

        $startNumber = 10;
        $startAmt = $startNumber + 4;

        $totalData = array(
            'tar_per'       => 0,
            'tar_amt'       => 0,
            'tar_gap'       => 0,
            'inci'          => 0,
            'col'           => 0,
            'inci_amt'      => 0,
            'col_amt'       => 0,
            'today_rem'     => 0,
            'today_rem_amt' => 0,
            'flow_rate'     => 0,
            'flow_rate_amt' => 0,
            'col_rate'      => 0,
            'col_rate_amt'  => 0
        );

        $dueDateCodeTotal = $totalData;
        $debtGroupTotal = $totalData;
        $debtProdTotal = array();

        foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
            $debtProdTotal[$gProdValue['text']] = $totalData;
        }

        $start_row = 4;

        $start_row_debt_group = 4;
        $debt_group = $dataGroup1[0]['debt_group'];

        $start_row_due_date_code = 4;
        $due_date_code = $dataGroup1[0]['due_date_code'];

        $start_row_prod = 4;
        $prod_row = $dataGroup1[0]['product'];

        $start_due_date = 4;
        $due_date = date('d/m/Y', $dataGroup1[0]['due_date']);

        foreach($dataGroup1 as $key => $value) {
            if($prod_row != $value['product']) {
                $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row - 1));
                $worksheet->setCellValue('D' . $start_row_prod, $due_date);

                $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row));
                $worksheet->setCellValue('C' . $start_row_prod, $prod_row);

                $worksheet->mergeCells('D' . $start_row . ':E' . ($start_row));
                $worksheet->setCellValue('D' . $start_row, 'TOTAL');

                $worksheet->setCellValue('F' . $start_row, (!empty($totalData['tar_per']) ? $totalData['tar_per'] : 0));
                $worksheet->setCellValue('G' . $start_row, (!empty($totalData['tar_amt']) ? $totalData['tar_amt'] : 0));
                $worksheet->setCellValue('H' . $start_row, (!empty($totalData['tar_gap']) ? $totalData['tar_gap'] : 0));
                $worksheet->setCellValue('I' . $start_row, (!empty($totalData['inci']) ? $totalData['inci'] : 0));
                $worksheet->setCellValue('J' . $start_row, (!empty($totalData['col']) ? $totalData['col'] : 0));
                $worksheet->setCellValue('K' . $start_row, (!empty($totalData['rem']) ? $totalData['rem'] : 0));
                $worksheet->setCellValue('L' . $start_row, (!empty($totalData['flow_rate']) ? $totalData['flow_rate'] : 0));
                $worksheet->setCellValue('M' . $start_row, (!empty($totalData['col_ratio']) ? $totalData['col_ratio'] : 0));
                $worksheet->setCellValue('N' . $start_row, (!empty($totalData['inci_amt']) ? $totalData['inci_amt'] : 0));
                $worksheet->setCellValue('O' . $start_row, (!empty($totalData['col_amt']) ? $totalData['col_amt'] : 0));
                $worksheet->setCellValue('P' . $start_row, (!empty($totalData['payment_amt']) ? $totalData['payment_amt'] : 0));
                $worksheet->setCellValue('Q' . $start_row, (!empty($totalData['rem_actual']) ? $totalData['rem_actual'] : 0));
                $worksheet->setCellValue('R' . $start_row, (!empty($totalData['col_ratio_actual']) ? $totalData['col_ratio_actual'] : 0));
                $worksheet->setCellValue('S' . $start_row, (!empty($totalData['flow_rate_actual']) ? $totalData['flow_rate_actual'] : 0));

                $worksheet->getStyle('D' . $start_row . ':' . $this->stringFromColumnIndex($startAmt  + 5) . $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                $worksheet->getStyle('D' . $start_row . ':' . $this->stringFromColumnIndex($startAmt  + 5) . $start_row)->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($totalData as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }

                $start_row += 1;

                $prod_row = $value['product'];
                $due_date = date('d/m/Y',  $value['due_date']);
                $start_row_prod = $start_row;
                $countTeamInGroup = 0;
            }

            if($due_date_code != $value['due_date_code']) {
                $worksheet->mergeCells('B' . $start_row_due_date_code . ':B' . ($start_row - 1));
                $worksheet->setCellValue('B' . $start_row_due_date_code, $due_date_code);

                $worksheet->mergeCells('B' . $start_row . ':E' . ($start_row));
                $worksheet->setCellValue('B' . $start_row, 'TOTAL');

                $worksheet->setCellValue('F' . $start_row, (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                $worksheet->setCellValue('G' . $start_row, (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                $worksheet->setCellValue('H' . $start_row, (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                $worksheet->setCellValue('I' . $start_row, (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));
                $worksheet->setCellValue('J' . $start_row, (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                $worksheet->setCellValue('K' . $start_row, (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                $worksheet->setCellValue('L' . $start_row, (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                $worksheet->setCellValue('M' . $start_row, (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                $worksheet->setCellValue('N' . $start_row, (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                $worksheet->setCellValue('O' . $start_row, (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                $worksheet->setCellValue('P' . $start_row, (!empty($dueDateCodeTotal['payment_amt']) ? $dueDateCodeTotal['payment_amt'] : 0));
                $worksheet->setCellValue('Q' . $start_row, (!empty($dueDateCodeTotal['rem_actual']) ? $dueDateCodeTotal['rem_actual'] : 0));
                $worksheet->setCellValue('R' . $start_row, (!empty($dueDateCodeTotal['col_ratio_actual']) ? $dueDateCodeTotal['col_ratio_actual'] : 0));
                $worksheet->setCellValue('S' . $start_row, (!empty($dueDateCodeTotal['flow_rate_actual']) ? $dueDateCodeTotal['flow_rate_actual'] : 0));

                $worksheet->getStyle('B' . $start_row . ':' . $this->stringFromColumnIndex($startAmt  + 5) . $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084');
                $worksheet->getStyle('B' . $start_row . ':' . $this->stringFromColumnIndex($startAmt  + 5) . $start_row)->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($dueDateCodeTotal as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }

                $start_row += 1;
                $start_row_prod += 1;

                $due_date_code = $value['due_date_code'];
                $start_row_due_date_code = $start_row;
            }

            if($debt_group != $value['debt_group']) {
                if(!empty($groupProduct['data'])) {
                    $rowGroup = $start_row;
                    foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                        $worksheet->mergeCells('B' . ($start_row + $gProdKey) . ':E' . ($start_row + $gProdKey));
                        $worksheet->setCellValue('B' . ($start_row + $gProdKey), $gProdValue['text']);
                        $debtProdTotalGroup = $debtProdTotal[$gProdValue['text']];

                        $worksheet->setCellValue('F' . $rowGroup, (!empty($debtProdTotalGroup['tar_per']) ? $debtProdTotalGroup['tar_per'] : 0));
                        $worksheet->setCellValue('G' . $rowGroup, (!empty($debtProdTotalGroup['tar_amt']) ? $debtProdTotalGroup['tar_amt'] : 0));
                        $worksheet->setCellValue('H' . $rowGroup, (!empty($debtProdTotalGroup['tar_gap']) ? $debtProdTotalGroup['tar_gap'] : 0));
                        $worksheet->setCellValue('I' . $rowGroup, (!empty($debtProdTotalGroup['inci']) ? $debtProdTotalGroup['inci'] : 0));
                        $worksheet->setCellValue('J' . $rowGroup, (!empty($debtProdTotalGroup['col']) ? $debtProdTotalGroup['col'] : 0));
                        $worksheet->setCellValue('K' . $rowGroup, (!empty($debtProdTotalGroup['rem']) ? $debtProdTotalGroup['rem'] : 0));
                        $worksheet->setCellValue('L' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                        $worksheet->setCellValue('M' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio']) ? $debtProdTotalGroup['col_ratio'] : 0));
                        $worksheet->setCellValue('N' . $rowGroup, (!empty($debtProdTotalGroup['inci_amt']) ? $debtProdTotalGroup['inci_amt'] : 0));
                        $worksheet->setCellValue('O' . $rowGroup, (!empty($debtProdTotalGroup['col_amt']) ? $debtProdTotalGroup['col_amt'] : 0));
                        $worksheet->setCellValue('P' . $rowGroup, (!empty($debtProdTotalGroup['payment_amt']) ? $debtProdTotalGroup['payment_amt'] : 0));
                        $worksheet->setCellValue('Q' . $rowGroup, (!empty($debtProdTotalGroup['rem_actual']) ? $debtProdTotalGroup['rem_actual'] : 0));
                        $worksheet->setCellValue('R' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio_actual']) ? $debtProdTotalGroup['col_ratio_actual'] : 0));
                        $worksheet->setCellValue('S' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_actual']) ? $debtProdTotalGroup['flow_rate_actual'] : 0));

                        $worksheet->getStyle('B' . $rowGroup . ':' . $this->stringFromColumnIndex($startAmt  + 5) . $rowGroup)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                        $worksheet->getStyle('B' . $rowGroup . ':' . $this->stringFromColumnIndex($startAmt  + 5) . $rowGroup)->applyFromArray([
                            'font'      => [
                                'bold'  => true
                            ]
                        ]);

                        foreach($debtProdTotal[$gProdValue['text']] as $totalKey => &$totalValue) {
                            $totalValue = 0;
                        }
                        $rowGroup += 1;
                    }

                    $worksheet->mergeCells('B' . ($start_row + count($groupProduct['data'])) . ':E' . ($start_row + count($groupProduct['data'])));
                    $worksheet->setCellValue('B' . ($start_row + count($groupProduct['data'])), $debt_group.'-Total');
                }

                $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + (count($groupProduct['data']))));
                $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));


                $worksheet->setCellValue('J' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col']) ? $groupProduct['col'] : 0));
                $worksheet->setCellValue('K' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['rem']) ? $groupProduct['rem'] : 0));
                $worksheet->setCellValue('L' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['flow_rate']) ? $groupProduct['flow_rate'] : 0));
                $worksheet->setCellValue('M' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_ratio']) ? $groupProduct['col_ratio'] : 0));
                $worksheet->setCellValue('N' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['inci_amt']) ? $groupProduct['inci_amt'] : 0));
                $worksheet->setCellValue('O' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_amt']) ? $groupProduct['col_amt'] : 0));
                $worksheet->setCellValue('P' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['payment_amt']) ? $groupProduct['payment_amt'] : 0));
                $worksheet->setCellValue('Q' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['rem_actual']) ? $groupProduct['rem_actual'] : 0));
                $worksheet->setCellValue('R' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_ratio_actual']) ? $groupProduct['col_ratio_actual'] : 0));
                $worksheet->setCellValue('S' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['flow_rate_actual']) ? $groupProduct['flow_rate_actual'] : 0));

                $worksheet->getStyle('B' . ($start_row + count($groupProduct['data'])) . ':' . $this->stringFromColumnIndex($startAmt  + 5) .($start_row + count($groupProduct['data'])))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                $worksheet->getStyle('B' . ($start_row + count($groupProduct['data'])) . ':' . $this->stringFromColumnIndex($startAmt  + 5) . ($start_row + count($groupProduct['data'])))->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($debtGroupTotal as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }


                $start_row += (count($groupProduct['data']) + 1);
                $start_row_prod += (count($groupProduct['data']) + 1);
                $start_row_due_date_code += (count($groupProduct['data']) + 1);

                $debt_group = $value['debt_group'];
                $start_row_debt_group = $start_row;
            }


            $worksheet->setCellValue('E' . $start_row, $value['team']);
            $worksheet->setCellValue('F' . $start_row, (!empty($value['tar_per']) ? $value['tar_per'] : 0));
            $worksheet->setCellValue('G' . $start_row, (!empty($value['tar_amt']) ? $value['tar_amt'] : 0));
            $worksheet->setCellValue('H' . $start_row, (!empty($value['tar_gap']) ? $value['tar_gap'] : 0));
            $worksheet->setCellValue('I' . $start_row, (!empty($value['inci']) ? $value['inci'] : 0));
            $worksheet->setCellValue('J' . $start_row, (!empty($value['col']) ? $value['col'] : 0));
            $worksheet->setCellValue('K' . $start_row, (!empty($value['rem']) ? $value['rem'] : 0));
            $worksheet->setCellValue('L' . $start_row, (!empty($value['flow_rate']) ? $value['flow_rate'] : 0));
            $worksheet->setCellValue('M' . $start_row, (!empty($value['col_ratio']) ? $value['col_ratio'] : 0));
            $worksheet->setCellValue('N' . $start_row, (!empty($value['inci_amt']) ? $value['inci_amt'] : 0));
            $worksheet->setCellValue('O' . $start_row, (!empty($value['col_amt']) ? $value['col_amt'] : 0));
            $worksheet->setCellValue('P' . $start_row, (!empty($value['payment_amt']) ? $value['payment_amt'] : 0));
            $worksheet->setCellValue('Q' . $start_row, (!empty($value['rem_actual']) ? $value['rem_actual'] : 0));
            $worksheet->setCellValue('R' . $start_row, (!empty($value['col_ratio_actual']) ? $value['col_ratio_actual'] : 0));
            $worksheet->setCellValue('S' . $start_row, (!empty($value['flow_rate_actual']) ? $value['flow_rate_actual'] : 0));


            foreach($totalData as $keyTotal => &$totalValue) {
                $tempTotalData = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalData;
            }

            foreach($dueDateCodeTotal as $keyTotal => &$totalValue) {
                $tempTotalDateCode = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalDateCode;
            }

            foreach($debtGroupTotal as $keyTotal => &$totalValue) {
                $tempTotalDebtGroup = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalDebtGroup;
            }

            foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                if ($gProdValue['text'] == $value['product']) {
                    foreach($debtProdTotal[$gProdValue['text']] as $keyTotal => &$totalValue) {
                        $tempTotalDebtProd = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                        $totalValue +=  $tempTotalDebtProd;
                    }
                }

            }

            if ($key == (count($dataGroup1) - 1)) {
                if ($value['debt_group'] != 'F') {
                    if(!empty($groupProduct['data'])) {
                        $rowGroup = $start_row + 3;
                        foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                            $worksheet->mergeCells('B' . ($start_row + $gProdKey + 3) . ':E' . ($start_row + $gProdKey + 3));
                            $worksheet->setCellValue('B' . ($start_row + $gProdKey + 3), $gProdValue['text']);
                            $debtProdTotalGroup = $debtProdTotal[$gProdValue['text']];

                            $worksheet->setCellValue('F' . $rowGroup, (!empty($debtProdTotalGroup['tar_per']) ? $debtProdTotalGroup['tar_per'] : 0));
                            $worksheet->setCellValue('G' . $rowGroup, (!empty($debtProdTotalGroup['tar_amt']) ? $debtProdTotalGroup['tar_amt'] : 0));
                            $worksheet->setCellValue('H' . $rowGroup, (!empty($debtProdTotalGroup['tar_gap']) ? $debtProdTotalGroup['tar_gap'] : 0));
                            $worksheet->setCellValue('I' . $rowGroup, (!empty($debtProdTotalGroup['inci']) ? $debtProdTotalGroup['inci'] : 0));

                            $worksheet->setCellValue('J' . $rowGroup, (!empty($debtProdTotalGroup['col']) ? $debtProdTotalGroup['col'] : 0));
                            $worksheet->setCellValue('K' . $rowGroup, (!empty($debtProdTotalGroup['rem']) ? $debtProdTotalGroup['rem'] : 0));
                            $worksheet->setCellValue('L' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                            $worksheet->setCellValue('M' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio']) ? $debtProdTotalGroup['col_ratio'] : 0));
                            $worksheet->setCellValue('N' . $rowGroup, (!empty($debtProdTotalGroup['inci_amt']) ? $debtProdTotalGroup['inci_amt'] : 0));
                            $worksheet->setCellValue('O' . $rowGroup, (!empty($debtProdTotalGroup['col_amt']) ? $debtProdTotalGroup['col_amt'] : 0));
                            $worksheet->setCellValue('P' . $rowGroup, (!empty($debtProdTotalGroup['payment_amt']) ? $debtProdTotalGroup['payment_amt'] : 0));
                            $worksheet->setCellValue('Q' . $rowGroup, (!empty($debtProdTotalGroup['rem_actual']) ? $debtProdTotalGroup['rem_actual'] : 0));
                            $worksheet->setCellValue('R' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio_actual']) ? $debtProdTotalGroup['col_ratio_actual'] : 0));
                            $worksheet->setCellValue('S' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_actual']) ? $debtProdTotalGroup['flow_rate_actual'] : 0));

                             $worksheet->getStyle('B' . $rowGroup . ':S' . $rowGroup)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                            $worksheet->getStyle('B' . $rowGroup . ':S' . $rowGroup)->applyFromArray([
                                'font'      => [
                                    'bold'  => true
                                ]
                            ]);
                            foreach($debtProdTotal[$gProdValue['text']] as $totalKey => &$totalValue) {
                                $totalValue = 0;
                            }
                            $rowGroup += 1;
                        }

                        $worksheet->mergeCells('B' . ($start_row + count($groupProduct['data']) + 3) . ':E' . ($start_row + count($groupProduct['data']) + 3));
                        $worksheet->setCellValue('B' . ($start_row + count($groupProduct['data']) + 3), 'D-Total');
                    }

                    $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + 5));
                    $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                    $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                    $worksheet->mergeCells('B' . $start_row_due_date_code . ':B' . ($start_row + 1));
                    $worksheet->setCellValue('B' . $start_row_due_date_code, $due_date_code);

                    $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row + 1));
                    $worksheet->setCellValue('C' . $start_row_prod, $prod_row);

                    $worksheet->mergeCells('B' . ($start_row + 2) . ':E' . ($start_row + 2));
                    $worksheet->setCellValue('B' . ($start_row + 2), 'TOTAL');

                    $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row));
                    $worksheet->setCellValue('D' . $start_row_prod, $due_date);

                    $worksheet->mergeCells('D' . ($start_row + 1) . ':E' . ($start_row + 1));
                    $worksheet->setCellValue('D' . ($start_row + 1), 'TOTAL');

                    $worksheet->setCellValue('F' . ($start_row + 1), (!empty($total['tar_per']) ? $total['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 1), (!empty($total['tar_amt']) ? $total['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 1), (!empty($total['tar_gap']) ? $total['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 1), (!empty($total['inci']) ? $total['inci'] : 0));

                    $worksheet->setCellValue('J' . ($start_row + 1), (!empty($total['col']) ? $total['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 1), (!empty($total['rem']) ? $total['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 1), (!empty($total['flow_rate']) ? $total['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 1), (!empty($total['col_ratio']) ? $total['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 1), (!empty($total['inci_amt']) ? $total['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 1), (!empty($total['col_amt']) ? $total['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 1), (!empty($total['payment_amt']) ? $total['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 1), (!empty($total['rem_actual']) ? $total['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 1), (!empty($total['col_ratio_actual']) ? $total['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' . ($start_row + 1), (!empty($total['flow_rate_actual']) ? $total['flow_rate_actual'] : 0));

                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 2), (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + 2), (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 2), (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 2), (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 2), (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 2), (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 2), (!empty($dueDateCodeTotal['payment_amt']) ? $dueDateCodeTotal['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 2), (!empty($dueDateCodeTotal['rem_actual']) ? $dueDateCodeTotal['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 2), (!empty($dueDateCodeTotal['col_ratio_actual']) ? $dueDateCodeTotal['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate_actual']) ? $dueDateCodeTotal['flow_rate_actual'] : 0));

                    $worksheet->getStyle('B' . ($start_row + 2) . ':S' . ($start_row + 2))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084');
                    $worksheet->getStyle('B' . ($start_row + 2) . ':S' . ($start_row + 2))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);
                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col']) ? $debtGroupTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['rem']) ? $debtGroupTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate']) ? $debtGroupTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_ratio']) ? $debtGroupTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci_amt']) ? $debtGroupTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_amt']) ? $debtGroupTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['payment_amt']) ? $debtGroupTotal['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['rem_actual']) ? $debtGroupTotal['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_ratio_actual']) ? $debtGroupTotal['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate_actual']) ? $debtGroupTotal['flow_rate_actual'] : 0));

                    $worksheet->getStyle('B' . ($start_row + count($groupProduct['data']) + 3) . ':S' .($start_row + count($groupProduct['data']) + 3))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                    $worksheet->getStyle('B' . ($start_row + count($groupProduct['data']) + 3) . ':S' . ($start_row + count($groupProduct['data']) + 3))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);

                    foreach($debtGroupTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
                else
                {
                    $worksheet->mergeCells('B' . ($start_row + 1) . ':E' . ($start_row + 1 ));
                    $worksheet->setCellValue('B' . ($start_row + 1 ), 'F-Total');
                    $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + 1 ));
                    $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                    $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');
                    $worksheet->mergeCells('B' . $start_row_prod . ':D' . $start_row );
                    $worksheet->setCellValue('B' . $start_row_prod, $prod_row);

                    $worksheet->setCellValue('F' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 1 ), (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + 1), (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 1), (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 1), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 1), (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 1), (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 1), (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 1), (!empty($dueDateCodeTotal['payment_amt']) ? $dueDateCodeTotal['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 1), (!empty($dueDateCodeTotal['rem_actual']) ? $dueDateCodeTotal['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 1), (!empty($dueDateCodeTotal['col_ratio_actual']) ? $dueDateCodeTotal['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' . ($start_row + 1), (!empty($dueDateCodeTotal['flow_rate_actual']) ? $dueDateCodeTotal['flow_rate_actual'] : 0));

                    $worksheet->getStyle('B' . ($start_row + 1) . ':' . $this->stringFromColumnIndex($startAmt  + 5) .($start_row + 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                    $worksheet->getStyle('B' . ($start_row + 1) . ':' . $this->stringFromColumnIndex($startAmt  + 5) . ($start_row + 1))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);
                    foreach($dueDateCodeTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
            }
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:S".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);






        //GROUP B & C
        //sheet 2
        $worksheet = $spreadsheet->createSheet(1);
        $worksheet->setTitle('Group B & C');

        $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);

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

        $worksheet->mergeCells('E1:E3');
        $worksheet->setCellValue('E1', 'GROUP');
        $worksheet->getStyle("E1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("E1")->applyFromArray($style);

        $worksheet->mergeCells('F1:H2');
        $worksheet->setCellValue('F1', 'Target');
        $worksheet->getStyle("F1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("F1")->applyFromArray($style);

        $worksheet->mergeCells('I1:U1');
        $worksheet->setCellValue('I1', $now['mon'].'/'.$now['year']);
        $worksheet->getStyle("I1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("I1")->applyFromArray($style);

        $worksheet->mergeCells('A2:C2');
        $worksheet->setCellValue('A2', 'number.os');
        $worksheet->getStyle("A2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("A2")->applyFromArray($style);

        $worksheet->mergeCells('I2:M2');
        $worksheet->setCellValue('I2', 'Number');
        $worksheet->getStyle("I2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("I2")->applyFromArray($style);

        $worksheet->mergeCells('N2:U2');
        $worksheet->setCellValue('N2', 'Outstanding Balance');
        $worksheet->getStyle("N2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("N2")->applyFromArray($style);

        $worksheet->mergeCells('A3:B3');
        $worksheet->setCellValue('A3', 'Group');
        $worksheet->setCellValue('C3', 'Product');
        $worksheet->getStyle("A3:C3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EDEDED');
        $worksheet->getStyle("A3:C3")->applyFromArray($style);

        $worksheet->setCellValue('F3', 'Percentage');
        $worksheet->setCellValue('G3', 'Account');
        $worksheet->setCellValue('H3', 'Gap (account)');
        $worksheet->getStyle("F3:H3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("F3:H3")->applyFromArray($style);

        $worksheet->setCellValue('I3', 'Total Incidence');
        $worksheet->setCellValue('J3', 'Total Collected');
        $worksheet->getStyle("I3:J3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F4B084');
        $worksheet->getStyle("I3:J3")->applyFromArray($style);

        $worksheet->setCellValue('K3', 'Remaining');
        $worksheet->setCellValue('L3', 'Flow rate');
        $worksheet->setCellValue('M3', 'Collected Ratio');
        $worksheet->getStyle("K3:M3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("K3:M3")->applyFromArray($style);

        $worksheet->setCellValue('N3', 'Total outstanding balance at due date');
        $worksheet->setCellValue('O3', 'Total Collected amount (actual amount)');
        $worksheet->getStyle("N3:O3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F4B084');
        $worksheet->getStyle("N3:O3")->applyFromArray($style);

        $worksheet->setCellValue('P3', 'Payment amount received');
        $worksheet->setCellValue('Q3', 'Remaining (Actual amount)');
        $worksheet->setCellValue('R3', 'Collected Ratio (Actual amount)');
        $worksheet->setCellValue('S3', 'Remaining (OS at current - OS at due date)');
        $worksheet->getStyle("P3:S3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("P3:S3")->applyFromArray($style);

        $worksheet->setCellValue('T3', 'Flow rate (OS at current - OS at due date)');
        $worksheet->setCellValue('U3', 'Collected Ratio (OS at current - OS at due date)');

        foreach(range('C','U') as $columnID) {
            $worksheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $headerStyle = array(
            'font'          => array(
                'bold'      => true,
            ),
            'alignment'     => array(
                'wrapText'  => true,
            )
        );

        $worksheet->getStyle("A1:U3")->applyFromArray($headerStyle);

        $startNumber = 10;
        $startAmt = $startNumber + 4;

        $totalData = array(
            'tar_per'       => 0,
            'tar_amt'       => 0,
            'tar_gap'       => 0,
            'inci'          => 0,
            'col'           => 0,
            'inci_amt'      => 0,
            'col_amt'       => 0,
            'today_rem'     => 0,
            'today_rem_amt' => 0,
            'flow_rate'     => 0,
            'flow_rate_amt' => 0,
            'col_rate'      => 0,
            'col_rate_amt'  => 0
        );

        $dueDateCodeTotal = $totalData;
        $debtGroupTotal = $totalData;
        $debtProdTotal = array();

        foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
            $debtProdTotal[$gProdValue['text']] = $totalData;
        }

        $start_row = 4;

        $start_row_debt_group = 4;
        $debt_group = $dataGroup2[0]['debt_group'];

        $start_row_due_date_code = 4;
        $due_date_code = $dataGroup2[0]['due_date_code'];

        $start_row_prod = 4;
        $prod_row = $dataGroup2[0]['product'];

        $start_due_date = 4;
        $due_date = date('d/m/Y', $dataGroup2[0]['due_date']);

        foreach($dataGroup2 as $key => $value) {
            if($prod_row != $value['product']) {
                $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row - 1));
                $worksheet->setCellValue('D' . $start_row_prod, $due_date);

                $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row));
                $worksheet->setCellValue('C' . $start_row_prod, $prod_row);

                $worksheet->mergeCells('D' . $start_row . ':E' . ($start_row));
                $worksheet->setCellValue('D' . $start_row, 'TOTAL');

                $worksheet->setCellValue('F' . $start_row, (!empty($totalData['tar_per']) ? $totalData['tar_per'] : 0));
                $worksheet->setCellValue('G' . $start_row, (!empty($totalData['tar_amt']) ? $totalData['tar_amt'] : 0));
                $worksheet->setCellValue('H' . $start_row, (!empty($totalData['tar_gap']) ? $totalData['tar_gap'] : 0));
                $worksheet->setCellValue('I' . $start_row, (!empty($totalData['inci']) ? $totalData['inci'] : 0));
                $worksheet->setCellValue('J' . $start_row, (!empty($totalData['col']) ? $totalData['col'] : 0));
                $worksheet->setCellValue('K' . $start_row, (!empty($totalData['rem']) ? $totalData['rem'] : 0));
                $worksheet->setCellValue('L' . $start_row, (!empty($totalData['flow_rate']) ? $totalData['flow_rate'] : 0));
                $worksheet->setCellValue('M' . $start_row, (!empty($totalData['col_ratio']) ? $totalData['col_ratio'] : 0));
                $worksheet->setCellValue('N' . $start_row, (!empty($totalData['inci_amt']) ? $totalData['inci_amt'] : 0));
                $worksheet->setCellValue('O' . $start_row, (!empty($totalData['col_amt']) ? $totalData['col_amt'] : 0));
                $worksheet->setCellValue('P' . $start_row, (!empty($totalData['payment_amt']) ? $totalData['payment_amt'] : 0));
                $worksheet->setCellValue('Q' . $start_row, (!empty($totalData['rem_actual']) ? $totalData['rem_actual'] : 0));
                $worksheet->setCellValue('R' . $start_row, (!empty($totalData['col_ratio_actual']) ? $totalData['col_ratio_actual'] : 0));
                $worksheet->setCellValue('S' . $start_row, (!empty($totalData['rem_os']) ? $totalData['rem_os'] : 0));
                $worksheet->setCellValue('T' . $start_row, (!empty($totalData['flow_rate_os']) ? $totalData['flow_rate_os'] : 0));
                $worksheet->setCellValue('U' . $start_row, (!empty($totalData['col_ratio_os']) ? $totalData['col_ratio_os'] : 0));

                $worksheet->getStyle('D' . $start_row . ':U' . $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                $worksheet->getStyle('D' . $start_row . ':U'. $start_row)->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($totalData as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }

                $start_row += 1;

                $prod_row = $value['product'];
                $due_date = date('d/m/Y',  $value['due_date']);
                $start_row_prod = $start_row;
                $countTeamInGroup = 0;
            }

            if($due_date_code != $value['due_date_code']) {
                $worksheet->mergeCells('B' . $start_row_due_date_code . ':B' . ($start_row - 1));
                $worksheet->setCellValue('B' . $start_row_due_date_code, $due_date_code);

                $worksheet->mergeCells('B' . $start_row . ':E' . ($start_row));
                $worksheet->setCellValue('B' . $start_row, 'TOTAL');

                $worksheet->setCellValue('F' . $start_row, (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                $worksheet->setCellValue('G' . $start_row, (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                $worksheet->setCellValue('H' . $start_row, (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                $worksheet->setCellValue('I' . $start_row, (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));
                $worksheet->setCellValue('J' . $start_row, (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                $worksheet->setCellValue('K' . $start_row, (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                $worksheet->setCellValue('L' . $start_row, (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                $worksheet->setCellValue('M' . $start_row, (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                $worksheet->setCellValue('N' . $start_row, (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                $worksheet->setCellValue('O' . $start_row, (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                $worksheet->setCellValue('P' . $start_row, (!empty($dueDateCodeTotal['payment_amt']) ? $dueDateCodeTotal['payment_amt'] : 0));
                $worksheet->setCellValue('Q' . $start_row, (!empty($dueDateCodeTotal['rem_actual']) ? $dueDateCodeTotal['rem_actual'] : 0));
                $worksheet->setCellValue('R' . $start_row, (!empty($dueDateCodeTotal['col_ratio_actual']) ? $dueDateCodeTotal['col_ratio_actual'] : 0));
                $worksheet->setCellValue('S' . $start_row, (!empty($dueDateCodeTotal['rem_os']) ? $dueDateCodeTotal['rem_os'] : 0));
                $worksheet->setCellValue('T' . $start_row, (!empty($dueDateCodeTotal['flow_rate_os']) ? $dueDateCodeTotal['flow_rate_os'] : 0));
                $worksheet->setCellValue('U' . $start_row, (!empty($dueDateCodeTotal['col_ratio_os']) ? $dueDateCodeTotal['col_ratio_os'] : 0));

                $worksheet->getStyle('B' . $start_row . ':U' . $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084');
                $worksheet->getStyle('B' . $start_row . ':U' . $start_row)->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($dueDateCodeTotal as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }

                $start_row += 1;
                $start_row_prod += 1;

                $due_date_code = $value['due_date_code'];
                $start_row_due_date_code = $start_row;
            }

            if($debt_group != $value['debt_group']) {
                if(!empty($groupProduct['data'])) {
                    $rowGroup = $start_row;
                    foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                        $worksheet->mergeCells('B' . ($start_row + $gProdKey) . ':E' . ($start_row + $gProdKey));
                        $worksheet->setCellValue('B' . ($start_row + $gProdKey), $gProdValue['text']);
                        $debtProdTotalGroup = $debtProdTotal[$gProdValue['text']];

                        $worksheet->setCellValue('F' . $rowGroup, (!empty($debtProdTotalGroup['tar_per']) ? $debtProdTotalGroup['tar_per'] : 0));
                        $worksheet->setCellValue('G' . $rowGroup, (!empty($debtProdTotalGroup['tar_amt']) ? $debtProdTotalGroup['tar_amt'] : 0));
                        $worksheet->setCellValue('H' . $rowGroup, (!empty($debtProdTotalGroup['tar_gap']) ? $debtProdTotalGroup['tar_gap'] : 0));
                        $worksheet->setCellValue('I' . $rowGroup, (!empty($debtProdTotalGroup['inci']) ? $debtProdTotalGroup['inci'] : 0));
                        $worksheet->setCellValue('J' . $rowGroup, (!empty($debtProdTotalGroup['col']) ? $debtProdTotalGroup['col'] : 0));
                        $worksheet->setCellValue('K' . $rowGroup, (!empty($debtProdTotalGroup['rem']) ? $debtProdTotalGroup['rem'] : 0));
                        $worksheet->setCellValue('L' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                        $worksheet->setCellValue('M' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio']) ? $debtProdTotalGroup['col_ratio'] : 0));
                        $worksheet->setCellValue('N' . $rowGroup, (!empty($debtProdTotalGroup['inci_amt']) ? $debtProdTotalGroup['inci_amt'] : 0));
                        $worksheet->setCellValue('O' . $rowGroup, (!empty($debtProdTotalGroup['col_amt']) ? $debtProdTotalGroup['col_amt'] : 0));
                        $worksheet->setCellValue('P' . $rowGroup, (!empty($debtProdTotalGroup['payment_amt']) ? $debtProdTotalGroup['payment_amt'] : 0));
                        $worksheet->setCellValue('Q' . $rowGroup, (!empty($debtProdTotalGroup['rem_actual']) ? $debtProdTotalGroup['rem_actual'] : 0));
                        $worksheet->setCellValue('R' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio_actual']) ? $debtProdTotalGroup['col_ratio_actual'] : 0));
                        $worksheet->setCellValue('S' . $rowGroup, (!empty($debtProdTotalGroup['rem_os']) ? $debtProdTotalGroup['rem_os'] : 0));
                        $worksheet->setCellValue('T' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_os']) ? $debtProdTotalGroup['flow_rate_os'] : 0));
                        $worksheet->setCellValue('U' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio_os']) ? $debtProdTotalGroup['col_ratio_os'] : 0));

                        $worksheet->getStyle('B' . $rowGroup . ':U' . $rowGroup)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                        $worksheet->getStyle('B' . $rowGroup . ':U'. $rowGroup)->applyFromArray([
                            'font'      => [
                                'bold'  => true
                            ]
                        ]);

                        foreach($debtProdTotal[$gProdValue['text']] as $totalKey => &$totalValue) {
                            $totalValue = 0;
                        }
                        $rowGroup += 1;
                    }

                    $worksheet->mergeCells('B' . ($start_row + count($groupProduct['data'])) . ':E' . ($start_row + count($groupProduct['data'])));
                    $worksheet->setCellValue('B' . ($start_row + count($groupProduct['data'])), $debt_group.'-Total');
                }

                $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + (count($groupProduct['data']))));
                $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));


                $worksheet->setCellValue('J' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col']) ? $groupProduct['col'] : 0));
                $worksheet->setCellValue('K' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['rem']) ? $groupProduct['rem'] : 0));
                $worksheet->setCellValue('L' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['flow_rate']) ? $groupProduct['flow_rate'] : 0));
                $worksheet->setCellValue('M' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_ratio']) ? $groupProduct['col_ratio'] : 0));
                $worksheet->setCellValue('N' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['inci_amt']) ? $groupProduct['inci_amt'] : 0));
                $worksheet->setCellValue('O' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_amt']) ? $groupProduct['col_amt'] : 0));
                $worksheet->setCellValue('P' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['payment_amt']) ? $groupProduct['payment_amt'] : 0));
                $worksheet->setCellValue('Q' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['rem_actual']) ? $groupProduct['rem_actual'] : 0));
                $worksheet->setCellValue('R' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_ratio_actual']) ? $groupProduct['col_ratio_actual'] : 0));
                $worksheet->setCellValue('S' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['rem_os']) ? $groupProduct['rem_os'] : 0));
                $worksheet->setCellValue('T' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['flow_rate_os']) ? $groupProduct['flow_rate_os'] : 0));
                $worksheet->setCellValue('U' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_ratio_os']) ? $groupProduct['col_ratio_os'] : 0));

                $worksheet->getStyle('B' . ($start_row + count($groupProduct['data'])) . ':U'  .($start_row + count($groupProduct['data'])))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                $worksheet->getStyle('B' . ($start_row + count($groupProduct['data'])) . ':U' . ($start_row + count($groupProduct['data'])))->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($debtGroupTotal as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }


                $start_row += (count($groupProduct['data']) + 1);
                $start_row_prod += (count($groupProduct['data']) + 1);
                $start_row_due_date_code += (count($groupProduct['data']) + 1);

                $debt_group = $value['debt_group'];
                $start_row_debt_group = $start_row;
            }


            $worksheet->setCellValue('E' . $start_row, $value['team']);
            $worksheet->setCellValue('F' . $start_row, (!empty($value['tar_per']) ? $value['tar_per'] : 0));
            $worksheet->setCellValue('G' . $start_row, (!empty($value['tar_amt']) ? $value['tar_amt'] : 0));
            $worksheet->setCellValue('H' . $start_row, (!empty($value['tar_gap']) ? $value['tar_gap'] : 0));
            $worksheet->setCellValue('I' . $start_row, (!empty($value['inci']) ? $value['inci'] : 0));
            $worksheet->setCellValue('J' . $start_row, (!empty($value['col']) ? $value['col'] : 0));
            $worksheet->setCellValue('K' . $start_row, (!empty($value['rem']) ? $value['rem'] : 0));
            $worksheet->setCellValue('L' . $start_row, (!empty($value['flow_rate']) ? $value['flow_rate'] : 0));
            $worksheet->setCellValue('M' . $start_row, (!empty($value['col_ratio']) ? $value['col_ratio'] : 0));
            $worksheet->setCellValue('N' . $start_row, (!empty($value['inci_amt']) ? $value['inci_amt'] : 0));
            $worksheet->setCellValue('O' . $start_row, (!empty($value['col_amt']) ? $value['col_amt'] : 0));
            $worksheet->setCellValue('P' . $start_row, (!empty($value['payment_amt']) ? $value['payment_amt'] : 0));
            $worksheet->setCellValue('Q' . $start_row, (!empty($value['rem_actual']) ? $value['rem_actual'] : 0));
            $worksheet->setCellValue('R' . $start_row, (!empty($value['col_ratio_actual']) ? $value['col_ratio_actual'] : 0));
            $worksheet->setCellValue('S' . $start_row, (!empty($value['rem_os']) ? $value['rem_os'] : 0));
            $worksheet->setCellValue('T' . $start_row, (!empty($value['flow_rate_os']) ? $value['flow_rate_os'] : 0));
            $worksheet->setCellValue('U' . $start_row, (!empty($value['col_ratio_os']) ? $value['col_ratio_os'] : 0));


            foreach($totalData as $keyTotal => &$totalValue) {
                $tempTotalData = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalData;
            }

            foreach($dueDateCodeTotal as $keyTotal => &$totalValue) {
                $tempTotalDateCode = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalDateCode;
            }

            foreach($debtGroupTotal as $keyTotal => &$totalValue) {
                $tempTotalDebtGroup = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalDebtGroup;
            }

            foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                if ($gProdValue['text'] == $value['product']) {
                    foreach($debtProdTotal[$gProdValue['text']] as $keyTotal => &$totalValue) {
                        $tempTotalDebtProd = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                        $totalValue +=  $tempTotalDebtProd;
                    }
                }

            }

            if ($key == (count($dataGroup2) - 1)) {
                if ($value['debt_group'] != 'F') {
                    if(!empty($groupProduct['data'])) {
                        $rowGroup = $start_row + 3;
                        foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                            $worksheet->mergeCells('B' . ($start_row + $gProdKey + 3) . ':E' . ($start_row + $gProdKey + 3));
                            $worksheet->setCellValue('B' . ($start_row + $gProdKey + 3), $gProdValue['text']);
                            $debtProdTotalGroup = $debtProdTotal[$gProdValue['text']];

                            $worksheet->setCellValue('F' . $rowGroup, (!empty($debtProdTotalGroup['tar_per']) ? $debtProdTotalGroup['tar_per'] : 0));
                            $worksheet->setCellValue('G' . $rowGroup, (!empty($debtProdTotalGroup['tar_amt']) ? $debtProdTotalGroup['tar_amt'] : 0));
                            $worksheet->setCellValue('H' . $rowGroup, (!empty($debtProdTotalGroup['tar_gap']) ? $debtProdTotalGroup['tar_gap'] : 0));
                            $worksheet->setCellValue('I' . $rowGroup, (!empty($debtProdTotalGroup['inci']) ? $debtProdTotalGroup['inci'] : 0));

                            $worksheet->setCellValue('J' . $rowGroup, (!empty($debtProdTotalGroup['col']) ? $debtProdTotalGroup['col'] : 0));
                            $worksheet->setCellValue('K' . $rowGroup, (!empty($debtProdTotalGroup['rem']) ? $debtProdTotalGroup['rem'] : 0));
                            $worksheet->setCellValue('L' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                            $worksheet->setCellValue('M' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio']) ? $debtProdTotalGroup['col_ratio'] : 0));
                            $worksheet->setCellValue('N' . $rowGroup, (!empty($debtProdTotalGroup['inci_amt']) ? $debtProdTotalGroup['inci_amt'] : 0));
                            $worksheet->setCellValue('O' . $rowGroup, (!empty($debtProdTotalGroup['col_amt']) ? $debtProdTotalGroup['col_amt'] : 0));
                            $worksheet->setCellValue('P' . $rowGroup, (!empty($debtProdTotalGroup['payment_amt']) ? $debtProdTotalGroup['payment_amt'] : 0));
                            $worksheet->setCellValue('Q' . $rowGroup, (!empty($debtProdTotalGroup['rem_actual']) ? $debtProdTotalGroup['rem_actual'] : 0));
                            $worksheet->setCellValue('R' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio_actual']) ? $debtProdTotalGroup['col_ratio_actual'] : 0));
                            $worksheet->setCellValue('S' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_actual']) ? $debtProdTotalGroup['flow_rate_actual'] : 0));

                            $worksheet->getStyle('B' . $rowGroup . ':U' . $rowGroup)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                            $worksheet->getStyle('B' . $rowGroup . ':U'. $rowGroup)->applyFromArray([
                                'font'      => [
                                    'bold'  => true
                                ]
                            ]);

                            foreach($debtProdTotal[$gProdValue['text']] as $totalKey => &$totalValue) {
                                $totalValue = 0;
                            }
                            $rowGroup += 1;
                        }

                        $worksheet->mergeCells('B' . ($start_row + count($groupProduct['data']) + 3) . ':E' . ($start_row + count($groupProduct['data']) + 3));
                        $worksheet->setCellValue('B' . ($start_row + count($groupProduct['data']) + 3), 'D-Total');
                    }

                    $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + 5));
                    $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                    $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                    $worksheet->mergeCells('B' . $start_row_due_date_code . ':B' . ($start_row + 1));
                    $worksheet->setCellValue('B' . $start_row_due_date_code, $due_date_code);

                    $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row + 1));
                    $worksheet->setCellValue('C' . $start_row_prod, $prod_row);

                    $worksheet->mergeCells('B' . ($start_row + 2) . ':E' . ($start_row + 2));
                    $worksheet->setCellValue('B' . ($start_row + 2), 'TOTAL');

                    $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row));
                    $worksheet->setCellValue('D' . $start_row_prod, $due_date);

                    $worksheet->mergeCells('D' . ($start_row + 1) . ':E' . ($start_row + 1));
                    $worksheet->setCellValue('D' . ($start_row + 1), 'TOTAL');

                    $worksheet->setCellValue('F' . ($start_row + 1), (!empty($total['tar_per']) ? $total['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 1), (!empty($total['tar_amt']) ? $total['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 1), (!empty($total['tar_gap']) ? $total['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 1), (!empty($total['inci']) ? $total['inci'] : 0));

                    $worksheet->setCellValue('J' . ($start_row + 1), (!empty($total['col']) ? $total['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 1), (!empty($total['rem']) ? $total['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 1), (!empty($total['flow_rate']) ? $total['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 1), (!empty($total['col_ratio']) ? $total['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 1), (!empty($total['inci_amt']) ? $total['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 1), (!empty($total['col_amt']) ? $total['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 1), (!empty($total['payment_amt']) ? $total['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 1), (!empty($total['rem_actual']) ? $total['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 1), (!empty($total['col_ratio_actual']) ? $total['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' . ($start_row + 1), (!empty($total['flow_rate_actual']) ? $total['flow_rate_actual'] : 0));

                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 2), (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + 2), (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 2), (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 2), (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 2), (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 2), (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 2), (!empty($dueDateCodeTotal['payment_amt']) ? $dueDateCodeTotal['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 2), (!empty($dueDateCodeTotal['rem_actual']) ? $dueDateCodeTotal['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 2), (!empty($dueDateCodeTotal['col_ratio_actual']) ? $dueDateCodeTotal['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate_actual']) ? $dueDateCodeTotal['flow_rate_actual'] : 0));

                    $worksheet->getStyle('B' . ($start_row + 2) . ':U' .($start_row + 2))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084');
                    $worksheet->getStyle('B' . ($start_row + 2) . ':U' . ($start_row + 2))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);
                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col']) ? $debtGroupTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['rem']) ? $debtGroupTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate']) ? $debtGroupTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_ratio']) ? $debtGroupTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci_amt']) ? $debtGroupTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_amt']) ? $debtGroupTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['payment_amt']) ? $debtGroupTotal['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['rem_actual']) ? $debtGroupTotal['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_ratio_actual']) ? $debtGroupTotal['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate_actual']) ? $debtGroupTotal['flow_rate_actual'] : 0));

                     $worksheet->getStyle('B' . ($start_row + count($groupProduct['data']) + 3) . ':U'  .($start_row + count($groupProduct['data']) + 3))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                    $worksheet->getStyle('B' . ($start_row + count($groupProduct['data']) + 3) . ':U' . ($start_row + count($groupProduct['data']) + 3))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);
                    foreach($debtGroupTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
                else
                {
                    $worksheet->mergeCells('B' . ($start_row + 1) . ':E' . ($start_row + 1 ));
                    $worksheet->setCellValue('B' . ($start_row + 1 ), 'F-Total');
                    $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + 1 ));
                    $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                    $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');
                    $worksheet->mergeCells('B' . $start_row_prod . ':D' . $start_row );
                    $worksheet->setCellValue('B' . $start_row_prod, $prod_row);

                    $worksheet->setCellValue('F' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 1 ), (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + 1), (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 1), (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 1), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 1), (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 1), (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 1), (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 1), (!empty($dueDateCodeTotal['payment_amt']) ? $dueDateCodeTotal['payment_amt'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 1), (!empty($dueDateCodeTotal['rem_actual']) ? $dueDateCodeTotal['rem_actual'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 1), (!empty($dueDateCodeTotal['col_ratio_actual']) ? $dueDateCodeTotal['col_ratio_actual'] : 0));
                    $worksheet->setCellValue('S' .($start_row + 1), (!empty($dueDateCodeTotal['rem_os']) ? $dueDateCodeTotal['rem_os'] : 0));
                    $worksheet->setCellValue('T' .($start_row + 1), (!empty($dueDateCodeTotal['flow_rate_os']) ? $dueDateCodeTotal['flow_rate_os'] : 0));
                    $worksheet->setCellValue('U' .($start_row + 1), (!empty($dueDateCodeTotal['col_ratio_os']) ? $dueDateCodeTotal['col_ratio_os'] : 0));

                    $worksheet->getStyle('B' . ($start_row + 1) . ':U'  .($start_row + 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                    $worksheet->getStyle('B' . ($start_row + 1) . ':U' . ($start_row + 1))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);
                    foreach($dueDateCodeTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
            }
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:U".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);



        //GROUP D & E & F
        //Sheet 3
        $worksheet = $spreadsheet->createSheet(2);
        $worksheet->setTitle('Group D & E & F');
        $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);

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

        $worksheet->mergeCells('E1:E3');
        $worksheet->setCellValue('E1', 'GROUP');
        $worksheet->getStyle("E1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("E1")->applyFromArray($style);

        $worksheet->mergeCells('F1:H2');
        $worksheet->setCellValue('F1', 'Target');
        $worksheet->getStyle("F1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("F1")->applyFromArray($style);

        $worksheet->mergeCells('I1:R1');
        $worksheet->setCellValue('I1', $now['mon'].'/'.$now['year']);
        $worksheet->getStyle("I1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("I1")->applyFromArray($style);

        $worksheet->mergeCells('A2:C2');
        $worksheet->setCellValue('A2', 'number.os');
        $worksheet->getStyle("A2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("A2")->applyFromArray($style);

        $worksheet->mergeCells('I2:M2');
        $worksheet->setCellValue('I2', 'Number');
        $worksheet->getStyle("I2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("I2")->applyFromArray($style);

        $worksheet->mergeCells('N2:R2');
        $worksheet->setCellValue('N2', 'Outstanding Balance');
        $worksheet->getStyle("N2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("N2")->applyFromArray($style);

        $worksheet->mergeCells('A3:B3');
        $worksheet->setCellValue('A3', 'Group');
        $worksheet->setCellValue('C3', 'Product');
        $worksheet->getStyle("A3:C3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EDEDED');
        $worksheet->getStyle("A3:C3")->applyFromArray($style);

        $worksheet->setCellValue('F3', 'Percentage');
        $worksheet->setCellValue('G3', 'Account');
        $worksheet->setCellValue('H3', 'Gap (account)');
        $worksheet->getStyle("F3:H3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("F3:H3")->applyFromArray($style);

        $worksheet->setCellValue('I3', 'Total Incidence');
        $worksheet->setCellValue('J3', 'Total Collected');
        $worksheet->getStyle("I3:J3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F4B084');
        $worksheet->getStyle("I3:J3")->applyFromArray($style);

        $worksheet->setCellValue('K3', 'Remaining');
        $worksheet->setCellValue('L3', 'Flow rate');
        $worksheet->setCellValue('M3', 'Collected Ratio');
        $worksheet->getStyle("K3:M3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("K3:M3")->applyFromArray($style);

        $worksheet->setCellValue('N3', 'Total outstanding balance at due date');
        $worksheet->setCellValue('O3', 'Total Collected amount (actual amount)');
        $worksheet->getStyle("N3:O3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F4B084');
        $worksheet->getStyle("N3:O3")->applyFromArray($style);

        $worksheet->setCellValue('P3', 'Remaining');
        $worksheet->setCellValue('Q3', 'Flow rate');
        $worksheet->setCellValue('R3', 'CollectedRatio');
        $worksheet->getStyle("P3:R3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');
        $worksheet->getStyle("P3:R3")->applyFromArray($style);

        foreach(range('C','R') as $columnID) {
            $worksheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $headerStyle = array(
            'font'          => array(
                'bold'      => true,
            ),
            'alignment'     => array(
                'wrapText'  => true,
            )
        );

        $worksheet->getStyle("A1:R3")->applyFromArray($headerStyle);

        $startNumber = 10;
        $startAmt = $startNumber + 4;

        $totalData = array(
            'tar_per'       => 0,
            'tar_amt'       => 0,
            'tar_gap'       => 0,
            'inci'          => 0,
            'col'           => 0,
            'inci_amt'      => 0,
            'col_amt'       => 0,
            'today_rem'     => 0,
            'today_rem_amt' => 0,
            'flow_rate'     => 0,
            'flow_rate_amt' => 0,
            'col_rate'      => 0,
            'col_rate_amt'  => 0
        );

        $dueDateCodeTotal = $totalData;
        $debtGroupTotal = $totalData;
        $debtProdTotal = array();

        foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
            $debtProdTotal[$gProdValue['text']] = $totalData;
        }

        $start_row = 4;

        $start_row_debt_group = 4;
        $debt_group = $dataGroup3[0]['debt_group'];

        $start_row_due_date_code = 4;
        $due_date_code = $dataGroup3[0]['due_date_code'];

        $start_row_prod = 4;
        $prod_row = $dataGroup3[0]['product'];

        $start_due_date = 4;
        $due_date = date('d/m/Y', $dataGroup3[0]['due_date']);

        foreach($dataGroup3 as $key => $value) {
            if($prod_row != $value['product']) {
                $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row - 1));
                $worksheet->setCellValue('D' . $start_row_prod, $due_date);

                $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row));
                $worksheet->setCellValue('C' . $start_row_prod, $prod_row);

                $worksheet->mergeCells('D' . $start_row . ':E' . ($start_row));
                $worksheet->setCellValue('D' . $start_row, 'TOTAL');

                $worksheet->setCellValue('F' . $start_row, (!empty($totalData['tar_per']) ? $totalData['tar_per'] : 0));
                $worksheet->setCellValue('G' . $start_row, (!empty($totalData['tar_amt']) ? $totalData['tar_amt'] : 0));
                $worksheet->setCellValue('H' . $start_row, (!empty($totalData['tar_gap']) ? $totalData['tar_gap'] : 0));
                $worksheet->setCellValue('I' . $start_row, (!empty($totalData['inci']) ? $totalData['inci'] : 0));
                $worksheet->setCellValue('J' . $start_row, (!empty($totalData['col']) ? $totalData['col'] : 0));
                $worksheet->setCellValue('K' . $start_row, (!empty($totalData['rem']) ? $totalData['rem'] : 0));
                $worksheet->setCellValue('L' . $start_row, (!empty($totalData['flow_rate']) ? $totalData['flow_rate'] : 0));
                $worksheet->setCellValue('M' . $start_row, (!empty($totalData['col_ratio']) ? $totalData['col_ratio'] : 0));
                $worksheet->setCellValue('N' . $start_row, (!empty($totalData['inci_amt']) ? $totalData['inci_amt'] : 0));
                $worksheet->setCellValue('O' . $start_row, (!empty($totalData['col_amt']) ? $totalData['col_amt'] : 0));
                $worksheet->setCellValue('P' . $start_row, (!empty($totalData['rem_os']) ? $totalData['rem_os'] : 0));
                $worksheet->setCellValue('Q' . $start_row, (!empty($totalData['flow_rate_os']) ? $totalData['flow_rate_os'] : 0));
                $worksheet->setCellValue('R' . $start_row, (!empty($totalData['col_ratio_os']) ? $totalData['col_ratio_os'] : 0));

                $worksheet->getStyle('D' . $start_row . ':R'  . $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                $worksheet->getStyle('D' . $start_row . ':R' . $start_row)->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($totalData as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }

                $start_row += 1;

                $prod_row = $value['product'];
                $due_date = date('d/m/Y',  $value['due_date']);
                $start_row_prod = $start_row;
                $countTeamInGroup = 0;
            }

            if($due_date_code != $value['due_date_code']) {
                $worksheet->mergeCells('B' . $start_row_due_date_code . ':B' . ($start_row - 1));
                $worksheet->setCellValue('B' . $start_row_due_date_code, $due_date_code);

                $worksheet->mergeCells('B' . $start_row . ':E' . ($start_row));
                $worksheet->setCellValue('B' . $start_row, 'TOTAL');

                $worksheet->setCellValue('F' . $start_row, (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                $worksheet->setCellValue('G' . $start_row, (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                $worksheet->setCellValue('H' . $start_row, (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                $worksheet->setCellValue('I' . $start_row, (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));
                $worksheet->setCellValue('J' . $start_row, (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                $worksheet->setCellValue('K' . $start_row, (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                $worksheet->setCellValue('L' . $start_row, (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                $worksheet->setCellValue('M' . $start_row, (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                $worksheet->setCellValue('N' . $start_row, (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                $worksheet->setCellValue('O' . $start_row, (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                $worksheet->setCellValue('P' . $start_row, (!empty($dueDateCodeTotal['rem_os']) ? $dueDateCodeTotal['rem_os'] : 0));
                $worksheet->setCellValue('Q' . $start_row, (!empty($dueDateCodeTotal['flow_rate_os']) ? $dueDateCodeTotal['flow_rate_os'] : 0));
                $worksheet->setCellValue('R' . $start_row, (!empty($dueDateCodeTotal['col_ratio_os']) ? $dueDateCodeTotal['col_ratio_os'] : 0));

                $worksheet->getStyle('B' . $start_row . ':R'  . $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084');
                $worksheet->getStyle('B' . $start_row . ':R' . $start_row)->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($dueDateCodeTotal as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }

                $start_row += 1;
                $start_row_prod += 1;

                $due_date_code = $value['due_date_code'];
                $start_row_due_date_code = $start_row;
            }

            if($debt_group != $value['debt_group']) {
                if(!empty($groupProduct['data'])) {
                    $rowGroup = $start_row;
                    foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                        $worksheet->mergeCells('B' . ($start_row + $gProdKey) . ':E' . ($start_row + $gProdKey));
                        $worksheet->setCellValue('B' . ($start_row + $gProdKey), $gProdValue['text']);
                        $debtProdTotalGroup = $debtProdTotal[$gProdValue['text']];

                        $worksheet->setCellValue('F' . $rowGroup, (!empty($debtProdTotalGroup['tar_per']) ? $debtProdTotalGroup['tar_per'] : 0));
                        $worksheet->setCellValue('G' . $rowGroup, (!empty($debtProdTotalGroup['tar_amt']) ? $debtProdTotalGroup['tar_amt'] : 0));
                        $worksheet->setCellValue('H' . $rowGroup, (!empty($debtProdTotalGroup['tar_gap']) ? $debtProdTotalGroup['tar_gap'] : 0));
                        $worksheet->setCellValue('I' . $rowGroup, (!empty($debtProdTotalGroup['inci']) ? $debtProdTotalGroup['inci'] : 0));
                        $worksheet->setCellValue('J' . $rowGroup, (!empty($debtProdTotalGroup['col']) ? $debtProdTotalGroup['col'] : 0));
                        $worksheet->setCellValue('K' . $rowGroup, (!empty($debtProdTotalGroup['rem']) ? $debtProdTotalGroup['rem'] : 0));
                        $worksheet->setCellValue('L' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                        $worksheet->setCellValue('M' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio']) ? $debtProdTotalGroup['col_ratio'] : 0));
                        $worksheet->setCellValue('N' . $rowGroup, (!empty($debtProdTotalGroup['inci_amt']) ? $debtProdTotalGroup['inci_amt'] : 0));
                        $worksheet->setCellValue('O' . $rowGroup, (!empty($debtProdTotalGroup['col_amt']) ? $debtProdTotalGroup['col_amt'] : 0));
                        $worksheet->setCellValue('P' . $rowGroup, (!empty($debtProdTotalGroup['rem_os']) ? $debtProdTotalGroup['rem_os'] : 0));
                        $worksheet->setCellValue('Q' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_os']) ? $debtProdTotalGroup['flow_rate_os'] : 0));
                        $worksheet->setCellValue('R' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio_os']) ? $debtProdTotalGroup['col_ratio_os'] : 0));

                        $worksheet->getStyle('B' . $rowGroup . ':R'  . $rowGroup)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                        $worksheet->getStyle('B' . $rowGroup . ':R'  . $rowGroup)->applyFromArray([
                            'font'      => [
                                'bold'  => true
                            ]
                        ]);

                        foreach($debtProdTotal[$gProdValue['text']] as $totalKey => &$totalValue) {
                            $totalValue = 0;
                        }
                        $rowGroup += 1;
                    }

                    $worksheet->mergeCells('B' . ($start_row + count($groupProduct['data'])) . ':E' . ($start_row + count($groupProduct['data'])));
                    $worksheet->setCellValue('B' . ($start_row + count($groupProduct['data'])), $debt_group.'-Total');
                }

                $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + (count($groupProduct['data']))));
                $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));


                $worksheet->setCellValue('J' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col']) ? $groupProduct['col'] : 0));
                $worksheet->setCellValue('K' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['rem']) ? $groupProduct['rem'] : 0));
                $worksheet->setCellValue('L' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['flow_rate']) ? $groupProduct['flow_rate'] : 0));
                $worksheet->setCellValue('M' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_ratio']) ? $groupProduct['col_ratio'] : 0));
                $worksheet->setCellValue('N' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['inci_amt']) ? $groupProduct['inci_amt'] : 0));
                $worksheet->setCellValue('O' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_amt']) ? $groupProduct['col_amt'] : 0));
                $worksheet->setCellValue('P' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['rem_os']) ? $groupProduct['rem_os'] : 0));
                $worksheet->setCellValue('Q' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['flow_rate_os']) ? $groupProduct['flow_rate_os'] : 0));
                $worksheet->setCellValue('R' . ($start_row + count($groupProduct['data'])), (!empty($groupProduct['col_ratio_os']) ? $groupProduct['col_ratio_os'] : 0));

                $worksheet->getStyle('B' . ($start_row + count($groupProduct['data'])) . ':R' .($start_row + count($groupProduct['data'])))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                $worksheet->getStyle('B' . ($start_row + count($groupProduct['data'])) . ':R' . ($start_row + count($groupProduct['data'])))->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($debtGroupTotal as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }


                $start_row += (count($groupProduct['data']) + 1);
                $start_row_prod += (count($groupProduct['data']) + 1);
                $start_row_due_date_code += (count($groupProduct['data']) + 1);

                $debt_group = $value['debt_group'];
                $start_row_debt_group = $start_row;
            }


            $worksheet->setCellValue('E' . $start_row, $value['team']);
            $worksheet->setCellValue('F' . $start_row, (!empty($value['tar_per']) ? $value['tar_per'] : 0));
            $worksheet->setCellValue('G' . $start_row, (!empty($value['tar_amt']) ? $value['tar_amt'] : 0));
            $worksheet->setCellValue('H' . $start_row, (!empty($value['tar_gap']) ? $value['tar_gap'] : 0));
            $worksheet->setCellValue('I' . $start_row, (!empty($value['inci']) ? $value['inci'] : 0));
            $worksheet->setCellValue('J' . $start_row, (!empty($value['col']) ? $value['col'] : 0));
            $worksheet->setCellValue('K' . $start_row, (!empty($value['rem']) ? $value['rem'] : 0));
            $worksheet->setCellValue('L' . $start_row, (!empty($value['flow_rate']) ? $value['flow_rate'] : 0));
            $worksheet->setCellValue('M' . $start_row, (!empty($value['col_ratio']) ? $value['col_ratio'] : 0));
            $worksheet->setCellValue('N' . $start_row, (!empty($value['inci_amt']) ? $value['inci_amt'] : 0));
            $worksheet->setCellValue('O' . $start_row, (!empty($value['col_amt']) ? $value['col_amt'] : 0));
            $worksheet->setCellValue('P' . $start_row, (!empty($value['rem_os']) ? $value['rem_os'] : 0));
            $worksheet->setCellValue('Q' . $start_row, (!empty($value['flow_rate_os']) ? $value['flow_rate_os'] : 0));
            $worksheet->setCellValue('R' . $start_row, (!empty($value['col_ratio_os']) ? $value['col_ratio_os'] : 0));


            foreach($totalData as $keyTotal => &$totalValue) {
                $tempTotalData = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalData;
            }

            foreach($dueDateCodeTotal as $keyTotal => &$totalValue) {
                $tempTotalDateCode = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalDateCode;
            }

            foreach($debtGroupTotal as $keyTotal => &$totalValue) {
                $tempTotalDebtGroup = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                $totalValue += $tempTotalDebtGroup;
            }

            foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                if ($gProdValue['text'] == $value['product']) {
                    foreach($debtProdTotal[$gProdValue['text']] as $keyTotal => &$totalValue) {
                        $tempTotalDebtProd = (!empty($value[$keyTotal])) ? $value[$keyTotal] : 0;
                        $totalValue +=  $tempTotalDebtProd;
                    }
                }

            }

            if ($key == (count($dataGroup3) - 1)) {
                if ($value['debt_group'] != 'F') {
                    if(!empty($groupProduct['data'])) {
                        $rowGroup = $start_row + 3;
                        foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
                            $worksheet->mergeCells('B' . ($start_row + $gProdKey + 3) . ':E' . ($start_row + $gProdKey + 3));
                            $worksheet->setCellValue('B' . ($start_row + $gProdKey + 3), $gProdValue['text']);
                            $debtProdTotalGroup = $debtProdTotal[$gProdValue['text']];

                            $worksheet->setCellValue('F' . $rowGroup, (!empty($debtProdTotalGroup['tar_per']) ? $debtProdTotalGroup['tar_per'] : 0));
                            $worksheet->setCellValue('G' . $rowGroup, (!empty($debtProdTotalGroup['tar_amt']) ? $debtProdTotalGroup['tar_amt'] : 0));
                            $worksheet->setCellValue('H' . $rowGroup, (!empty($debtProdTotalGroup['tar_gap']) ? $debtProdTotalGroup['tar_gap'] : 0));
                            $worksheet->setCellValue('I' . $rowGroup, (!empty($debtProdTotalGroup['inci']) ? $debtProdTotalGroup['inci'] : 0));

                            $worksheet->setCellValue('J' . $rowGroup, (!empty($debtProdTotalGroup['col']) ? $debtProdTotalGroup['col'] : 0));
                            $worksheet->setCellValue('K' . $rowGroup, (!empty($debtProdTotalGroup['rem']) ? $debtProdTotalGroup['rem'] : 0));
                            $worksheet->setCellValue('L' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                            $worksheet->setCellValue('M' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio']) ? $debtProdTotalGroup['col_ratio'] : 0));
                            $worksheet->setCellValue('N' . $rowGroup, (!empty($debtProdTotalGroup['inci_amt']) ? $debtProdTotalGroup['inci_amt'] : 0));
                            $worksheet->setCellValue('O' . $rowGroup, (!empty($debtProdTotalGroup['col_amt']) ? $debtProdTotalGroup['col_amt'] : 0));
                            $worksheet->setCellValue('P' . $rowGroup, (!empty($debtProdTotalGroup['rem_os']) ? $debtProdTotalGroup['rem_os'] : 0));
                            $worksheet->setCellValue('Q' . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_os']) ? $debtProdTotalGroup['flow_rate_os'] : 0));
                            $worksheet->setCellValue('R' . $rowGroup, (!empty($debtProdTotalGroup['col_ratio_os']) ? $debtProdTotalGroup['col_ratio_os'] : 0));

                             $worksheet->getStyle('B' . $rowGroup . ':R' . $rowGroup)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');
                            $worksheet->getStyle('B' . $rowGroup . ':R' . $rowGroup)->applyFromArray([
                                'font'      => [
                                    'bold'  => true
                                ]
                            ]);
                            foreach($debtProdTotal[$gProdValue['text']] as $totalKey => &$totalValue) {
                                $totalValue = 0;
                            }
                            $rowGroup += 1;
                        }

                        $worksheet->mergeCells('B' . ($start_row + count($groupProduct['data']) + 3) . ':E' . ($start_row + count($groupProduct['data']) + 3));
                        $worksheet->setCellValue('B' . ($start_row + count($groupProduct['data']) + 3), 'D-Total');
                    }

                    $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + 5));
                    $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                    $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                    $worksheet->mergeCells('B' . $start_row_due_date_code . ':B' . ($start_row + 1));
                    $worksheet->setCellValue('B' . $start_row_due_date_code, $due_date_code);

                    $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row + 1));
                    $worksheet->setCellValue('C' . $start_row_prod, $prod_row);

                    $worksheet->mergeCells('B' . ($start_row + 2) . ':E' . ($start_row + 2));
                    $worksheet->setCellValue('B' . ($start_row + 2), 'TOTAL');

                    $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row));
                    $worksheet->setCellValue('D' . $start_row_prod, $due_date);

                    $worksheet->mergeCells('D' . ($start_row + 1) . ':E' . ($start_row + 1));
                    $worksheet->setCellValue('D' . ($start_row + 1), 'TOTAL');

                    $worksheet->setCellValue('F' . ($start_row + 1), (!empty($total['tar_per']) ? $total['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 1), (!empty($total['tar_amt']) ? $total['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 1), (!empty($total['tar_gap']) ? $total['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 1), (!empty($total['inci']) ? $total['inci'] : 0));

                    $worksheet->setCellValue('J' . ($start_row + 1), (!empty($total['col']) ? $total['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 1), (!empty($total['rem']) ? $total['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 1), (!empty($total['flow_rate']) ? $total['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 1), (!empty($total['col_ratio']) ? $total['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 1), (!empty($total['inci_amt']) ? $total['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 1), (!empty($total['col_amt']) ? $total['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 1), (!empty($total['rem_os']) ? $total['rem_os'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 1), (!empty($total['flow_rate_os']) ? $total['flow_rate_os'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 1), (!empty($total['col_ratio_os']) ? $total['col_ratio_os'] : 0));

                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 2), (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + 2), (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 2), (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 2), (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 2), (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 2), (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 2), (!empty($dueDateCodeTotal['rem_os']) ? $dueDateCodeTotal['rem_os'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate_os']) ? $dueDateCodeTotal['flow_rate_os'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 2), (!empty($dueDateCodeTotal['col_ratio_os']) ? $dueDateCodeTotal['col_ratio_os'] : 0));

                    $worksheet->getStyle('B' . ($start_row + 2) . ':R' . ($start_row + 2))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F4B084');
                    $worksheet->getStyle('B' . ($start_row + 2) . ':R' . ($start_row + 2))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);
                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col']) ? $debtGroupTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['rem']) ? $debtGroupTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate']) ? $debtGroupTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_ratio']) ? $debtGroupTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci_amt']) ? $debtGroupTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_amt']) ? $debtGroupTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['rem_os']) ? $debtGroupTotal['rem_os'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate_os']) ? $debtGroupTotal['flow_rate_os'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_ratio_os']) ? $debtGroupTotal['col_ratio_os'] : 0));

                    $worksheet->getStyle('B' . ($start_row + count($groupProduct['data']) + 3) . ':R' .($start_row + count($groupProduct['data']) + 3))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                    $worksheet->getStyle('B' . ($start_row + count($groupProduct['data']) + 3) . ':R' . ($start_row + count($groupProduct['data']) + 3))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);

                    foreach($debtGroupTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
                else
                {
                    $worksheet->mergeCells('B' . ($start_row + 1) . ':E' . ($start_row + 1 ));
                    $worksheet->setCellValue('B' . ($start_row + 1 ), 'F-Total');
                    $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + 1 ));
                    $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                    $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');
                    $worksheet->mergeCells('B' . $start_row_prod . ':D' . $start_row );
                    $worksheet->setCellValue('B' . $start_row_prod, $prod_row);

                    $worksheet->setCellValue('F' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 1 ), (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 1 ), (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));


                    $worksheet->setCellValue('J' . ($start_row + 1), (!empty($dueDateCodeTotal['col']) ? $dueDateCodeTotal['col'] : 0));
                    $worksheet->setCellValue('K' . ($start_row + 1), (!empty($dueDateCodeTotal['rem']) ? $dueDateCodeTotal['rem'] : 0));
                    $worksheet->setCellValue('L' . ($start_row + 1), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue('M' . ($start_row + 1), (!empty($dueDateCodeTotal['col_ratio']) ? $dueDateCodeTotal['col_ratio'] : 0));
                    $worksheet->setCellValue('N' . ($start_row + 1), (!empty($dueDateCodeTotal['inci_amt']) ? $dueDateCodeTotal['inci_amt'] : 0));
                    $worksheet->setCellValue('O' . ($start_row + 1), (!empty($dueDateCodeTotal['col_amt']) ? $dueDateCodeTotal['col_amt'] : 0));
                    $worksheet->setCellValue('P' . ($start_row + 1), (!empty($dueDateCodeTotal['rem_os']) ? $dueDateCodeTotal['rem_os'] : 0));
                    $worksheet->setCellValue('Q' . ($start_row + 1), (!empty($dueDateCodeTotal['flow_rate_os']) ? $dueDateCodeTotal['flow_rate_os'] : 0));
                    $worksheet->setCellValue('R' . ($start_row + 1), (!empty($dueDateCodeTotal['col_ratio_os']) ? $dueDateCodeTotal['col_ratio_os'] : 0));

                    $worksheet->getStyle('B' . ($start_row + 1) . ':R' .($start_row + 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
                    $worksheet->getStyle('B' . ($start_row + 1) . ':R' . ($start_row + 1))->applyFromArray([
                        'font'      => [
                            'bold'  => true
                        ]
                    ]);
                    foreach($dueDateCodeTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
            }
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:R".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);



        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "loan/export/" . 'DailyProductivity - each user and group.xlsx';
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