<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

Class Outsoucing_collection_trend_report extends WFF_Controller {

    private $collection = "Cus_assigned_partner";
    private $collection_amount = "Outsoucing_Collection_Trend_Amount_Report";
    private $collection_assigned = "Outsoucing_Collection_Trend_AssignDPD_Report";

    public function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
		$this->load->library("mongo_db");
		$this->load->library("excel");
        $this->load->library("mongo_private");
        $this->collection = set_sub_collection($this->collection);
        $this->collection_amount = set_sub_collection($this->collection_amount);
        $this->collection_assigned = set_sub_collection($this->collection_assigned);
        $date = date('Y-m-d');
        $sdate = date('Y-01-01');
        $edate = date('Y-m-d');
        $this->date = strtotime($date);
        $this->sdate = strtotime($sdate);
        $this->edate = strtotime($edate);
    }

    public function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection_amount, $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function get_partner()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->mongo_db->distinct($this->collection, "COMPANY");
            $data = array();
            foreach($response as $value){
                $data[] = array('name' => $value);
            }
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function read_amount()
    {
        try {
            $request = json_decode(file_get_contents('php://input'));           
            if(!empty($request->year)&&is_numeric($request->year)){
                $year = (string)$request->year;
            } else {
                $year = (string)date("Y");
            }
            $where = array('year'=> $year);
            if(!empty($request->partner)){
                $where["partner"] = $request->partner;
            }
            $response = $this->mongo_db->select(['partner', 'outsoucing', 'collected'])->where($where)->get($this->collection_amount);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function read_assigned()
    {
        try {
            $request = json_decode(file_get_contents('php://input'));           
            if(!empty($request->year)&&is_numeric($request->year)){
                $year = (string)$request->year;
            } else {
                $year = (string)date("Y");
            }
            $where = array('year'=> $year);
            if(!empty($request->partner)){
                $where["partner"] = $request->partner;
            }
            $response = $this->mongo_db->select(['partner', 'outsoucing', 'collected'])->where($where)->get($this->collection_assigned);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function exportExcel()
    {
        //redirect(base_url("upload/loan/export/Outsoucing Collection trend.xlsx"));
        try {
            //Nhận và xử lý request
            $request = json_decode(file_get_contents('php://input'));           
            if(!empty($request->year)&&is_numeric($request->year)){
                $year = (string)$request->year;
            } else {
                $year = (string)date("Y");
            }
            $where = array('year'=> $year);
            if(!empty($request->partner)){
                $where["partner"] = $request->partner;
            }
            $map = array("l30"=>"<30","p30"=>"30+","p60"=>"60+","p90"=>"90+","p180"=>"180+","subtotal"=>"subtotal","p360"=>"360+");
            $total_map = array(
                'outsoucing' => array(
                    'account' => array(
                        'l30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p60' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p90' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p180' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'subtotal' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p360' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0)
                    ),
                    'amount' => array(
                        'l30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p60' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p90' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p180' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'subtotal' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p360' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0)
                    )
                ),
                'collected' => array(
                    'account' => array(
                        'l30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p60' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p90' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p180' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'subtotal' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p360' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0)
                    ),
                    'amount' => array(
                        'l30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p30' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p60' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p90' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p180' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'subtotal' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0),
                        'p360' => array("T1" => 0, "T2" => 0,"T3" => 0,"T4" => 0,"T5" => 0,"T6" => 0,"T7" => 0,"T8" => 0,"T9" => 0,"T10" => 0,"T11" => 0,"T12" => 0)
                    )
                )
            ); 
            //---------------------------------------------------------------------------------------------
            $filename = "Outsoucing Collection trend.xlsx";
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                        ->setCreator("South Telecom")
                        ->setLastModifiedBy("Thanh Hung")
                        ->setTitle("SMS DAILY SMS REPORT")
                        ->setSubject("SMS DAILY SMS REPORT")
                        ->setDescription("Office 2007 XLSX, generated using PHP classes.")
                        ->setKeywords("office 2007 openxml php")
                        ->setCategory("Report");
            $worksheet = $spreadsheet->setActiveSheetIndex(0);
            $worksheet = $spreadsheet->getActiveSheet();
            $total_amount = $total_map;
            //Sheet Amount-------------------------------------------------------------------------------------------------------------------------
            //Lấy dữ liệu
            $response = $this->mongo_db->where($where)->order_by(array("partner" => 1))->get($this->collection_amount);
            // print_r($response);die;
            $worksheet = $spreadsheet->createSheet(0); 
            $worksheet = $spreadsheet->getSheet(0);
            $worksheet->setTitle('Amount');
            //Đóng băng cột G, hàng 4
            $worksheet->freezePane('G4');
            //Set Width
            $worksheet->getColumnDimension('A')->setWidth(3);
            foreach(range('B','S') as $col) {
                $worksheet->getColumnDimension($col)->setAutoSize(true);
            }
            // row 1 empty
            // row 2      
            $worksheet->setCellValue("G2", "X");
            $worksheet->setCellValue("O2", "Unit:Number,thousandVND");
            $worksheet->setCellValue('S2', "");
            $worksheet->mergeCells("O2:S2");
            $worksheet->getStyle("O2:S2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // row 3
            $styleArray = array('font' => array('bold' => true));
            $worksheet->setCellValue("G3", date('M-Y', strtotime($year."-01-01")));
            $worksheet->setCellValue("H3", date('M-Y', strtotime($year."-02-01")));
            $worksheet->setCellValue("I3", date('M-Y', strtotime($year."-03-01")));
            $worksheet->setCellValue("J3", date('M-Y', strtotime($year."-04-01")));
            $worksheet->setCellValue("K3", date('M-Y', strtotime($year."-05-01")));
            $worksheet->setCellValue("L3", date('M-Y', strtotime($year."-06-01")));
            $worksheet->setCellValue("M3", date('M-Y', strtotime($year."-07-01")));
            $worksheet->setCellValue("N3", date('M-Y', strtotime($year."-08-01")));
            $worksheet->setCellValue("O3", date('M-Y', strtotime($year."-09-01")));
            $worksheet->setCellValue("P3", date('M-Y', strtotime($year."-10-01")));
            $worksheet->setCellValue("Q3", date('M-Y', strtotime($year."-11-01")));
            $worksheet->setCellValue("R3", date('M-Y', strtotime($year."-12-01")));
            $worksheet->setCellValue("S3", "Total");
            $worksheet->getStyle('G3:S3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle('G3:S3')->applyFromArray($styleArray);
            $i = 4;            
            
            foreach($response as $key => $value){
                //Create Columns Partner
                $worksheet->setCellValue("B".$i, $value["partner"]);
                $worksheet->mergeCells("B".$i.":B".($i+27));
                $worksheet->getStyle("B".$i.":B".($i+27))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $worksheet->getStyle("B".$i.":B".($i+27))->applyFromArray( [
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                //Create Columns Outsoucing/Collected
                $worksheet->setCellValue("C".$i, "Outsoucing");
                $worksheet->mergeCells("C".$i.":C".($i+13));
                $worksheet->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $worksheet->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                //
                foreach($value["outsoucing"] as $key_out => $val_out) { 
                    $worksheet->setCellValue("D".$i, ucfirst($key_out));
                    $worksheet->mergeCells("D".$i.":D".($i+6));
                    $worksheet->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $worksheet->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                        'borders' => [
                            'top' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                            'right' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ]
                        ]
                    ]);
                    foreach($val_out as $key_wf => $val_wf){
                        if($key_wf=="before"){
                            $worksheet->setCellValue("E".$i, "Before \n Write Off");
                            $worksheet->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
                            $worksheet->mergeCells("E".$i.":E".($i+5));
                            $worksheet->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        } else {
                            $worksheet->setCellValue("E".$i, "Write Off");
                            $worksheet->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet->getStyle("E".$i)->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        }
                        
                        foreach($val_wf as $type => $content){
                            $total_amount['outsoucing'][$key_out][$type]["T1"] += $content["T1"];
                            $total_amount['outsoucing'][$key_out][$type]["T2"] += $content["T2"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T3"] += $content["T3"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T4"] += $content["T4"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T5"] += $content["T5"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T6"] += $content["T6"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T7"] += $content["T7"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T8"] += $content["T8"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T9"] += $content["T9"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T10"] += $content["T10"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T11"] += $content["T11"]; 
                            $total_amount['outsoucing'][$key_out][$type]["T12"] += $content["T12"]; 

                            $worksheet->setCellValue("F".$i, $map[$type]);
                            $worksheet->setCellValue("G".$i, $content["T1"]);
                            $worksheet->setCellValue("H".$i, $content["T2"]);
                            $worksheet->setCellValue("I".$i, $content["T3"]);
                            $worksheet->setCellValue("J".$i, $content["T4"]);
                            $worksheet->setCellValue("K".$i, $content["T5"]);
                            $worksheet->setCellValue("L".$i, $content["T6"]);
                            $worksheet->setCellValue("M".$i, $content["T7"]);
                            $worksheet->setCellValue("N".$i, $content["T8"]);
                            $worksheet->setCellValue("O".$i, $content["T9"]);
                            $worksheet->setCellValue("P".$i, $content["T10"]);
                            $worksheet->setCellValue("Q".$i, $content["T11"]);
                            $worksheet->setCellValue("R".$i, $content["T12"]);
                            $worksheet->setCellValue("S".$i, $content["T1"]+$content["T2"]+$content["T3"]+$content["T4"]+$content["T5"]+$content["T6"]+$content["T7"]+$content["T8"]+$content["T9"]+$content["T10"]+$content["T11"]+$content["T12"]);
                            $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                            if(in_array($type, array("l30","p30","p60", "p90"))){
                                $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOTTED,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } elseif($type=="p180"){
                                $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOUBLE,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } else {
                                if($type=="p360"){
                                    $worksheet->getStyle("F".$i.":S".$i)
                                            ->getFill()
                                            ->setFillType(Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setARGB('FFFF00');
                                } else {
                                    $worksheet->getStyle("F".$i.":S".$i)
                                        ->getFill()
                                        ->setFillType(Fill::FILL_SOLID)
                                        ->getStartColor()
                                        ->setARGB('D9E1F2');
                                }
                                $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            }
                            $i++;
                        }
                    }
                }
                //

                $worksheet->setCellValue("C".$i, "Collected");
                $worksheet->mergeCells("C".$i.":C".($i+13));
                $worksheet->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $worksheet->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                foreach($value["collected"] as $key_out => $val_out) {  
                    $worksheet->setCellValue("D".$i, ucfirst($key_out));
                    $worksheet->mergeCells("D".$i.":D".($i+6));
                    $worksheet->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $worksheet->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                        'borders' => [
                            'top' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                            'right' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ]
                        ]
                    ]);

                    foreach($val_out as $key_wf => $val_wf){
                        if($key_wf=="before"){
                            $worksheet->setCellValue("E".$i, "Before \n Write Off");
                            $worksheet->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
                            $worksheet->mergeCells("E".$i.":E".($i+5));
                            $worksheet->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        } else {
                            $worksheet->setCellValue("E".$i, "Write Off");
                            $worksheet->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet->getStyle("E".$i)->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        }
                        foreach($val_wf as $type => $content){
                            $total_amount['collected'][$key_out][$type]["T1"] += $content["T1"];
                            $total_amount['collected'][$key_out][$type]["T2"] += $content["T2"]; 
                            $total_amount['collected'][$key_out][$type]["T3"] += $content["T3"]; 
                            $total_amount['collected'][$key_out][$type]["T4"] += $content["T4"]; 
                            $total_amount['collected'][$key_out][$type]["T5"] += $content["T5"]; 
                            $total_amount['collected'][$key_out][$type]["T6"] += $content["T6"]; 
                            $total_amount['collected'][$key_out][$type]["T7"] += $content["T7"]; 
                            $total_amount['collected'][$key_out][$type]["T8"] += $content["T8"]; 
                            $total_amount['collected'][$key_out][$type]["T9"] += $content["T9"]; 
                            $total_amount['collected'][$key_out][$type]["T10"] += $content["T10"]; 
                            $total_amount['collected'][$key_out][$type]["T11"] += $content["T11"]; 
                            $total_amount['collected'][$key_out][$type]["T12"] += $content["T12"]; 
                            
                            $worksheet->setCellValue("F".$i, $map[$type]);
                            $worksheet->setCellValue("G".$i, $content["T1"]);
                            $worksheet->setCellValue("H".$i, $content["T2"]);
                            $worksheet->setCellValue("I".$i, $content["T3"]);
                            $worksheet->setCellValue("J".$i, $content["T4"]);
                            $worksheet->setCellValue("K".$i, $content["T5"]);
                            $worksheet->setCellValue("L".$i, $content["T6"]);
                            $worksheet->setCellValue("M".$i, $content["T7"]);
                            $worksheet->setCellValue("N".$i, $content["T8"]);
                            $worksheet->setCellValue("O".$i, $content["T9"]);
                            $worksheet->setCellValue("P".$i, $content["T10"]);
                            $worksheet->setCellValue("Q".$i, $content["T11"]);
                            $worksheet->setCellValue("R".$i, $content["T12"]);
                            $worksheet->setCellValue("S".$i, $content["T1"]+$content["T2"]+$content["T3"]+$content["T4"]+$content["T5"]+$content["T6"]+$content["T7"]+$content["T8"]+$content["T9"]+$content["T10"]+$content["T11"]+$content["T12"]);
                            $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                            if(in_array($type, array("l30","p30","p60", "p90"))){
                                $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOTTED,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } elseif($type=="p180"){
                                $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOUBLE,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } else {
                                if($type=="p360"){
                                    $worksheet->getStyle("F".$i.":S".$i)
                                            ->getFill()
                                            ->setFillType(Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setARGB('FFFF00');
                                } else {
                                    $worksheet->getStyle("F".$i.":S".$i)
                                            ->getFill()
                                            ->setFillType(Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setARGB('D9E1F2');
                                }
                                $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            }
                            $i++;
                        }
                    }
                }
            }

            //Tính total
            $worksheet->setCellValue("B".$i, "TOTAL");
            $worksheet->mergeCells("B".$i.":B".($i+27));
            $worksheet->getStyle("B".$i.":B".($i+27))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("B".$i.":B".($i+27))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            //Create Columns Outsoucing/Collected
            $worksheet->setCellValue("C".$i, "Outsoucing");
            $worksheet->mergeCells("C".$i.":C".($i+13));
            $worksheet->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("D".$i, "Account");
            $worksheet->mergeCells("D".$i.":D".($i+6));
            $worksheet->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("E".$i, "Before \n Write Off");
            $worksheet->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet->mergeCells("E".$i.":E".($i+5));
            $worksheet->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);
            
            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet->setCellValue("F".$i, $v_m);
                    $worksheet->setCellValue("G".$i, $total_amount['outsoucing']['account'][$k_m]["T1"]);
                    $worksheet->setCellValue("H".$i, $total_amount['outsoucing']['account'][$k_m]["T2"]);
                    $worksheet->setCellValue("I".$i, $total_amount['outsoucing']['account'][$k_m]["T3"]);
                    $worksheet->setCellValue("J".$i, $total_amount['outsoucing']['account'][$k_m]["T4"]);
                    $worksheet->setCellValue("K".$i, $total_amount['outsoucing']['account'][$k_m]["T5"]);
                    $worksheet->setCellValue("L".$i, $total_amount['outsoucing']['account'][$k_m]["T6"]);
                    $worksheet->setCellValue("M".$i, $total_amount['outsoucing']['account'][$k_m]["T7"]);
                    $worksheet->setCellValue("N".$i, $total_amount['outsoucing']['account'][$k_m]["T8"]);
                    $worksheet->setCellValue("O".$i, $total_amount['outsoucing']['account'][$k_m]["T9"]);
                    $worksheet->setCellValue("P".$i, $total_amount['outsoucing']['account'][$k_m]["T10"]);
                    $worksheet->setCellValue("Q".$i, $total_amount['outsoucing']['account'][$k_m]["T11"]);
                    $worksheet->setCellValue("R".$i, $total_amount['outsoucing']['account'][$k_m]["T12"]);
                    $worksheet->setCellValue("S".$i, $total_amount['outsoucing']['account'][$k_m]["T1"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T2"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T3"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T4"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T5"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T6"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T7"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T8"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T9"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T10"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T11"]+
                                                    $total_amount['outsoucing']['account'][$k_m]["T12"]);
                    $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet->getStyle("F".$i.":S".$i)
                                    ->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('D9E1F2');
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }               
            }

            $worksheet->setCellValue("E".$i, "Write Off");
            $worksheet->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("F".$i, "360+");
            $worksheet->setCellValue("G".$i, $total_amount['outsoucing']['account']["p360"]["T1"]);
            $worksheet->setCellValue("H".$i, $total_amount['outsoucing']['account']["p360"]["T2"]);
            $worksheet->setCellValue("I".$i, $total_amount['outsoucing']['account']["p360"]["T3"]);
            $worksheet->setCellValue("J".$i, $total_amount['outsoucing']['account']["p360"]["T4"]);
            $worksheet->setCellValue("K".$i, $total_amount['outsoucing']['account']["p360"]["T5"]);
            $worksheet->setCellValue("L".$i, $total_amount['outsoucing']['account']["p360"]["T6"]);
            $worksheet->setCellValue("M".$i, $total_amount['outsoucing']['account']["p360"]["T7"]);
            $worksheet->setCellValue("N".$i, $total_amount['outsoucing']['account']["p360"]["T8"]);
            $worksheet->setCellValue("O".$i, $total_amount['outsoucing']['account']["p360"]["T9"]);
            $worksheet->setCellValue("P".$i, $total_amount['outsoucing']['account']["p360"]["T10"]);
            $worksheet->setCellValue("Q".$i, $total_amount['outsoucing']['account']["p360"]["T11"]);
            $worksheet->setCellValue("R".$i, $total_amount['outsoucing']['account']["p360"]["T12"]);
            $worksheet->setCellValue("S".$i, $total_amount['outsoucing']['account']["p360"]["T1"]+
                                            $total_amount['outsoucing']['account']["p360"]["T2"]+
                                            $total_amount['outsoucing']['account']["p360"]["T3"]+
                                            $total_amount['outsoucing']['account']["p360"]["T4"]+
                                            $total_amount['outsoucing']['account']["p360"]["T5"]+
                                            $total_amount['outsoucing']['account']["p360"]["T6"]+
                                            $total_amount['outsoucing']['account']["p360"]["T7"]+
                                            $total_amount['outsoucing']['account']["p360"]["T8"]+
                                            $total_amount['outsoucing']['account']["p360"]["T9"]+
                                            $total_amount['outsoucing']['account']["p360"]["T10"]+
                                            $total_amount['outsoucing']['account']["p360"]["T11"]+
                                            $total_amount['outsoucing']['account']["p360"]["T12"]);
            $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            

            $worksheet->setCellValue("D".$i, "Amount");
            $worksheet->mergeCells("D".$i.":D".($i+6));
            $worksheet->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("E".$i, "Before \n Write Off");
            $worksheet->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet->mergeCells("E".$i.":E".($i+5));
            $worksheet->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet->setCellValue("F".$i, $v_m);
                    $worksheet->setCellValue("G".$i, $total_amount['outsoucing']['amount'][$k_m]["T1"]);
                    $worksheet->setCellValue("H".$i, $total_amount['outsoucing']['amount'][$k_m]["T2"]);
                    $worksheet->setCellValue("I".$i, $total_amount['outsoucing']['amount'][$k_m]["T3"]);
                    $worksheet->setCellValue("J".$i, $total_amount['outsoucing']['amount'][$k_m]["T4"]);
                    $worksheet->setCellValue("K".$i, $total_amount['outsoucing']['amount'][$k_m]["T5"]);
                    $worksheet->setCellValue("L".$i, $total_amount['outsoucing']['amount'][$k_m]["T6"]);
                    $worksheet->setCellValue("M".$i, $total_amount['outsoucing']['amount'][$k_m]["T7"]);
                    $worksheet->setCellValue("N".$i, $total_amount['outsoucing']['amount'][$k_m]["T8"]);
                    $worksheet->setCellValue("O".$i, $total_amount['outsoucing']['amount'][$k_m]["T9"]);
                    $worksheet->setCellValue("P".$i, $total_amount['outsoucing']['amount'][$k_m]["T10"]);
                    $worksheet->setCellValue("Q".$i, $total_amount['outsoucing']['amount'][$k_m]["T11"]);
                    $worksheet->setCellValue("R".$i, $total_amount['outsoucing']['amount'][$k_m]["T12"]);
                    $worksheet->setCellValue("S".$i, $total_amount['outsoucing']['amount'][$k_m]["T1"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T2"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T3"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T4"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T5"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T6"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T7"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T8"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T9"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T10"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T11"]+
                                                    $total_amount['outsoucing']['amount'][$k_m]["T12"]);
                    $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet->getStyle("F".$i.":S".$i)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('D9E1F2');
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }
            }

            $worksheet->setCellValue("E".$i, "Write Off");
            $worksheet->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("F".$i, "360+");
            $worksheet->setCellValue("G".$i, $total_amount['outsoucing']['amount']["p360"]["T1"]);
            $worksheet->setCellValue("H".$i, $total_amount['outsoucing']['amount']["p360"]["T2"]);
            $worksheet->setCellValue("I".$i, $total_amount['outsoucing']['amount']["p360"]["T3"]);
            $worksheet->setCellValue("J".$i, $total_amount['outsoucing']['amount']["p360"]["T4"]);
            $worksheet->setCellValue("K".$i, $total_amount['outsoucing']['amount']["p360"]["T5"]);
            $worksheet->setCellValue("L".$i, $total_amount['outsoucing']['amount']["p360"]["T6"]);
            $worksheet->setCellValue("M".$i, $total_amount['outsoucing']['amount']["p360"]["T7"]);
            $worksheet->setCellValue("N".$i, $total_amount['outsoucing']['amount']["p360"]["T8"]);
            $worksheet->setCellValue("O".$i, $total_amount['outsoucing']['amount']["p360"]["T9"]);
            $worksheet->setCellValue("P".$i, $total_amount['outsoucing']['amount']["p360"]["T10"]);
            $worksheet->setCellValue("Q".$i, $total_amount['outsoucing']['amount']["p360"]["T11"]);
            $worksheet->setCellValue("R".$i, $total_amount['outsoucing']['amount']["p360"]["T12"]);
            $worksheet->setCellValue("S".$i, $total_amount['outsoucing']['amount']["p360"]["T1"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T2"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T3"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T4"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T5"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T6"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T7"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T8"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T9"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T10"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T11"]+
                                            $total_amount['outsoucing']['amount']["p360"]["T12"]);
            $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            //Create Columns Outsoucing/Collected
            $worksheet->setCellValue("C".$i, "Collected");
            $worksheet->mergeCells("C".$i.":C".($i+13));
            $worksheet->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("D".$i, "Account");
            $worksheet->mergeCells("D".$i.":D".($i+6));
            $worksheet->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("E".$i, "Before \n Write Off");
            $worksheet->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet->mergeCells("E".$i.":E".($i+5));
            $worksheet->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);
            
            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet->setCellValue("F".$i, $v_m);
                    $worksheet->setCellValue("G".$i, $total_amount['collected']['account'][$k_m]["T1"]);
                    $worksheet->setCellValue("H".$i, $total_amount['collected']['account'][$k_m]["T2"]);
                    $worksheet->setCellValue("I".$i, $total_amount['collected']['account'][$k_m]["T3"]);
                    $worksheet->setCellValue("J".$i, $total_amount['collected']['account'][$k_m]["T4"]);
                    $worksheet->setCellValue("K".$i, $total_amount['collected']['account'][$k_m]["T5"]);
                    $worksheet->setCellValue("L".$i, $total_amount['collected']['account'][$k_m]["T6"]);
                    $worksheet->setCellValue("M".$i, $total_amount['collected']['account'][$k_m]["T7"]);
                    $worksheet->setCellValue("N".$i, $total_amount['collected']['account'][$k_m]["T8"]);
                    $worksheet->setCellValue("O".$i, $total_amount['collected']['account'][$k_m]["T9"]);
                    $worksheet->setCellValue("P".$i, $total_amount['collected']['account'][$k_m]["T10"]);
                    $worksheet->setCellValue("Q".$i, $total_amount['collected']['account'][$k_m]["T11"]);
                    $worksheet->setCellValue("R".$i, $total_amount['collected']['account'][$k_m]["T12"]);
                    $worksheet->setCellValue("S".$i, $total_amount['collected']['account'][$k_m]["T1"]+
                                                    $total_amount['collected']['account'][$k_m]["T2"]+
                                                    $total_amount['collected']['account'][$k_m]["T3"]+
                                                    $total_amount['collected']['account'][$k_m]["T4"]+
                                                    $total_amount['collected']['account'][$k_m]["T5"]+
                                                    $total_amount['collected']['account'][$k_m]["T6"]+
                                                    $total_amount['collected']['account'][$k_m]["T7"]+
                                                    $total_amount['collected']['account'][$k_m]["T8"]+
                                                    $total_amount['collected']['account'][$k_m]["T9"]+
                                                    $total_amount['collected']['account'][$k_m]["T10"]+
                                                    $total_amount['collected']['account'][$k_m]["T11"]+
                                                    $total_amount['collected']['account'][$k_m]["T12"]);
                    $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet->getStyle("F".$i.":S".$i)
                                    ->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('D9E1F2');
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }               
            }

            $worksheet->setCellValue("E".$i, "Write Off");
            $worksheet->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("F".$i, "360+");
            $worksheet->setCellValue("G".$i, $total_amount['collected']['account']["p360"]["T1"]);
            $worksheet->setCellValue("H".$i, $total_amount['collected']['account']["p360"]["T2"]);
            $worksheet->setCellValue("I".$i, $total_amount['collected']['account']["p360"]["T3"]);
            $worksheet->setCellValue("J".$i, $total_amount['collected']['account']["p360"]["T4"]);
            $worksheet->setCellValue("K".$i, $total_amount['collected']['account']["p360"]["T5"]);
            $worksheet->setCellValue("L".$i, $total_amount['collected']['account']["p360"]["T6"]);
            $worksheet->setCellValue("M".$i, $total_amount['collected']['account']["p360"]["T7"]);
            $worksheet->setCellValue("N".$i, $total_amount['collected']['account']["p360"]["T8"]);
            $worksheet->setCellValue("O".$i, $total_amount['collected']['account']["p360"]["T9"]);
            $worksheet->setCellValue("P".$i, $total_amount['collected']['account']["p360"]["T10"]);
            $worksheet->setCellValue("Q".$i, $total_amount['collected']['account']["p360"]["T11"]);
            $worksheet->setCellValue("R".$i, $total_amount['collected']['account']["p360"]["T12"]);
            $worksheet->setCellValue("S".$i, $total_amount['collected']['account']["p360"]["T1"]+
                                                $total_amount['collected']['account']["p360"]["T2"]+
                                                $total_amount['collected']['account']["p360"]["T3"]+
                                                $total_amount['collected']['account']["p360"]["T4"]+
                                                $total_amount['collected']['account']["p360"]["T5"]+
                                                $total_amount['collected']['account']["p360"]["T6"]+
                                                $total_amount['collected']['account']["p360"]["T7"]+
                                                $total_amount['collected']['account']["p360"]["T8"]+
                                                $total_amount['collected']['account']["p360"]["T9"]+
                                                $total_amount['collected']['account']["p360"]["T10"]+
                                                $total_amount['collected']['account']["p360"]["T11"]+
                                                $total_amount['collected']['account']["p360"]["T12"]);
            $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            

            $worksheet->setCellValue("D".$i, "Amount");
            $worksheet->mergeCells("D".$i.":D".($i+6));
            $worksheet->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("E".$i, "Before \n Write Off");
            $worksheet->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet->mergeCells("E".$i.":E".($i+5));
            $worksheet->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet->setCellValue("F".$i, $v_m);
                    $worksheet->setCellValue("G".$i, $total_amount['collected']['amount'][$k_m]["T1"]);
                    $worksheet->setCellValue("H".$i, $total_amount['collected']['amount'][$k_m]["T2"]);
                    $worksheet->setCellValue("I".$i, $total_amount['collected']['amount'][$k_m]["T3"]);
                    $worksheet->setCellValue("J".$i, $total_amount['collected']['amount'][$k_m]["T4"]);
                    $worksheet->setCellValue("K".$i, $total_amount['collected']['amount'][$k_m]["T5"]);
                    $worksheet->setCellValue("L".$i, $total_amount['collected']['amount'][$k_m]["T6"]);
                    $worksheet->setCellValue("M".$i, $total_amount['collected']['amount'][$k_m]["T7"]);
                    $worksheet->setCellValue("N".$i, $total_amount['collected']['amount'][$k_m]["T8"]);
                    $worksheet->setCellValue("O".$i, $total_amount['collected']['amount'][$k_m]["T9"]);
                    $worksheet->setCellValue("P".$i, $total_amount['collected']['amount'][$k_m]["T10"]);
                    $worksheet->setCellValue("Q".$i, $total_amount['collected']['amount'][$k_m]["T11"]);
                    $worksheet->setCellValue("R".$i, $total_amount['collected']['amount'][$k_m]["T12"]);
                    $worksheet->setCellValue("S".$i, $total_amount['collected']['amount'][$k_m]["T1"]+
                                                    $total_amount['collected']['amount'][$k_m]["T2"]+
                                                    $total_amount['collected']['amount'][$k_m]["T3"]+
                                                    $total_amount['collected']['amount'][$k_m]["T4"]+
                                                    $total_amount['collected']['amount'][$k_m]["T5"]+
                                                    $total_amount['collected']['amount'][$k_m]["T6"]+
                                                    $total_amount['collected']['amount'][$k_m]["T7"]+
                                                    $total_amount['collected']['amount'][$k_m]["T8"]+
                                                    $total_amount['collected']['amount'][$k_m]["T9"]+
                                                    $total_amount['collected']['amount'][$k_m]["T10"]+
                                                    $total_amount['collected']['amount'][$k_m]["T11"]+
                                                    $total_amount['collected']['amount'][$k_m]["T12"]);
                    $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet->getStyle("F".$i.":S".$i)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('D9E1F2');
                        $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }
            }

            $worksheet->setCellValue("E".$i, "Write Off");
            $worksheet->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet->setCellValue("F".$i, "360+");
            $worksheet->setCellValue("G".$i, $total_amount['collected']['amount']["p360"]["T1"]);
            $worksheet->setCellValue("H".$i, $total_amount['collected']['amount']["p360"]["T2"]);
            $worksheet->setCellValue("I".$i, $total_amount['collected']['amount']["p360"]["T3"]);
            $worksheet->setCellValue("J".$i, $total_amount['collected']['amount']["p360"]["T4"]);
            $worksheet->setCellValue("K".$i, $total_amount['collected']['amount']["p360"]["T5"]);
            $worksheet->setCellValue("L".$i, $total_amount['collected']['amount']["p360"]["T6"]);
            $worksheet->setCellValue("M".$i, $total_amount['collected']['amount']["p360"]["T7"]);
            $worksheet->setCellValue("N".$i, $total_amount['collected']['amount']["p360"]["T8"]);
            $worksheet->setCellValue("O".$i, $total_amount['collected']['amount']["p360"]["T9"]);
            $worksheet->setCellValue("P".$i, $total_amount['collected']['amount']["p360"]["T10"]);
            $worksheet->setCellValue("Q".$i, $total_amount['collected']['amount']["p360"]["T11"]);
            $worksheet->setCellValue("R".$i, $total_amount['collected']['amount']["p360"]["T12"]);
            $worksheet->setCellValue("S".$i, $total_amount['collected']['amount']["p360"]["T1"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T3"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T4"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T5"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T6"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T7"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T8"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T9"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T10"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T11"]+
                                            $total_amount['collected']['amount']["p360"]["T2"]["T12"]);
            $worksheet->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            //End Total


            foreach(range('G','R') as $col) {
                $worksheet->getStyle($col."3".":".$col.($i-1))->applyFromArray( [
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ]
                ]);           
            }

            $worksheet->getStyle("B3:S".($i-1))->applyFromArray( [
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            ////////////////////////////////////////////////////////////////////////////////////////
            //Sheet Assigned DPD---------------------------------------------------------------------------------
            $total_assign_dpd = $total_map;
            //Lấy dữ liệu
            $response = $this->mongo_db->where($where)->order_by(array("partner" => 1))->get($this->collection_assigned);
            $worksheet1 = $spreadsheet->createSheet(1); 
            $worksheet1 = $spreadsheet->getSheet(1);
            $worksheet1->setTitle('Assigned DPD');
            //Đóng băng cột G, hàng 4
            $worksheet1->freezePane('G4');
            //Set Width
            $worksheet1->getColumnDimension('A')->setWidth(3);
            foreach(range('B','S') as $col) {
                $worksheet1->getColumnDimension($col)->setAutoSize(true);
            }
            // row 1 empty
            // row 2      
            $worksheet1->setCellValue("G2", "X");
            $worksheet1->setCellValue("O2", "Unit:Number,thousandVND");
            $worksheet1->setCellValue('S2', "");
            $worksheet1->mergeCells("O2:S2");
            $worksheet1->getStyle("O2:S2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // row 3
            $styleArray = array('font' => array('bold' => true));
            $worksheet1->setCellValue("G3", date('M-Y', strtotime($year."-01-01")));
            $worksheet1->setCellValue("H3", date('M-Y', strtotime($year."-02-01")));
            $worksheet1->setCellValue("I3", date('M-Y', strtotime($year."-03-01")));
            $worksheet1->setCellValue("J3", date('M-Y', strtotime($year."-04-01")));
            $worksheet1->setCellValue("K3", date('M-Y', strtotime($year."-05-01")));
            $worksheet1->setCellValue("L3", date('M-Y', strtotime($year."-06-01")));
            $worksheet1->setCellValue("M3", date('M-Y', strtotime($year."-07-01")));
            $worksheet1->setCellValue("N3", date('M-Y', strtotime($year."-08-01")));
            $worksheet1->setCellValue("O3", date('M-Y', strtotime($year."-09-01")));
            $worksheet1->setCellValue("P3", date('M-Y', strtotime($year."-10-01")));
            $worksheet1->setCellValue("Q3", date('M-Y', strtotime($year."-11-01")));
            $worksheet1->setCellValue("R3", date('M-Y', strtotime($year."-12-01")));
            $worksheet1->setCellValue("S3", "Total");
            $worksheet1->getStyle('G3:S3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle('G3:S3')->applyFromArray($styleArray);
            $i = 4;
            foreach($response as $key => $value){
                //Create Columns Partner
                $worksheet1->setCellValue("B".$i, $value["partner"]);
                $worksheet1->mergeCells("B".$i.":B".($i+27));
                $worksheet1->getStyle("B".$i.":B".($i+27))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $worksheet1->getStyle("B".$i.":B".($i+27))->applyFromArray( [
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                //Create Columns Outsoucing/Collected
                $worksheet1->setCellValue("C".$i, "Outsoucing");
                $worksheet1->mergeCells("C".$i.":C".($i+13));
                $worksheet1->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $worksheet1->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                //
                foreach($value["outsoucing"] as $key_out => $val_out) {  
                    $worksheet1->setCellValue("D".$i, ucfirst($key_out));
                    $worksheet1->mergeCells("D".$i.":D".($i+6));
                    $worksheet1->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $worksheet1->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                        'borders' => [
                            'top' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                            'right' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ]
                        ]
                    ]);
                    foreach($val_out as $key_wf => $val_wf){
                        if($key_wf=="before"){
                            $worksheet1->setCellValue("E".$i, "Before \n Write Off");
                            $worksheet1->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
                            $worksheet1->mergeCells("E".$i.":E".($i+5));
                            $worksheet1->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet1->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        } else {
                            $worksheet1->setCellValue("E".$i, "Write Off");
                            $worksheet1->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet1->getStyle("E".$i)->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        }
                        
                        foreach($val_wf as $type => $content){         
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T1"] += $content["T1"];
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T2"] += $content["T2"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T3"] += $content["T3"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T4"] += $content["T4"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T5"] += $content["T5"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T6"] += $content["T6"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T7"] += $content["T7"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T8"] += $content["T8"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T9"] += $content["T9"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T10"] += $content["T10"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T11"] += $content["T11"]; 
                            $total_assign_dpd['outsoucing'][$key_out][$type]["T12"] += $content["T12"]; 
                            
                            $worksheet1->setCellValue("F".$i, $map[$type]);
                            $worksheet1->setCellValue("G".$i, $content["T1"]);
                            $worksheet1->setCellValue("H".$i, $content["T2"]);
                            $worksheet1->setCellValue("I".$i, $content["T3"]);
                            $worksheet1->setCellValue("J".$i, $content["T4"]);
                            $worksheet1->setCellValue("K".$i, $content["T5"]);
                            $worksheet1->setCellValue("L".$i, $content["T6"]);
                            $worksheet1->setCellValue("M".$i, $content["T7"]);
                            $worksheet1->setCellValue("N".$i, $content["T8"]);
                            $worksheet1->setCellValue("O".$i, $content["T9"]);
                            $worksheet1->setCellValue("P".$i, $content["T10"]);
                            $worksheet1->setCellValue("Q".$i, $content["T11"]);
                            $worksheet1->setCellValue("R".$i, $content["T12"]);
                            $worksheet1->setCellValue("S".$i, $content["T1"]+$content["T2"]+$content["T3"]+$content["T4"]+$content["T5"]+$content["T6"]+$content["T7"]+$content["T8"]+$content["T9"]+$content["T10"]+$content["T11"]+$content["T12"]);
                            $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                            if(in_array($type, array("l30","p30","p60", "p90"))){
                                $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOTTED,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } elseif($type=="p180"){
                                $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOUBLE,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } else {
                                if($type=="p360"){
                                    $worksheet1->getStyle("F".$i.":S".$i)
                                            ->getFill()
                                            ->setFillType(Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setARGB('FFFF00');
                                } else {
                                    $worksheet1->getStyle("F".$i.":S".$i)
                                        ->getFill()
                                        ->setFillType(Fill::FILL_SOLID)
                                        ->getStartColor()
                                        ->setARGB('D9E1F2');
                                }
                                $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            }
                            $i++;
                        }
                    }
                }
                //

                $worksheet1->setCellValue("C".$i, "Collected");
                $worksheet1->mergeCells("C".$i.":C".($i+13));
                $worksheet1->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $worksheet1->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ]
                ]);
                foreach($value["collected"] as $key_out => $val_out) {  
                    $worksheet1->setCellValue("D".$i, ucfirst($key_out));
                    $worksheet1->mergeCells("D".$i.":D".($i+6));
                    $worksheet1->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $worksheet1->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                        'borders' => [
                            'top' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                            'right' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ]
                        ]
                    ]);

                    foreach($val_out as $key_wf => $val_wf){
                        if($key_wf=="before"){
                            $worksheet1->setCellValue("E".$i, "Before \n Write Off");
                            $worksheet1->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
                            $worksheet1->mergeCells("E".$i.":E".($i+5));
                            $worksheet1->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet1->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        } else {
                            $worksheet1->setCellValue("E".$i, "Write Off");
                            $worksheet1->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                            $worksheet1->getStyle("E".$i)->applyFromArray( [
                                'borders' => [
                                    'top' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ],
                                    'right' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['rgb' => '000000'],
                                    ]
                                ]
                            ]);
                        }
                        foreach($val_wf as $type => $content){     
                            $total_assign_dpd['collected'][$key_out][$type]["T1"] += $content["T1"];
                            $total_assign_dpd['collected'][$key_out][$type]["T2"] += $content["T2"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T3"] += $content["T3"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T4"] += $content["T4"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T5"] += $content["T5"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T6"] += $content["T6"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T7"] += $content["T7"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T8"] += $content["T8"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T9"] += $content["T9"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T10"] += $content["T10"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T11"] += $content["T11"]; 
                            $total_assign_dpd['collected'][$key_out][$type]["T12"] += $content["T12"]; 

                            $worksheet1->setCellValue("F".$i, $map[$type]);
                            $worksheet1->setCellValue("G".$i, $content["T1"]);
                            $worksheet1->setCellValue("H".$i, $content["T2"]);
                            $worksheet1->setCellValue("I".$i, $content["T3"]);
                            $worksheet1->setCellValue("J".$i, $content["T4"]);
                            $worksheet1->setCellValue("K".$i, $content["T5"]);
                            $worksheet1->setCellValue("L".$i, $content["T6"]);
                            $worksheet1->setCellValue("M".$i, $content["T7"]);
                            $worksheet1->setCellValue("N".$i, $content["T8"]);
                            $worksheet1->setCellValue("O".$i, $content["T9"]);
                            $worksheet1->setCellValue("P".$i, $content["T10"]);
                            $worksheet1->setCellValue("Q".$i, $content["T11"]);
                            $worksheet1->setCellValue("R".$i, $content["T12"]);
                            $worksheet1->setCellValue("S".$i, $content["T1"]+$content["T2"]+$content["T3"]+$content["T4"]+$content["T5"]+$content["T6"]+$content["T7"]+$content["T8"]+$content["T9"]+$content["T10"]+$content["T11"]+$content["T12"]);
                            $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                            if(in_array($type, array("l30","p30","p60", "p90"))){
                                $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOTTED,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } elseif($type=="p180"){
                                $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_DOUBLE,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            } else {
                                if($type=="p360"){
                                    $worksheet1->getStyle("F".$i.":S".$i)
                                            ->getFill()
                                            ->setFillType(Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setARGB('FFFF00');
                                } else {
                                    $worksheet1->getStyle("F".$i.":S".$i)
                                            ->getFill()
                                            ->setFillType(Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setARGB('D9E1F2');
                                }
                                $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                                    'borders' => [
                                        'bottom' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => ['rgb' => '000000'],
                                        ],
                                    ]
                                ]);
                            }
                            $i++;
                        }
                    }
                }
            }

            //Tính total
            $worksheet1->setCellValue("B".$i, "TOTAL");
            $worksheet1->mergeCells("B".$i.":B".($i+27));
            $worksheet1->getStyle("B".$i.":B".($i+27))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("B".$i.":B".($i+27))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            //Create Columns Outsoucing/Collected
            $worksheet1->setCellValue("C".$i, "Outsoucing");
            $worksheet1->mergeCells("C".$i.":C".($i+13));
            $worksheet1->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("D".$i, "Account");
            $worksheet1->mergeCells("D".$i.":D".($i+6));
            $worksheet1->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("E".$i, "Before \n Write Off");
            $worksheet1->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet1->mergeCells("E".$i.":E".($i+5));
            $worksheet1->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);
            
            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet1->setCellValue("F".$i, $v_m);
                    $worksheet1->setCellValue("G".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T1"]);
                    $worksheet1->setCellValue("H".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T2"]);
                    $worksheet1->setCellValue("I".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T3"]);
                    $worksheet1->setCellValue("J".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T4"]);
                    $worksheet1->setCellValue("K".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T5"]);
                    $worksheet1->setCellValue("L".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T6"]);
                    $worksheet1->setCellValue("M".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T7"]);
                    $worksheet1->setCellValue("N".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T8"]);
                    $worksheet1->setCellValue("O".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T9"]);
                    $worksheet1->setCellValue("P".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T10"]);
                    $worksheet1->setCellValue("Q".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T11"]);
                    $worksheet1->setCellValue("R".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T12"]);
                    $worksheet1->setCellValue("S".$i, $total_assign_dpd['outsoucing']['account'][$k_m]["T1"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T2"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T3"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T4"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T5"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T6"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T7"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T8"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T9"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T10"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T11"]+
                                                    $total_assign_dpd['outsoucing']['account'][$k_m]["T12"]);
                    $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet1->getStyle("F".$i.":S".$i)
                                    ->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('D9E1F2');
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }               
            }

            $worksheet1->setCellValue("E".$i, "Write Off");
            $worksheet1->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("F".$i, "360+");
            $worksheet1->setCellValue("G".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T1"]);
            $worksheet1->setCellValue("H".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T2"]);
            $worksheet1->setCellValue("I".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T3"]);
            $worksheet1->setCellValue("J".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T4"]);
            $worksheet1->setCellValue("K".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T5"]);
            $worksheet1->setCellValue("L".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T6"]);
            $worksheet1->setCellValue("M".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T7"]);
            $worksheet1->setCellValue("N".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T8"]);
            $worksheet1->setCellValue("O".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T9"]);
            $worksheet1->setCellValue("P".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T10"]);
            $worksheet1->setCellValue("Q".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T11"]);
            $worksheet1->setCellValue("R".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T12"]);
            $worksheet1->setCellValue("S".$i, $total_assign_dpd['outsoucing']['account']["p360"]["T1"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T2"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T3"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T4"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T5"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T6"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T7"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T8"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T9"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T10"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T11"]+
                                            $total_assign_dpd['outsoucing']['account']["p360"]["T12"]);
            $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet1->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            

            $worksheet1->setCellValue("D".$i, "Amount");
            $worksheet1->mergeCells("D".$i.":D".($i+6));
            $worksheet1->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("E".$i, "Before \n Write Off");
            $worksheet1->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet1->mergeCells("E".$i.":E".($i+5));
            $worksheet1->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet1->setCellValue("F".$i, $v_m);
                    $worksheet1->setCellValue("G".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T1"]);
                    $worksheet1->setCellValue("H".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T2"]);
                    $worksheet1->setCellValue("I".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T3"]);
                    $worksheet1->setCellValue("J".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T4"]);
                    $worksheet1->setCellValue("K".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T5"]);
                    $worksheet1->setCellValue("L".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T6"]);
                    $worksheet1->setCellValue("M".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T7"]);
                    $worksheet1->setCellValue("N".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T8"]);
                    $worksheet1->setCellValue("O".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T9"]);
                    $worksheet1->setCellValue("P".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T10"]);
                    $worksheet1->setCellValue("Q".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T11"]);
                    $worksheet1->setCellValue("R".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T12"]);
                    $worksheet1->setCellValue("S".$i, $total_assign_dpd['outsoucing']['amount'][$k_m]["T1"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T2"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T3"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T4"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T5"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T6"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T7"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T8"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T9"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T10"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T11"]+
                                                    $total_assign_dpd['outsoucing']['amount'][$k_m]["T12"]);
                    $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet1->getStyle("F".$i.":S".$i)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('D9E1F2');
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }
            }

            $worksheet1->setCellValue("E".$i, "Write Off");
            $worksheet1->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("F".$i, "360+");
            $worksheet1->setCellValue("G".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T1"]);
            $worksheet1->setCellValue("H".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T2"]);
            $worksheet1->setCellValue("I".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T3"]);
            $worksheet1->setCellValue("J".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T4"]);
            $worksheet1->setCellValue("K".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T5"]);
            $worksheet1->setCellValue("L".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T6"]);
            $worksheet1->setCellValue("M".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T7"]);
            $worksheet1->setCellValue("N".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T8"]);
            $worksheet1->setCellValue("O".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T9"]);
            $worksheet1->setCellValue("P".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T10"]);
            $worksheet1->setCellValue("Q".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T11"]);
            $worksheet1->setCellValue("R".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T12"]);
            $worksheet1->setCellValue("S".$i, $total_assign_dpd['outsoucing']['amount']["p360"]["T1"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T2"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T3"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T4"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T5"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T6"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T7"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T8"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T9"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T10"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T11"]+
                                            $total_assign_dpd['outsoucing']['amount']["p360"]["T12"]);
            $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet1->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            //Create Columns Outsoucing/Collected
            $worksheet1->setCellValue("C".$i, "Collected");
            $worksheet1->mergeCells("C".$i.":C".($i+13));
            $worksheet1->getStyle("C".$i.":C".($i+13))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("C".$i.":C".($i+13))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("D".$i, "Account");
            $worksheet1->mergeCells("D".$i.":D".($i+6));
            $worksheet1->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("E".$i, "Before \n Write Off");
            $worksheet1->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet1->mergeCells("E".$i.":E".($i+5));
            $worksheet1->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);
            
            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet1->setCellValue("F".$i, $v_m);
                    $worksheet1->setCellValue("G".$i, $total_assign_dpd['collected']['account'][$k_m]["T1"]);
                    $worksheet1->setCellValue("H".$i, $total_assign_dpd['collected']['account'][$k_m]["T2"]);
                    $worksheet1->setCellValue("I".$i, $total_assign_dpd['collected']['account'][$k_m]["T3"]);
                    $worksheet1->setCellValue("J".$i, $total_assign_dpd['collected']['account'][$k_m]["T4"]);
                    $worksheet1->setCellValue("K".$i, $total_assign_dpd['collected']['account'][$k_m]["T5"]);
                    $worksheet1->setCellValue("L".$i, $total_assign_dpd['collected']['account'][$k_m]["T6"]);
                    $worksheet1->setCellValue("M".$i, $total_assign_dpd['collected']['account'][$k_m]["T7"]);
                    $worksheet1->setCellValue("N".$i, $total_assign_dpd['collected']['account'][$k_m]["T8"]);
                    $worksheet1->setCellValue("O".$i, $total_assign_dpd['collected']['account'][$k_m]["T9"]);
                    $worksheet1->setCellValue("P".$i, $total_assign_dpd['collected']['account'][$k_m]["T10"]);
                    $worksheet1->setCellValue("Q".$i, $total_assign_dpd['collected']['account'][$k_m]["T11"]);
                    $worksheet1->setCellValue("R".$i, $total_assign_dpd['collected']['account'][$k_m]["T12"]);
                    $worksheet1->setCellValue("S".$i, $total_assign_dpd['collected']['account'][$k_m]["T1"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T2"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T3"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T4"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T5"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T6"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T7"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T8"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T9"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T10"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T11"]+
                                                    $total_assign_dpd['collected']['account'][$k_m]["T12"]);
                    $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet1->getStyle("F".$i.":S".$i)
                                    ->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()
                                    ->setARGB('D9E1F2');
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }               
            }

            $worksheet1->setCellValue("E".$i, "Write Off");
            $worksheet1->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("F".$i, "360+");
            $worksheet1->setCellValue("G".$i, $total_assign_dpd['collected']['account']["p360"]["T1"]);
            $worksheet1->setCellValue("H".$i, $total_assign_dpd['collected']['account']["p360"]["T2"]);
            $worksheet1->setCellValue("I".$i, $total_assign_dpd['collected']['account']["p360"]["T3"]);
            $worksheet1->setCellValue("J".$i, $total_assign_dpd['collected']['account']["p360"]["T4"]);
            $worksheet1->setCellValue("K".$i, $total_assign_dpd['collected']['account']["p360"]["T5"]);
            $worksheet1->setCellValue("L".$i, $total_assign_dpd['collected']['account']["p360"]["T6"]);
            $worksheet1->setCellValue("M".$i, $total_assign_dpd['collected']['account']["p360"]["T7"]);
            $worksheet1->setCellValue("N".$i, $total_assign_dpd['collected']['account']["p360"]["T8"]);
            $worksheet1->setCellValue("O".$i, $total_assign_dpd['collected']['account']["p360"]["T9"]);
            $worksheet1->setCellValue("P".$i, $total_assign_dpd['collected']['account']["p360"]["T10"]);
            $worksheet1->setCellValue("Q".$i, $total_assign_dpd['collected']['account']["p360"]["T11"]);
            $worksheet1->setCellValue("R".$i, $total_assign_dpd['collected']['account']["p360"]["T12"]);
            $worksheet1->setCellValue("S".$i, $total_assign_dpd['collected']['account']["p360"]["T1"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T2"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T3"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T4"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T5"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T6"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T7"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T8"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T9"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T10"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T11"]+
                                                $total_assign_dpd['collected']['account']["p360"]["T12"]);
            $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet1->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            

            $worksheet1->setCellValue("D".$i, "Amount");
            $worksheet1->mergeCells("D".$i.":D".($i+6));
            $worksheet1->getStyle("D".$i.":D".($i+6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("D".$i.":D".($i+6))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("E".$i, "Before \n Write Off");
            $worksheet1->getStyle('E'.$i)->getAlignment()->setWrapText(true); 
            $worksheet1->mergeCells("E".$i.":E".($i+5));
            $worksheet1->getStyle("E".$i.":E".($i+5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i.":E".($i+5))->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            foreach($map as $k_m => $v_m){
                if($k_m!="p360"){
                    $worksheet1->setCellValue("F".$i, $v_m);
                    $worksheet1->setCellValue("G".$i, $total_assign_dpd['collected']['amount'][$k_m]["T1"]);
                    $worksheet1->setCellValue("H".$i, $total_assign_dpd['collected']['amount'][$k_m]["T2"]);
                    $worksheet1->setCellValue("I".$i, $total_assign_dpd['collected']['amount'][$k_m]["T3"]);
                    $worksheet1->setCellValue("J".$i, $total_assign_dpd['collected']['amount'][$k_m]["T4"]);
                    $worksheet1->setCellValue("K".$i, $total_assign_dpd['collected']['amount'][$k_m]["T5"]);
                    $worksheet1->setCellValue("L".$i, $total_assign_dpd['collected']['amount'][$k_m]["T6"]);
                    $worksheet1->setCellValue("M".$i, $total_assign_dpd['collected']['amount'][$k_m]["T7"]);
                    $worksheet1->setCellValue("N".$i, $total_assign_dpd['collected']['amount'][$k_m]["T8"]);
                    $worksheet1->setCellValue("O".$i, $total_assign_dpd['collected']['amount'][$k_m]["T9"]);
                    $worksheet1->setCellValue("P".$i, $total_assign_dpd['collected']['amount'][$k_m]["T10"]);
                    $worksheet1->setCellValue("Q".$i, $total_assign_dpd['collected']['amount'][$k_m]["T11"]);
                    $worksheet1->setCellValue("R".$i, $total_assign_dpd['collected']['amount'][$k_m]["T12"]);
                    $worksheet1->setCellValue("S".$i, $total_assign_dpd['collected']['amount'][$k_m]["T1"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T2"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T3"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T4"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T5"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T6"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T7"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T8"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T9"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T10"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T11"]+
                                                    $total_assign_dpd['collected']['amount'][$k_m]["T12"]);
                    $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
                    if(in_array($k_m, array("l30","p30","p60", "p90"))){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOTTED,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } elseif($k_m=="p180"){
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    } else {
                        $worksheet1->getStyle("F".$i.":S".$i)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('D9E1F2');
                        $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                            'borders' => [
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ]
                        ]);
                    }
                    $i++;
                }
            }

            $worksheet1->setCellValue("E".$i, "Write Off");
            $worksheet1->getStyle("E".$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet1->getStyle("E".$i)->applyFromArray( [
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);

            $worksheet1->setCellValue("F".$i, "360+");
            $worksheet1->setCellValue("G".$i, $total_assign_dpd['collected']['amount']["p360"]["T1"]);
            $worksheet1->setCellValue("H".$i, $total_assign_dpd['collected']['amount']["p360"]["T2"]);
            $worksheet1->setCellValue("I".$i, $total_assign_dpd['collected']['amount']["p360"]["T3"]);
            $worksheet1->setCellValue("J".$i, $total_assign_dpd['collected']['amount']["p360"]["T4"]);
            $worksheet1->setCellValue("K".$i, $total_assign_dpd['collected']['amount']["p360"]["T5"]);
            $worksheet1->setCellValue("L".$i, $total_assign_dpd['collected']['amount']["p360"]["T6"]);
            $worksheet1->setCellValue("M".$i, $total_assign_dpd['collected']['amount']["p360"]["T7"]);
            $worksheet1->setCellValue("N".$i, $total_assign_dpd['collected']['amount']["p360"]["T8"]);
            $worksheet1->setCellValue("O".$i, $total_assign_dpd['collected']['amount']["p360"]["T9"]);
            $worksheet1->setCellValue("P".$i, $total_assign_dpd['collected']['amount']["p360"]["T10"]);
            $worksheet1->setCellValue("Q".$i, $total_assign_dpd['collected']['amount']["p360"]["T11"]);
            $worksheet1->setCellValue("R".$i, $total_assign_dpd['collected']['amount']["p360"]["T12"]);
            $worksheet1->setCellValue("S".$i, $total_assign_dpd['collected']['amount']["p360"]["T1"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T3"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T4"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T5"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T6"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T7"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T8"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T9"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T10"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T11"]+
                                            $total_assign_dpd['collected']['amount']["p360"]["T2"]["T12"]);
            $worksheet1->getStyle("F$i:S$i")->getNumberFormat()->setFormatCode('#,##0');
            $worksheet1->getStyle("F".$i.":S".$i)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            $worksheet1->getStyle("F".$i.":S".$i)->applyFromArray( [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ]
            ]);
            $i++;
            //End Total

            foreach(range('G','R') as $col) {
                $worksheet1->getStyle($col."3".":".$col.($i-1))->applyFromArray( [
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ]
                ]);           
            }

            $worksheet1->getStyle("B3:S".($i-1))->applyFromArray( [
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ]
                ]
            ]);
            //Tạo file excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $file_path = UPLOAD_PATH . "loan/export/" . $filename;
            $writer->save($file_path);

            echo json_encode(array("status" => 1, "data" => $file_path));
        } catch(Exception $ex) {
            echo json_encode(array("status" => 0, "message" => $ex->getMessage()));
        }
    }

}