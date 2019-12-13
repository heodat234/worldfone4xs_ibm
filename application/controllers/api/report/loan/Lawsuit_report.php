<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Lawsuit_report extends WFF_Controller {

    private $collection = "Lawsuit";
    private $model_collection = "Model";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->collection = set_sub_collection($this->collection);

    }

    function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function exportExcel() {
        $request    = $this->input->post();
        $start      =  strtotime($request['start']);
        $end        = strtotime(str_replace('/', '-', $request['end'])) ;
        // print_r($start);exit;    
        $match = array(
                 '$and' => array(
                    array('created_at'=> array( '$gte'=> $start, '$lte'=> $end))
                 )               
             );
        $data = $this->crud->where($match)->order_by(array('index' => 'asc'))->get($this->collection);

        $request = array (
          'take' => 50,
          'skip' => 0,
          "sort" => array(array("field" => "index", "dir" => "asc"))
        );
        $match = array( "collection" => $this->collection, 'sub_type' => array('$exists' => 'true') );
        $this->crud->select_db($this->config->item("_mongo_db"));
        $response = $this->crud->read("Model", $request, ["index","field", "title", "type"], $match);
        $response = $response['data'];
        foreach ($response as $key => $value) {
            $model[$value['field']] = $value;
        }

        // print_r($data);exit;
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator("South Telecom")
        ->setLastModifiedBy("Thanh Hung")
        ->setTitle("Lawsuit Report")
        ->setSubject("Lawsuit Report")
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
        $worksheet->getDefaultColumnDimension()->setWidth(30);

        $worksheet->mergeCells('A1:A2');
        $worksheet->setCellValue('A1', 'STT (No.)');

        $worksheet->mergeCells('B1:B2');
        $worksheet->setCellValue('B1', 'THÁNG');

        $worksheet->mergeCells('C1:C2');
        $worksheet->setCellValue('C1', 'SỐ HỢP ĐỒNG (AC No.)');

        $worksheet->mergeCells('D1:D2');
        $worksheet->setCellValue('D1', "TÊN KH (Cust's Name)");
        $worksheet->getStyle("A1:D2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("A1:D2")->applyFromArray($style);

        $worksheet->mergeCells('E1:F1');
        $worksheet->setCellValue('E1', 'ĐỊA ĐIỂM (Place) TP. : thành phố H. : huyện TX. : thị xã Q. : quận');
        $worksheet->setCellValue('E2', "QUẬN/ HUYỆN (District)");
        $worksheet->setCellValue('F2', "TỈNH/ THÀNH (Province/ City)");
        $worksheet->getStyle("E1:F2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C6E0B4');
        $worksheet->getStyle("E1:F2")->applyFromArray($style);

        $worksheet->mergeCells('G1:G2');
        $worksheet->setCellValue('G1', "NGƯỜI ĐƯỢC ỦY QUYỀN");
        $worksheet->getStyle("G1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("G1")->applyFromArray($style);

        $worksheet->mergeCells('H1:K1');
        $worksheet->setCellValue('H1', 'HỢP ĐỒNG (Contract)');
        $worksheet->setCellValue('H2', "NỢ GỐC (Principal)");
        $worksheet->setCellValue('I2', "LÃI (Interest)");
        $worksheet->setCellValue('J2', "TỔNG CỘNG");
        $worksheet->setCellValue('K2', "Tiền hàng tháng (Monthly)");
        $worksheet->getStyle("H1:K2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("H1:K2")->applyFromArray($style);

        $worksheet->mergeCells('L1:O1');
        $worksheet->setCellValue('L1', 'SỐ TIỀN ĐÃ THANH TOÁN (Paid amount)');
        $worksheet->setCellValue('L2', "NỢ GỐC (Principal)");
        $worksheet->setCellValue('M2', "LÃI (Interest)");
        $worksheet->setCellValue('N2', "PHẠT (Late charge)");
        $worksheet->setCellValue('O2', "TỔNG CỘNG (Total payment)");
        $worksheet->getStyle("L1:O1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('92D050');
        $worksheet->getStyle("L1:O1")->applyFromArray($style);
        $worksheet->getStyle("L2:O2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C9C9C9');
        $worksheet->getStyle("L2:O2")->applyFromArray($style);

        $worksheet->mergeCells('P1:T1');
        $worksheet->setCellValue('P1', 'DƯ NỢ KHỞI KIỆN (O/S balance when lawsuit)');
        $worksheet->setCellValue('P2', "NỢ GỐC (Principal)");
        $worksheet->setCellValue('Q2', "LÃI (Interest)");
        $worksheet->setCellValue('R2', "PHẠT (Late charge)");
        $worksheet->setCellValue('S2', "PHÍ TẤT TOÁN (Prepayment fee)");
        $worksheet->setCellValue('T2', "TỔNG CỘNG");
        $worksheet->getStyle("P1:T2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFE699');
        $worksheet->getStyle("P1:T2")->applyFromArray($style);

        $worksheet->mergeCells('U1:V1');
        $worksheet->setCellValue('U1', 'CỬA HÀNG (Dealer)');
        $worksheet->setCellValue('U2', "TÊN (Name)");
        $worksheet->setCellValue('V2', "ĐỊA CHỈ (Address)");
        $worksheet->getStyle("U1:V2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFE699');
        $worksheet->getStyle("U1:V2")->applyFromArray($style);

        $worksheet->mergeCells('W1:W2');
        $worksheet->setCellValue('W1', 'TẠM ỨNG ÁN PHÍ (Dự tính)');

        $worksheet->mergeCells('X1:X2');
        $worksheet->setCellValue('X1', 'PHƯƠNG THỨC NỘP');

        $worksheet->mergeCells('Y1:Y2');
        $worksheet->setCellValue('Y1', 'Ngày gởi FC');

        $worksheet->mergeCells('Z1:Z2');
        $worksheet->setCellValue('Z1', "NGÀY NỘP ĐƠN (Submiting date)");

        $worksheet->mergeCells('AA1:AA2');
        $worksheet->setCellValue('AA1', 'NGÀY NỘP TƯAP (Date of Advance pay)');

        $worksheet->mergeCells('AB1:AB2');
        $worksheet->setCellValue('AB1', 'NHẬN THÔNG BÁO THỤ LÝ');

        $worksheet->mergeCells('AC1:AC2');
        $worksheet->setCellValue('AC1', 'NGÀY HÒA GIẢI LẦN 1 (1st conciliation)');

        $worksheet->mergeCells('AD1:AD2');
        $worksheet->setCellValue('AD1', "NGÀY HÒA GIẢI LẦN 2 (2nd conciliation)");

        $worksheet->mergeCells('AE1:AE2');
        $worksheet->setCellValue('AE1', 'NGÀY HÒA GIẢI LẦN 3 (3nd conciliation)');

        $worksheet->mergeCells('AF1:AF2');
        $worksheet->setCellValue('AF1', "NGÀY XÉT XỬ SƠ THẨM (First instance trial date)");

        $worksheet->mergeCells('AG1:AG2');
        $worksheet->setCellValue('AG1', 'KHÁNG CÁO');

        $worksheet->mergeCells('AH1:AH2');
        $worksheet->setCellValue('AH1', 'NGÀY XÉT XỬ PHÚC THẨM (Trial of Appeal date, if any)');

        $worksheet->mergeCells('AI1:AI2');
        $worksheet->setCellValue('AI1', 'Theo dõi');

        $worksheet->getStyle("W1:AT2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("W1:AT2")->applyFromArray($style);

        $worksheet->mergeCells('AJ1:AJ2');
        $worksheet->setCellValue('AJ1', "Phương hướng giải quyết");
        $worksheet->getStyle("AJ1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('A5A5A5');
        $worksheet->getStyle("AJ1")->applyFromArray($style);

        $worksheet->mergeCells('AK1:AK2');
        $worksheet->setCellValue('AK1', "TÌNH TRẠNG KHỞI KIỆN (Lawsuit status)");
        $worksheet->getStyle("AK1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("AK1")->applyFromArray($style);

        $worksheet->mergeCells('AL1:AP1');
        $worksheet->setCellValue('AL1', 'TẠM ỨNG ÁN PHÍ');
        $worksheet->setCellValue('AL2', "Ngày tạm ứng");
        $worksheet->setCellValue('AM2', "Số tiền tạm ứng");
        $worksheet->setCellValue('AN2', "Chưa được hoàn trả án phí sau khi rút đơn");
        $worksheet->setCellValue('AO2', "Đã được hoàn trả tiền án phí");
        $worksheet->setCellValue('AP2', "Ngày được hoàn trả án phí");
        $worksheet->getStyle("AL1:AP1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C9C9C9');
        $worksheet->getStyle("AL1:AP1")->applyFromArray($style);
        $worksheet->getStyle("AL2:AP2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('A5A5A5');
        $worksheet->getStyle("AL2:AP2")->applyFromArray($style);


        $worksheet->mergeCells('AQ1:AQ2');
        $worksheet->setCellValue('AQ1', 'THẨM PHÁN');

        $worksheet->mergeCells('AR1:AS1');
        $worksheet->setCellValue('AR1', 'Gởi hồ sơ về nhà KH');
        $worksheet->setCellValue('AR2', "Ngày");
        $worksheet->setCellValue('AS2', "Số Bill");

        $worksheet->mergeCells('AT1:AU1');
        $worksheet->setCellValue('AT1', 'Ngày nộp đơn');
        $worksheet->setCellValue('AT2', "Ngày");
        $worksheet->setCellValue('AU2', "Số Bill");

        $worksheet->getStyle("AQ1:AU2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FF0000');
        $worksheet->getStyle("AQ1:AU2")->applyFromArray($style);


        foreach(range('A','AU') as $columnID) {
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


        $fieldToCol = array();
        // Title row
        $col = "A";
        $row = 1;
        if($model) {
            foreach ($model as $field => $prop) {
                $fieldToCol[ $field ] = $col;
                $col++;
            }
        } 
        --$col;
        $maxCol = $col;
        if($data) {
            $row = 3;
            $i = 0;
            foreach ($data as $doc) {
                // $worksheet->setCellValue('A' . $row, $i+1);
                foreach ($doc as $field => $value) {
                    if(isset($fieldToCol[ $field ], $model[$field])) {
                        $col = $fieldToCol[ $field ];
                        switch ($model[$field]["type"]) {
                            case 'array': case 'arrayPhone': case 'arrayEmail':
                                $val = implode(",", $value);
                                $worksheet->setCellValueExplicit($col . $row, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                                break;
                            
                            case 'string': case 'name': case 'phone': 
                            case 'email':
                                $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                                break;

                            case 'boolean':
                                $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_BOOLEAN);
                                break;


                            case 'int': case 'double':
                                $worksheet->setCellValueExplicit($col . $row,number_format($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                                break;

                            case 'timestamp':
                                if ($value != '') {
                                    $value = date("d/m/Y",$value);
                                }
                                $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                                break;

                            default:
                                break;
                        }
                    }
                }
                $row++;
                $i++;
            }
        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:AU".$maxCell['row'])->applyFromArray($headerStyle);
        $worksheet->getStyle("A1:AU".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $file_path = UPLOAD_PATH . "loan/export/" . 'LawsuitReport.xlsx';
        $writer->save($file_path);
        echo json_encode(array("status" => 1, "data" => $file_path));
    }

    // function saveAsExcel()
    // {
    //     try {
    //         $request    = $this->input->post();
    //         $start      = strtotime($request['startDate']);
    //         $end        = strtotime(str_replace('/', '-', $request['endDate'])) ;
                      
    //         $match = array(
    //                  '$and' => array(
    //                     array('created_at'=> array( '$gte'=> $start, '$lte'=> $end))
    //                  )               
    //              );
    //         $response = $this->crud->read($this->collection, array(),'');
    //         $data = $response['data'];
    //         $request = array (
    //           'take' => 50,
    //           'skip' => 0,
    //           "sort" => array(array("field" => "index", "dir" => "asc"))
    //         );
    //         $match = array( "collection" => $this->collection, 'sub_type' => array('$exists' => 'true') );
    //         $this->crud->select_db($this->config->item("_mongo_db"));
    //         $response = $this->crud->read("Model", $request, ["index","field", "title", "type"], $match);
    //         $response = $response['data'];
    //         foreach ($response as $key => $value) {
    //             $model[$value['field']] = $value;
    //         }
    //         // $this->excel->write($data,$model);

    //         $filename = "Lawsuit.xlsx";
    //         $file_template = "templateLawsuit.xlsx";

    //         //  Tiến hành đọc file excel
    //         $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(UPLOAD_PATH . "loan/template/" . $file_template);
    //         /**  Create a new Reader of the type that has been identified  **/
    //         $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

    //         // loads the whole workbook into a PHP object
    //         $excelWorkbook = $reader->load(UPLOAD_PATH . "loan/template/" . $file_template);

    //         // makes the sheet 'data' available as an object
    //         $worksheet = $excelWorkbook->setActiveSheetIndex(0);

    //         $fieldToCol = array();
    //         // Title row
    //         $col = "B";
    //         $row = 1;
    //         if($model) {
    //             foreach ($model as $field => $prop) {
    //                 $fieldToCol[ $field ] = $col;
    //                 $col++;
    //             }
    //         } 
    //         --$col;
    //         $maxCol = $col;
    //         if($data) {
    //             $row = 3;
    //             $i = 0;
    //             foreach ($data as $doc) {
    //                 $worksheet->setCellValue('A' . $row, $i+1);
    //                 foreach ($doc as $field => $value) {
    //                     if(isset($fieldToCol[ $field ], $model[$field]) ) {
    //                         $col = $fieldToCol[ $field ];
    //                         switch ($model[$field]["type"]) {
    //                             case 'array': case 'arrayPhone': case 'arrayEmail':
    //                                 $val = implode(",", $value);
    //                                 $worksheet->setCellValueExplicit($col . $row, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    //                                 break;
                                
    //                             case 'string': case 'name': case 'phone': 
    //                             case 'email':
    //                                 $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    //                                 break;

    //                             case 'boolean':
    //                                 $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_BOOLEAN);
    //                                 break;


    //                             case 'int': case 'double':
    //                                 $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    //                                 break;

    //                             case 'timestamp':
    //                                 if ($value != '') {
    //                                     $value = date("d/m/Y",$value);
    //                                 }
    //                                 $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    //                                 break;

    //                             default:
    //                                 break;
    //                         }
    //                     }
    //                 }
    //                 $row++;
    //                 $i++;
    //             }
    //         }
            
    //         $file_path = UPLOAD_PATH . "loan/export/" . $filename;
    //         $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelWorkbook, $inputFileType);
    //         $objWriter->save($file_path);
    //         echo json_encode(array("status" => 1, "data" => $file_path));
    //         // var_dump($response);
    //     } catch (Exception $e) {
    //         echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    //     }
    // }
}