<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Monthly_japanese_report extends WFF_Controller {

    private $collection_total = "Collection_factors_total";
    private $collection_detail = "Collection_factors_detail";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection_total = set_sub_collection($this->collection_total);
        $this->collection_detail = set_sub_collection($this->collection_detail);
    }

    function read_total() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection_total, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function read_detail() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection_detail, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function exportExcel(){
        try {
            $request    = $this->input->post();

            $date =str_replace('/', '-', $request['month']);
            $smonth = strtotime($date);
            $lastDateOfMonth = strtotime(date("Y-m-t", strtotime($date)));
        
            $match = array(
                '$and' => array(
                array('created_at'=> array( '$gte'=> $smonth, '$lte'=> $lastDateOfMonth))
                )               
            );
            $data_total = $this->crud->where($match)->order_by(array('index' => 'asc','detail'=>'asc'))->get($this->collection_total);
            $data_detail = $this->crud->where($match)->order_by(array('index' => 'asc'))->get($this->collection_detail);
            
            $filename = "MONTHLY JAPANESE REPORT ".str_replace('/', '-', $request['export']).".xlsx";
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
            ->setCreator("South Telecom")
            ->setLastModifiedBy("Son Vu")
            ->setTitle("Monthly Japanese Report")
            ->setSubject("Monthly Japanese Report")
            ->setDescription("Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Report");

            
            // TOTAL
            $worksheet = $spreadsheet->setActiveSheetIndex(0);
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet = $spreadsheet->getSheet(0);
            $worksheet->setTitle('MONTHLY JAPANESE TOTAL REPORT');
            $fieldToCol = array();
                // Title row
                $row = 2;
                $worksheet->setCellValue("A1", "Collection Factors");
                $worksheet->setCellValue("A2", "");
                $worksheet->getColumnDimension('A')->setAutoSize(true);
                $worksheet->setCellValue("B2", "");
                $worksheet->getColumnDimension('B')->setAutoSize(true);
                $worksheet->setCellValue("C2", "Last month");
                $worksheet->getColumnDimension('C')->setAutoSize(true);
                $worksheet->setCellValue("D1", "Unit Thousand dong");
                $worksheet->setCellValue("D2", "This month");
                $worksheet->getColumnDimension('D')->setAutoSize(true);
                $styleArray = array(
                    
                        
                    'font'  => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'ffffff'),
                        'size'  => 13
                       
                    ));
                $worksheet->getStyle("A2:D2")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('008738');
                
                $style = array('font' => array('bold' => true,
                                               'color' => array('rgb' => 'ffffff'),
                                               'size'  => 13),
                 'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                
                
                ));

              
                $worksheet->getStyle("A2:D2")->applyFromArray($style);

                $worksheet->getStyle('C')->getNumberFormat()->setFormatCode("#,##0");
                $worksheet->getStyle('D')->getNumberFormat()->setFormatCode("#,##0");
                $worksheet->getStyle('A')->getFont()->setBold(true);
          
                $start_row = 3;
                
                foreach($data_total as $key => $value) {
                    $detail_name = (isset($value['detail_name']))?$value['detail_name']:'';
                    if($value['detail'] == 'amt') {
                        $lastMonth = (isset($value['last_month']))?$value['last_month']/1000:'';
                        $thisMonth = (isset($value['this_month']))?$value['this_month']/1000:'';
                    }else{
                        $lastMonth = (isset($value['last_month']))?$value['last_month']:'';
                        $thisMonth = (isset($value['this_month']))?$value['this_month']:'';
                    }
                       

                    $worksheet->setCellValue('A' . $start_row, $detail_name);
                    $worksheet->setCellValueExplicit('B' . $start_row, $value['product_name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $worksheet->setCellValueExplicit('C' . $start_row, $lastMonth, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $worksheet->setCellValueExplicit('D' . $start_row, $thisMonth, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                   
                    
                   
                    
                $start_row += 1;
                }
                $total_row=count($data_total)+2;
                $worksheet->getStyle("A2:D$total_row")->getBorders()
                ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


            // DETAIL

            //card
            $worksheet_detail = $spreadsheet->createSheet(1);
            $worksheet_detail->setTitle('MONTHLY JAPANESE DETAIL REPORT');
            $fieldToCol = array();
            // Title row
            $worksheet_detail->setCellValue("A1", "Collection Factors");
            $worksheet_detail->mergeCells('A2:A3');
            $worksheet_detail->setCellValue("A2", "");
            $worksheet_detail->getColumnDimension('A')->setAutoSize(true);
            $worksheet_detail->mergeCells('B2:B3');
            $worksheet_detail->setCellValue("B2", "");
            $worksheet_detail->getColumnDimension('B')->setAutoSize(true);
            $worksheet_detail->mergeCells('C2:D3');
            $worksheet_detail->setCellValue("C2", "Last month");
            $worksheet_detail->getColumnDimension('C')->setAutoSize(true);
            $worksheet_detail->setCellValue("D1", "Unit Thousand dong");
            $worksheet_detail->getColumnDimension('D')->setAutoSize(true);
            $worksheet_detail->mergeCells('E2:F3');
            $worksheet_detail->setCellValue("E2", "This month");
            $worksheet_detail->getColumnDimension('E')->setAutoSize(true);
            $worksheet_detail->getColumnDimension('F')->setAutoSize(true);
           

            $worksheet_detail->getStyle("A2:F3")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('008738');
            $style = array('font' => array('bold' => true,
            'color' => array('rgb' => 'ffffff'),
            'size'  => 13),
            'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ));
            $worksheet_detail->getStyle("A2:G2")->applyFromArray($style);
            $worksheet_detail->getStyle('C:F')->getNumberFormat()->setFormatCode("#,##0");
            $worksheet_detail->getStyle('A')->getFont()->setBold(true);

            $start_row1 = 4;
            foreach($data_detail as $key1 => $value1) {
                $group_name = (isset($value1['group_name']))?$value1['group_name']:'';
                $product_name = (isset($value1['product_name']))?$value1['product_name']:'';
                $last_month_acc = (isset($value1['last_month_acc']))?$value1['last_month_acc']:'';
                $this_month_acc = (isset($value1['this_month_acc']))?$value1['this_month_acc']:'';
                if(strpos($value1['type_detail'], 'ratio')) {
                    $last_month_amt = (isset($value1['last_month_amt']))?$value1['last_month_amt']:'';
                    $this_month_amt = (isset($value1['this_month_amt']))?$value1['this_month_amt']:'';
                }
                else {
                    $last_month_amt = (isset($value1['last_month_amt']))?$value1['last_month_amt'] / 1000:'';
                    $this_month_amt = (isset($value1['this_month_amt']))?$value1['this_month_amt'] / 1000:'';
                }

                if(strpos($value1['type_detail'], 'ratio')) {
                    $worksheet_detail->getStyle('D' . $start_row1)->getNumberFormat()->setFormatCode('0.00%');
                    $worksheet_detail->getStyle('F' . $start_row1)->getNumberFormat()->setFormatCode('0.00%');
                }
                $worksheet_detail->setCellValue('A' . $start_row1, $group_name);
                $worksheet_detail->setCellValueExplicit('B' . $start_row1, $product_name, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $worksheet_detail->setCellValueExplicit('C' . $start_row1, $last_month_acc, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet_detail->setCellValueExplicit('E' . $start_row1, $this_month_acc, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet_detail->setCellValueExplicit('D' . $start_row1, $last_month_amt, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $worksheet_detail->setCellValueExplicit('F' . $start_row1, $this_month_amt, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                
            $start_row1 += 1;
            }
            $total_row1=count($data_detail)+3;
            $worksheet_detail->getStyle("A2:F$total_row1")->getBorders()
            ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);



            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $file_path = UPLOAD_PATH . "loan/export/" . $filename;
            $writer->save($file_path);
            echo json_encode(array("status" => 1, "data" => $file_path));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }

        




    }
}