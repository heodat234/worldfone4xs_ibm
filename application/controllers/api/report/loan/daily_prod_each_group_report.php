<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Daily_prod_prod_user_report extends WFF_Controller {

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
            $data = $this->crud->read($this->collection, $request);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function readExcel()
    {
        try {

            $filename = "export.xlsx";
            $file_template = "templateLawsuit.xlsx";

            $rowDataRaw = $this->excel->read(UPLOAD_PATH . "excel/" . $filename, 50, 1);

            echo json_encode($rowDataRaw);
            // var_dump($response);
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
        $request = json_decode($this->input->get("q"), TRUE);
        $request = array();
        $data = $this->crud->where($request)->order_by(array('debt_group' => 'asc', 'due_date_code' => 'asc', 'due_date' => 'asc'))->get($this->collection);
        $product = $this->crud->order_by(array('code' => 'asc'))->get(set_sub_collection('Product'));
        $groupProduct = $this->mongo_private->where(array('tags' => array('group', 'debt', 'product')))->getOne(set_sub_collection("Jsondata"));
        print_r($data);exit;
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
        $worksheet->getDefaultColumnDimension()->setWidth(12);

        $worksheet->mergeCells('A1:C1');
        $worksheet->setCellValue('A1', 'number.os');
        $worksheet->getStyle("A1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

        $worksheet->getStyle("A1")->applyFromArray($style);

        $worksheet->mergeCells('F1:H1');
        $worksheet->setCellValue('F1', 'TARGET');
        $worksheet->getStyle("F1:H2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');

        $worksheet->mergeCells('A2:B2');
        $worksheet->setCellValue('A2', 'Group');
        $worksheet->setCellValue('C2', 'Product');
        $worksheet->getStyle("A2:C2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EDEDED');

        $worksheet->setCellValue('F2', 'Percentage');
        $worksheet->setCellValue('G2', 'Amount');
        $worksheet->setCellValue('H2', 'Gap (amount)');

        $startNumber = 10;
        $startAmt = $startNumber + count($product) * 2 + 4;

        $worksheet->mergeCells($this->stringFromColumnIndex($startNumber - 1) . '1:' . $this->stringFromColumnIndex($startAmt - 1) . '1');
        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber - 1) . '1', 'Number');
        $worksheet->getStyle($this->stringFromColumnIndex($startNumber - 1) . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

        $worksheet->getStyle("D1:D2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FCE4D6');

        $worksheet->getColumnDimension('E')->setAutoSize(true);
        $worksheet->getStyle("E1:E2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');

        $worksheet->setCellValue('I2', 'Incidence');
        $worksheet->getStyle("I2")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('F4B084');

        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product)) . '2', 'Collected');
        $worksheet->getStyle($this->stringFromColumnIndex($startNumber + count($product)) . '2')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('F4B084');

        $worksheet->mergeCells($this->stringFromColumnIndex($startAmt) . '1:' . $this->stringFromColumnIndex($startAmt + count($product) * 2 + 3) . '1');
        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt) . '1', 'Outstanding Balance');
        $worksheet->getStyle($this->stringFromColumnIndex($startAmt) . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt) . '2', 'Total outstanding balance Incidence');
        $worksheet->getStyle($this->stringFromColumnIndex($startAmt) . '2')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('F4B084');

        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + 1) . '2', 'Total Collected amount (actual collected amount)');
        $worksheet->getStyle($this->stringFromColumnIndex($startAmt + count($product) + 1) . '2')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('F4B084');

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

        foreach($product as $keyprod => $valprod) {
            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . '2', $valprod['name'] . ' (' . $valprod['code'] . ')');
            $worksheet->getStyle($this->stringFromColumnIndex($startNumber + $keyprod) . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . '2', $valprod['name'] . ' (' . $valprod['code'] . ')');
            $worksheet->getStyle($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + $keyprod) . '2', $valprod['name'] . ' (' . $valprod['code'] . ')');
            $worksheet->getStyle($this->stringFromColumnIndex($startAmt + 1 + $keyprod) . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 2 + count($product) + $keyprod) . '2', $valprod['name'] . ' (' . $valprod['code'] . ')');
            $worksheet->getStyle($this->stringFromColumnIndex($startAmt + 2 + count($product) + $keyprod) . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

            $totalData['inci_' .  $valprod['code']] = 0;
            $totalData['col_' .  $valprod['code']] = 0;
            $totalData['inci_amt_' .  $valprod['code']] = 0;
            $totalData['col_amt_' .  $valprod['code']] = 0;
        }

        $dueDateCodeTotal = $totalData;
        $debtGroupTotal = $totalData;
        $debtProdTotal = array();

        foreach($groupProduct['data'] as $gProdKey => $gProdValue) {
            $debtProdTotal[$gProdValue['text']] = $totalData;
        }

        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . '2', 'Remaining');
        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . '2', 'Flow rate');
        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . '2', 'Collected ratio');
        $worksheet->getStyle($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . '2' . ':' . $this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . '2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDEBF7');

        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . '2', 'Remaining');
        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . '2', 'Flow rate');
        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . '2', 'Collected ratio');
        $worksheet->getStyle($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . '2' . ':' . $this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . '2')->getFill()
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

        $worksheet->getStyle("A1:" . $this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . '2')->applyFromArray($headerStyle);

        $start_row = 3;

        $start_row_debt_group = 3;
        $debt_group = $data[0]['debt_group'];

        $start_row_due_date_code = 3;
        $due_date_code = $data[0]['due_date_code'];

        $start_row_prod = 3;
        $prod_row = $data[0]['product'];

        $start_due_date = 3;
        $due_date = $data[0]['due_date'];
        $due_date_1 = date('d/m/Y',  $data[0]['due_date']);

        // print_r($data);
        foreach($data as $key => $value) {

            if($prod_row != $value['product']) {
                $due_date_val = new DateTime("@$due_date");
                $worksheet->mergeCells('D' . $start_row_prod . ':D' . ($start_row - 1));
                $worksheet->setCellValue('D' . $start_row_prod, $due_date_1);

                $worksheet->mergeCells('C' . $start_row_prod . ':C' . ($start_row));
                $worksheet->setCellValue('C' . $start_row_prod, $prod_row);

                $worksheet->mergeCells('D' . $start_row . ':E' . ($start_row));
                $worksheet->setCellValue('D' . $start_row, 'TOTAL');

                $worksheet->setCellValue('F' . $start_row, (!empty($totalData['tar_per']) ? $totalData['tar_per'] : 0));
                $worksheet->setCellValue('G' . $start_row, (!empty($totalData['tar_amt']) ? $totalData['tar_amt'] : 0));
                $worksheet->setCellValue('H' . $start_row, (!empty($totalData['tar_gap']) ? $totalData['tar_gap'] : 0));
                $worksheet->setCellValue('I' . $start_row, (!empty($totalData['inci']) ? $totalData['inci'] : 0));
                foreach($product as $keyprod => $valprod) {
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . $start_row, (!empty($totalData['inci_' . $valprod['code']]) ? $totalData['inci_' . $valprod['code']] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . $start_row, (!empty($totalData['col_' . $valprod['code']]) ? $totalData['col_' . $valprod['code']] : 0));

                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod + 1) . $start_row, (!empty($totalData['inci_amt_' . $valprod['code']]) ? $totalData['inci_amt_' . $valprod['code']] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 2 + count($product) + $keyprod) . $start_row, (!empty($totalData['col_amt_' . $valprod['code']]) ? $totalData['col_amt_' . $valprod['code']] : 0));
                }

                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . $start_row, (!empty($totalData['today_rem']) ? $totalData['today_rem'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . $start_row, (!empty($totalData['flow_rate']) ? $totalData['flow_rate'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . $start_row, (!empty($totalData['col_rate']) ? $totalData['col_rate'] : 0));

                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . $start_row, (!empty($totalData['today_rem_amt']) ? $totalData['today_rem_amt'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . $start_row, (!empty($totalData['flow_rate_amt']) ? $totalData['flow_rate_amt'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . $start_row, (!empty($totalData['col_rate_amt']) ? $totalData['col_rate_amt'] : 0));

                $worksheet->getStyle('D' . $start_row . ':' . $this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . $start_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
                $worksheet->getStyle('D' . $start_row . ':' . $this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . $start_row)->applyFromArray([
                    'font'      => [
                        'bold'  => true
                    ]
                ]);

                foreach($totalData as $totalKey => &$totalValue) {
                    $totalValue = 0;
                }

                $start_row += 1;

                $prod_row = $value['product'];
                $due_date_1 = date('d/m/Y',  $value['due_date']);
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
                foreach($product as $keyprod => $valprod) {
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . $start_row, (!empty($dueDateCodeTotal['inci_' . $valprod['code']]) ? $dueDateCodeTotal['inci_' . $valprod['code']] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . $start_row, (!empty($dueDateCodeTotal['col_' . $valprod['code']]) ? $dueDateCodeTotal['col_' . $valprod['code']] : 0));

                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . $start_row, (!empty($dueDateCodeTotal['inci_amt_' . $valprod['code']]) ? $dueDateCodeTotal['inci_amt_' . $valprod['code']] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . $start_row, (!empty($dueDateCodeTotal['col_amt_' . $valprod['code']]) ? $dueDateCodeTotal['col_amt_' . $valprod['code']] : 0));
                }

                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . $start_row, (!empty($dueDateCodeTotal['today_rem']) ? $dueDateCodeTotal['today_rem'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . $start_row, (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . $start_row, (!empty($dueDateCodeTotal['col_rate']) ? $dueDateCodeTotal['col_rate'] : 0));

                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . $start_row, (!empty($dueDateCodeTotal['today_rem_amt']) ? $dueDateCodeTotal['today_rem_amt'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . $start_row, (!empty($dueDateCodeTotal['flow_rate_amt']) ? $dueDateCodeTotal['flow_rate_amt'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . $start_row, (!empty($dueDateCodeTotal['col_rate_amt']) ? $dueDateCodeTotal['col_rate_amt'] : 0));

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
                        foreach($product as $keyprod => $valprod) {
                            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['inci_' . $valprod['code']]) ? $debtProdTotalGroup['inci_' . $valprod['code']] : 0));
                            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['col_' . $valprod['code']]) ? $debtProdTotalGroup['col_' . $valprod['code']] : 0));

                            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['inci_amt_' . $valprod['code']]) ? $debtProdTotalGroup['inci_amt_' . $valprod['code']] : 0));
                            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['col_amt_' . $valprod['code']]) ? $debtProdTotalGroup['col_amt_' . $valprod['code']] : 0));
                        }

                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . $rowGroup, (!empty($debtProdTotalGroup['today_rem']) ? $debtProdTotalGroup['today_rem'] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . $rowGroup, (!empty($debtProdTotalGroup['col_rate']) ? $debtProdTotalGroup['col_rate'] : 0));

                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . $rowGroup, (!empty($debtProdTotalGroup['today_rem_amt']) ? $debtProdTotalGroup['today_rem_amt'] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_amt']) ? $debtProdTotalGroup['flow_rate_amt'] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . $rowGroup, (!empty($debtProdTotalGroup['col_rate_amt']) ? $debtProdTotalGroup['col_rate_amt'] : 0));

                        foreach($debtProdTotal[$gProdValue['text']] as $totalKey => &$totalValue) {
                            $totalValue = 0;
                        }
                        $rowGroup += 1;
                    }

                    $worksheet->mergeCells('B' . ($start_row + count($groupProduct['data'])) . ':E' . ($start_row + count($groupProduct['data'])));
                    $worksheet->setCellValue('B' . ($start_row + count($groupProduct['data'])), 'D-Total');
                }

                $worksheet->mergeCells('A' . $start_row_debt_group . ':A' . ($start_row + (count($groupProduct['data']))));
                $worksheet->setCellValue('A' . $start_row_debt_group, $debt_group);
                $worksheet->getStyle('A' . $start_row_debt_group)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE699');

                $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));
                foreach($product as $keyprod => $valprod) {
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['inci_' . $valprod['code']]) ? $debtGroupTotal['inci_' . $valprod['code']] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['col_' . $valprod['code']]) ? $debtGroupTotal['col_' . $valprod['code']] : 0));

                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['inci_amt_' . $valprod['code']]) ? $debtGroupTotal['inci_amt_' . $valprod['code']] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['col_amt_' . $valprod['code']]) ? $debtGroupTotal['col_amt_' . $valprod['code']] : 0));
                }

                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['today_rem']) ? $debtGroupTotal['today_rem'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['flow_rate']) ? $debtGroupTotal['flow_rate'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['col_rate']) ? $debtGroupTotal['col_rate'] : 0));

                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['today_rem_amt']) ? $debtGroupTotal['today_rem_amt'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['flow_rate_amt']) ? $debtGroupTotal['flow_rate_amt'] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . ($start_row + count($groupProduct['data'])), (!empty($debtGroupTotal['col_rate_amt']) ? $debtGroupTotal['col_rate_amt'] : 0));

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
            foreach($product as $keyprod => $valprod) {
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . $start_row, (!empty($value['inci_' . $valprod['code']]) ? $value['inci_' . $valprod['code']] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . $start_row, (!empty($value['col_' . $valprod['code']]) ? $value['col_' . $valprod['code']] : 0));

                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . $start_row, (!empty($value['inci_amt_' . $valprod['code']]) ? $value['inci_amt_' . $valprod['code']] : 0));
                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . $start_row, (!empty($value['col_amt_' . $valprod['code']]) ? $value['col_amt_' . $valprod['code']] : 0));
            }

            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . $start_row, (!empty($value['today_rem']) ? $value['today_rem'] : 0));
            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . $start_row, (!empty($value['flow_rate']) ? $value['flow_rate'] : 0));
            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . $start_row, (!empty($value['col_rate']) ? $value['col_rate'] : 0));

            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . $start_row, (!empty($value['today_rem_amt']) ? $value['today_rem_amt'] : 0));
            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . $start_row, (!empty($value['flow_rate_amt']) ? $value['flow_rate_amt'] : 0));
            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . $start_row, (!empty($value['col_rate_amt']) ? $value['col_rate_amt'] : 0));

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

            if ($key == (count($data) - 1)) {
                $due_date_val = new DateTime("@$due_date");
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
                            foreach($product as $keyprod => $valprod) {
                                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['inci_' . $valprod['code']]) ? $debtProdTotalGroup['inci_' . $valprod['code']] : 0));
                                $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['col_' . $valprod['code']]) ? $debtProdTotalGroup['col_' . $valprod['code']] : 0));

                                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['inci_amt_' . $valprod['code']]) ? $debtProdTotalGroup['inci_amt_' . $valprod['code']] : 0));
                                $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . $rowGroup, (!empty($debtProdTotalGroup['col_amt_' . $valprod['code']]) ? $debtProdTotalGroup['col_amt_' . $valprod['code']] : 0));
                            }

                            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . $rowGroup, (!empty($debtProdTotalGroup['today_rem']) ? $debtProdTotalGroup['today_rem'] : 0));
                            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . $rowGroup, (!empty($debtProdTotalGroup['flow_rate']) ? $debtProdTotalGroup['flow_rate'] : 0));
                            $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . $rowGroup, (!empty($debtProdTotalGroup['col_rate']) ? $debtProdTotalGroup['col_rate'] : 0));

                            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . $rowGroup, (!empty($debtProdTotalGroup['today_rem_amt']) ? $debtProdTotalGroup['today_rem_amt'] : 0));
                            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . $rowGroup, (!empty($debtProdTotalGroup['flow_rate_amt']) ? $debtProdTotalGroup['flow_rate_amt'] : 0));
                            $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . $rowGroup, (!empty($debtProdTotalGroup['col_rate_amt']) ? $debtProdTotalGroup['col_rate_amt'] : 0));

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
                    $worksheet->setCellValue('D' . $start_row_prod, $due_date_1);

                    $worksheet->mergeCells('D' . ($start_row + 1) . ':E' . ($start_row + 1));
                    $worksheet->setCellValue('D' . ($start_row + 1), 'TOTAL');

                    $worksheet->setCellValue('F' . ($start_row + 1), (!empty($total['tar_per']) ? $total['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 1), (!empty($total['tar_amt']) ? $total['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 1), (!empty($total['tar_gap']) ? $total['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 1), (!empty($total['inci']) ? $total['inci'] : 0));
                    foreach($product as $keyprod => $valprod) {
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . ($start_row + 1), (!empty($total['inci_' . $valprod['code']]) ? $totla['inci_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . ($start_row + 1), (!empty($total['col_' . $valprod['code']]) ? $total['col_' . $valprod['code']] : 0));

                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . ($start_row + 1), (!empty($total['inci_amt_' . $valprod['code']]) ? $total['inci_amt_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . ($start_row + 1), (!empty($total['col_amt_' . $valprod['code']]) ? $total['col_amt_' . $valprod['code']] : 0));
                    }

                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . ($start_row + 1), (!empty($total['today_rem']) ? $total['today_rem'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . ($start_row + 1), (!empty($total['flow_rate']) ? $total['flow_rate'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . ($start_row + 1), (!empty($total['col_rate']) ? $total['col_rate'] : 0));

                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . ($start_row + 1), (!empty($total['today_rem_amt']) ? $total['today_rem_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . ($start_row + 1), (!empty($total['flow_rate_amt']) ? $total['flow_rate_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . ($start_row + 1), (!empty($total['col_rate_amt']) ? $total['col_rate_amt'] : 0));

                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_per']) ? $dueDateCodeTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_amt']) ? $dueDateCodeTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + 2), (!empty($dueDateCodeTotal['tar_gap']) ? $dueDateCodeTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + 2), (!empty($dueDateCodeTotal['inci']) ? $dueDateCodeTotal['inci'] : 0));
                    foreach($product as $keyprod => $valprod) {
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . ($start_row + 2), (!empty($dueDateCodeTotal['inci_' . $valprod['code']]) ? $dueDateCodeTotal['inci_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . ($start_row + 2), (!empty($dueDateCodeTotal['col_' . $valprod['code']]) ? $dueDateCodeTotal['col_' . $valprod['code']] : 0));

                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . ($start_row + 2), (!empty($dueDateCodeTotal['inci_amt_' . $valprod['code']]) ? $dueDateCodeTotal['inci_amt_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . ($start_row + 2), (!empty($dueDateCodeTotal['col_amt_' . $valprod['code']]) ? $dueDateCodeTotal['col_amt_' . $valprod['code']] : 0));
                    }

                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . ($start_row + 2), (!empty($dueDateCodeTotal['today_rem']) ? $dueDateCodeTotal['today_rem'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . ($start_row + 2), (!empty($dueDateCodeTotal['col_rate']) ? $dueDateCodeTotal['col_rate'] : 0));

                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . ($start_row + 2), (!empty($dueDateCodeTotal['today_rem_amt']) ? $dueDateCodeTotal['today_rem_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . ($start_row + 2), (!empty($dueDateCodeTotal['flow_rate_amt']) ? $dueDateCodeTotal['flow_rate_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . ($start_row + 2), (!empty($dueDateCodeTotal['col_rate_amt']) ? $dueDateCodeTotal['col_rate_amt'] : 0));

                    foreach($totalData as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }

                    $worksheet->setCellValue('F' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_per']) ? $debtGroupTotal['tar_per'] : 0));
                    $worksheet->setCellValue('G' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_amt']) ? $debtGroupTotal['tar_amt'] : 0));
                    $worksheet->setCellValue('H' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['tar_gap']) ? $debtGroupTotal['tar_gap'] : 0));
                    $worksheet->setCellValue('I' . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci']) ? $debtGroupTotal['inci'] : 0));
                    foreach($product as $keyprod => $valprod) {
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci_' . $valprod['code']]) ? $debtGroupTotal['inci_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_' . $valprod['code']]) ? $debtGroupTotal['col_' . $valprod['code']] : 0));

                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['inci_amt_' . $valprod['code']]) ? $debtGroupTotal['inci_amt_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_amt_' . $valprod['code']]) ? $debtGroupTotal['col_amt_' . $valprod['code']] : 0));
                    }

                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['today_rem']) ? $debtGroupTotal['today_rem'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 3) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate']) ? $debtGroupTotal['flow_rate'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_rate']) ? $debtGroupTotal['col_rate'] : 0));

                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['today_rem_amt']) ? $debtGroupTotal['today_rem_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 3) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['flow_rate_amt']) ? $debtGroupTotal['flow_rate_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . ($start_row + count($groupProduct['data']) + 3), (!empty($debtGroupTotal['col_rate_amt']) ? $debtGroupTotal['col_rate_amt'] : 0));

                    foreach($debtGroupTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
                else{
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
                    foreach($product as $keyprod => $valprod) {
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + $keyprod) . ($start_row + 1 ), (!empty($dueDateCodeTotal['inci_' . $valprod['code']]) ? $dueDateCodeTotal['inci_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + 1 + count($product) + $keyprod) . ($start_row + 1 ), (!empty($dueDateCodeTotal['col_' . $valprod['code']]) ? $dueDateCodeTotal['col_' . $valprod['code']] : 0));

                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + $keyprod) . ($start_row + 1 ), (!empty($dueDateCodeTotal['inci_amt_' . $valprod['code']]) ? $dueDateCodeTotal['inci_amt_' . $valprod['code']] : 0));
                        $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + 1 + count($product) + $keyprod) . ($start_row + 1 ), (!empty($dueDateCodeTotal['col_amt_' . $valprod['code']]) ? $dueDateCodeTotal['col_amt_' . $valprod['code']] : 0));
                    }

                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 2) . ($start_row + 1 ), (!empty($dueDateCodeTotal['today_rem']) ? $dueDateCodeTotal['today_rem'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod ) . ($start_row + 1 ), (!empty($dueDateCodeTotal['flow_rate']) ? $dueDateCodeTotal['flow_rate'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startNumber + count($product) + $keyprod + 4) . ($start_row + 1 ), (!empty($dueDateCodeTotal['col_rate']) ? $dueDateCodeTotal['col_rate'] : 0));

                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 2) . ($start_row + 1 ), (!empty($dueDateCodeTotal['today_rem_amt']) ? $dueDateCodeTotal['today_rem_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod ) . ($start_row + 1 ), (!empty($dueDateCodeTotal['flow_rate_amt']) ? $dueDateCodeTotal['flow_rate_amt'] : 0));
                    $worksheet->setCellValue($this->stringFromColumnIndex($startAmt + count($product) + $keyprod + 4) . ($start_row + 1 ), (!empty($dueDateCodeTotal['col_rate_amt']) ? $dueDateCodeTotal['col_rate_amt'] : 0));


                    foreach($dueDateCodeTotal as $totalKey => &$totalValue) {
                        $totalValue = 0;
                    }
                }
            }
            $start_row += 1;

        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:BY".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "loan/export/" . 'export.xlsx';
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