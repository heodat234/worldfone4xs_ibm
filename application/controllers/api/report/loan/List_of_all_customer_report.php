<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class List_of_all_customer_report extends WFF_Controller {

    private $collection = "List_of_all_customer_report";
    private $product = "LO_Product";
    private $data_total = "LO_List_of_all_customer_total_report";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
        $date = date('d-m-Y');
        $sdate = date('1-m-Y');
        $edate = date('t-m-Y');
        $this->date = strtotime($date);
        $this->sdate = strtotime($sdate) - 3600 *24 *20;
        $this->edate = strtotime($edate);
    }

    function all_loan_group()
    {
        try {
            
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "stt", "dir" => "asc"));
            $match = array('createdAt' => ['$gte' => $this->sdate, '$lte' => $this->edate]);
            $response = $this->crud->read($this->collection, $request,[],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function product()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "code", "dir" => "asc"));
            $match = array();
            $response = $this->crud->read($this->product, $request,['code','name'],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function data()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $request['sort'] = array(array("field" => "index", "dir" => "asc"));
            $match = array('createdAt' => ['$gte' => $this->sdate, '$lte' => $this->edate]);
            $response = $this->crud->read($this->data_total, $request,[],$match);
            
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function save()
    {
        shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveSmsDaily.py  > /dev/null &');
    }






   
    function exportExcel(){
        try {
            $request    = $this->input->post();

            $date =str_replace('/', '-', $request['month']);
            $smonth = strtotime($date);
            $lastDateOfMonth = strtotime(date("Y-m-t", strtotime($date)));
            
            $match = array(
                '$and' => array(
                array('createdAt'=> array( '$gte'=> $smonth, '$lte'=> $lastDateOfMonth))
                )               
            );
            $data = $this->crud->where($match)->order_by(array('index' => 'asc'))->get($this->data_total);
            $product = $this->crud->order_by(array('code' => 'asc'))->get($this->product);
           
            
            $filename = "List of all customer by Loan Group ".str_replace('/', '-', $request['export']).".xlsx";
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
            ->setCreator("South Telecom")
            ->setLastModifiedBy("Son Vu")
            ->setTitle("List of all customer by Loan Group Report")
            ->setSubject("List of all customer by Loan Group Report")
            ->setDescription("Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Report");

            
            // TOTAL
            $worksheet = $spreadsheet->setActiveSheetIndex(0);
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet = $spreadsheet->getSheet(0);
            $worksheet->setTitle('summary report');
            $fieldToCol = array();
                // Title row
              $coll ="A"; 
            foreach($product as $col){
                $worksheet->setCellValue("A1", "Collection Factors");
                $worksheet->setCellValue("A2", "");
                $worksheet->getColumnDimension('A')->setAutoSize(true);
            }
                $row = 2;
               
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


           


            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $file_path = UPLOAD_PATH . "loan/export/" . $filename;
            $writer->save($file_path);
            echo json_encode(array("status" => 1, "data" => $file_path));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }

    }

    function downloadExcel()
    {
        $dmonth =str_replace('/','',$_POST['month']);
        // $date = getdate();
        // $day = $date['mday'];
        // $month = $date['mon'];
        // if ($date['mday'] < 10) {
        //     $day = '0'.(string)$date['mday'];
        // }
        // if ($date['mon'] < 10) {
        //     $month = '0'.(string)$date['mon'];
        // }
        $file_path = UPLOAD_PATH . "loan/export/ListofallcustomerReport_". $dmonth .".xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}



