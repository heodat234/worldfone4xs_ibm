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
        $start      =  strtotime(str_replace('/', '-', $request['start'])) ;
        $end        = strtotime(str_replace('/', '-', $request['end'])) ;
        $match = array(
                 '$and' => array(
                    array('created_date'=> array( '$gte'=> $start, '$lte'=> $end))
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

        // print_r($model);exit;
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

        $worksheet->setCellValue('A1', 'STT (No.)');

        $worksheet->setCellValue('B1', 'NGÀY LÀM ĐƠN');

        $worksheet->setCellValue('C1', 'SỐ HỢP ĐỒNG (AC No.)');

        $worksheet->setCellValue('D1', "TÊN KH (Cust's Name)");
        $worksheet->getStyle("A1:D1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("A1:D1")->applyFromArray($style);

        $worksheet->setCellValue('E1', 'QUẬN/ HUYỆN (District)');
        $worksheet->setCellValue('F1', "TỈNH/ THÀNH (Province/ City)");
        $worksheet->getStyle("E1:F1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C6E0B4');
        $worksheet->getStyle("E1:F1")->applyFromArray($style);

        $worksheet->setCellValue('G1', "NGƯỜI ĐƯỢC ỦY QUYỀN");
        $worksheet->getStyle("G1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("G1")->applyFromArray($style);

        // $worksheet->mergeCells('H1:K1');
        // $worksheet->setCellValue('H1', 'HỢP ĐỒNG (Contract)');
        $worksheet->setCellValue('H1', "NỢ GỐC (Principal)");
        $worksheet->setCellValue('I1', "LÃI (Interest)");
        $worksheet->setCellValue('J1', "TỔNG CỘNG");
        $worksheet->setCellValue('K1', "Tiền hàng tháng (Monthly)");
        $worksheet->getStyle("H1:K1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFC000');
        $worksheet->getStyle("H1:K1")->applyFromArray($style);

        // $worksheet->mergeCells('L1:O1');
        // $worksheet->setCellValue('L1', 'SỐ TIỀN ĐÃ THANH TOÁN (Paid amount)');
        $worksheet->setCellValue('L1', " NỢ GỐC
(Principal)
(Paid amount) ");
        $worksheet->setCellValue('M1', " LÃI
(Interest)
(Paid amount) ");
        $worksheet->setCellValue('N1', " PHẠT
(Late charge)
(Paid amount) ");
        $worksheet->setCellValue('O1', " TỔNG CỘNG
(Total Paid amount) ");
        $worksheet->getStyle("L1:O1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C9C9C9');
        $worksheet->getStyle("L1:O1")->applyFromArray($style);

        $worksheet->setCellValue('P1', "NỢ GỐC (Principal) (O/S balance when lawsuit)");
        $worksheet->setCellValue('Q1', "LÃI
(Interest)
(O/S balance when lawsuit)");
        $worksheet->setCellValue('R1', " PHẠT
(Late charge)
(O/S balance when lawsuit) ");
        $worksheet->setCellValue('S1', " PHÍ TẤT TOÁN (Prepayment fee)
(O/S balance when lawsuit) ");
        $worksheet->setCellValue('T1', " TỔNG CỘNG
(O/S balance when lawsuit) ");
        $worksheet->getStyle("P1:T1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFE699');
        $worksheet->getStyle("P1:T1")->applyFromArray($style);

        $worksheet->setCellValue('U1'," TÊNCỬA HÀNG
(Dealer Name) ");
        $worksheet->setCellValue('V1', " ĐỊA CHỈ KHÁCH HÀNG
(Customer Address) ");
        $worksheet->getStyle("U1:V1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');
        $worksheet->getStyle("U1:V1")->applyFromArray($style);

        $worksheet->setCellValue('W1', 'TẠM ỨNG ÁN PHÍ (Dự tính)');

        $worksheet->setCellValue('X1', 'PHƯƠNG THỨC NỘP');

        $worksheet->setCellValue('Y1', 'Ngày gởi FC');

        // $worksheet->setCellValue('Z1', "NGÀY NỘP ĐƠN (Submiting date)");

        $worksheet->setCellValue('Z1', 'NGÀY NỘP TƯAP (Date of Advance pay)');

        $worksheet->setCellValue('AA1', 'NHẬN THÔNG BÁO THỤ LÝ');

        $worksheet->setCellValue('AB1', 'NGÀY HÒA GIẢI LẦN 1 (1st conciliation)');

        $worksheet->setCellValue('AC1', "NGÀY HÒA GIẢI LẦN 2 (2nd conciliation)");

        $worksheet->setCellValue('AD1', 'NGÀY HÒA GIẢI LẦN 3 (3nd conciliation)');

        $worksheet->setCellValue('AE1', "NGÀY XÉT XỬ SƠ THẨM
(First instance trial date)");

        $worksheet->setCellValue('AF1', 'KHÁNG CÁO');

        $worksheet->setCellValue('AG1', "NGÀY XÉT XỬ PHÚC THẨM
(Trial of Appeal date, if any)");

        $worksheet->setCellValue('AH1', 'Theo dõi');

        $worksheet->getStyle("W1:AH1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("W1:AH1")->applyFromArray($style);

        $worksheet->setCellValue('AI1', "Phương hướng giải quyết");
        $worksheet->getStyle("AI1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('A5A5A5');
        $worksheet->getStyle("AI1")->applyFromArray($style);

        $worksheet->setCellValue('AJ1', "TÌNH TRẠNG KHỞI KIỆN
(Lawsuit status)");
        $worksheet->getStyle("AJ1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00B050');
        $worksheet->getStyle("AJ1")->applyFromArray($style);

        $worksheet->setCellValue('AK1', "Ngày tạm ứng");
        $worksheet->setCellValue('AL1', "Số tiền tạm ứng");
        $worksheet->setCellValue('AM1', "Chưa được hoàn 
trả án phí sau khi rút đơn");
        $worksheet->setCellValue('AN1', "Đã được hoàn trả tiền án phí");
        $worksheet->setCellValue('AO1', "Ngày được hoàn trả án phí");
        $worksheet->getStyle("AK1:AO1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C9C9C9');
        $worksheet->getStyle("AK1:AK1")->applyFromArray($style);
        // $worksheet->getStyle("AL2:AP2")->getFill()
        //     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //     ->getStartColor()->setRGB('A5A5A5');
        // $worksheet->getStyle("AL2:AP2")->applyFromArray($style);


        $worksheet->setCellValue('AP1', 'THẨM PHÁN');

        $worksheet->setCellValue('AQ1', "Ngày Gởi hồ sơ về nhà KH");
        $worksheet->setCellValue('AR1', "Số Bill Gởi hồ sơ về nhà KH");

        $worksheet->setCellValue('AS1', "NGÀY NỘP ĐƠN
(Submiting date)");
        $worksheet->setCellValue('AT1', "Số Bill Ngày nộp đơn");

        $worksheet->getStyle("AP1:AT1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FF0000');
        $worksheet->getStyle("AP1:AT1")->applyFromArray($style);


        foreach(range('A','AT') as $columnID) {
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
            $row = 2;
            $i = 1;
            foreach ($data as $doc) {
                $nogoc_hopdong = $lai_hopdong = $nogoc_sotiendathanhtoan = $lai_sotiendathanhtoan = $phat_sotiendathanhtoan = $nogoc_dunokhoikien = $lai_dunokhoikien = $phat_dunokhoikien = $phitattoan_dunokhoikien = $tongcong_dunokhoikien = 0;
                foreach ($doc as $field => $value) {
                    if(isset($fieldToCol[ $field ], $model[$field])) {
                        $col = $fieldToCol[ $field ];
                        if ($field == 'nogoc_hopdong') {
                            $nogoc_hopdong = $value;
                        }
                        if ($field == 'lai_hopdong') {
                            $lai_hopdong = $value;
                        }
                        if ($field == 'tongcong_hopdong') {
                            $value = $nogoc_hopdong + $lai_hopdong;
                        }

                        if ($field == 'nogoc_sotiendathanhtoan') {
                            $nogoc_sotiendathanhtoan = $value;
                        }
                        if ($field == 'lai_sotiendathanhtoan') {
                            $lai_sotiendathanhtoan = $value;
                        }
                        if ($field == 'phat_sotiendathanhtoan') {
                            $phat_sotiendathanhtoan = $value;
                        }
                        if ($field == 'tongcong_sotiendathanhtoan') {
                            $value = $nogoc_sotiendathanhtoan + $lai_sotiendathanhtoan + $phat_sotiendathanhtoan;
                        }

                        if ($field == 'nogoc_dunokhoikien') {
                            $nogoc_dunokhoikien = $value;
                        }
                        if ($field == 'lai_dunokhoikien') {
                            $lai_dunokhoikien = $value;
                        }
                        if ($field == 'phat_dunokhoikien') {
                            $phat_dunokhoikien = $value;
                        }
                        if ($field == 'phitattoan_dunokhoikien') {
                            $phitattoan_dunokhoikien = $value;
                        }
                        if ($field == 'tongcong_dunokhoikien') {
                            $value = $nogoc_dunokhoikien + $lai_dunokhoikien + $phat_dunokhoikien + $phitattoan_dunokhoikien;
                            $tongcong_dunokhoikien = $value;
                        }

                        if ($field == 'tamung_anphi') {
                            $value = $tongcong_dunokhoikien * 0.025;
                        }
                        if ($field == 'sobill_nopdon') {
                            $value = (int)$value;
                        }
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
                                $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0');
                                break;

                            case 'timestamp':
                                if ($value != '') {
                                    $value = date("d/m/Y",$value);
                                }
                                $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                                $worksheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                                break;

                            default:
                                break;
                        }
                        if ($field == 'stt') {
                           $worksheet->setCellValue('A' . $row, $i);
                        }
                    }
                }
                $row++;
                $i++;
            }
        }

        $maxCell = $worksheet->getHighestRowAndColumn();
        $worksheet->getStyle("A1:AT".$maxCell['row'])->applyFromArray($headerStyle);
        $worksheet->getStyle("A1:AT".$maxCell['row'])->getBorders()
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