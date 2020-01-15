<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Thu_hoi_xe_api_report extends WFF_Controller {

    private $collection = "Thu_hoi_xe_report";
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
            $request['sort'] = array(array("field" => "No", "dir" => "asc"));
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function exportExcel() {
        $request    = $this->input->post();
        $start      = strtotime(str_replace('/', '-', $request['start'])) ;
        $end        = strtotime(str_replace('/', '-', $request['end'])) ;  

        $match = array(
                 '$and' => array(
                    array('createdAt'=> array( '$gte'=> $start, '$lte'=> $end))
                 )               
             );
        $data = $this->crud->where($match)->order_by(array('No' => 'asc'))->get($this->collection);

        $request = array (
          'take' => 50,
          'skip' => 0,
          "sort" => array(array("field" => "index", "dir" => "asc"))
        );
        $match = array( "collection" => $this->collection, 'sub_type' => array('$exists' => 'true') );
        $this->crud->select_db($this->config->item("_mongo_db"));
        $response = $this->crud->read("Model", $request, ["index","field", "title", "type"], $match);
        // print_r($data); exit;
        $response = $response['data'];
        foreach ($response as $key => $value) {
            $model[$value['field']] = $value;
        }

        // print_r($data);exit;
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator("South Telecom")
        ->setLastModifiedBy("Son Vu")
        ->setTitle("Thu Hoi Xe Report")
        ->setSubject("Thu Hoi Xe Report")
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
        $fontStyle = [
            'font' => [
                'size' => 16
            ]
        ];
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->getParent()->getDefaultStyle()->applyFromArray($style);
        $worksheet->getDefaultColumnDimension()->setWidth(30);

        $worksheet->mergeCells('A1:AC1');
        $worksheet->setCellValue('A1', "DANH SÁCH XE THU HỒI ( 引き揚げバイクの管理簿 )");
        $worksheet->getStyle("A1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $worksheet->getStyle("A1")->applyFromArray($fontStyle);
     

        $worksheet->setCellValue('A2', 'STT ( 管理番号 )');

        $worksheet->setCellValue('B2', 'Ngày thu hồi ( 引き揚げ日付 )');

        $worksheet->setCellValue('C2', 'Số hợp đồng ( 契約番号 )');

        $worksheet->setCellValue('D2', "Tên khách hàng ( お客様署名 )");


        $worksheet->setCellValue('E2', 'Sản phẩm ( product )');
        $worksheet->setCellValue('F2', "Nhãn hiệu ( バイク モデル )");
        $worksheet->setCellValue('G2', "Biển số ( バイク番号 )");
        
        $worksheet->setCellValue('H2', "Tình trạng ( 状況 )");

        $worksheet->setCellValue('I2', 'Giá bán ( 転売金額 )');
        $worksheet->setCellValue('J2', "Người thu hồi");
        $worksheet->setCellValue('K2', "Group");
        $worksheet->setCellValue('L2', "Ngày gửi thư thông báo hoàn tất thu hồi tài sản (nếu có)");
        $worksheet->setCellValue('M2', "Ngày gửi thư thông báo định giá tài sản (nếu có)");
    
        $worksheet->setCellValue('N2', 'Tình trạng Sold/Paid off/Not sale/returned');
        $worksheet->setCellValue('O2', "Ngày gửi thư thông báo hoàn tất xử lý & bán lại tài sản thu hồ");
        $worksheet->setCellValue('P2', " Hình thức xử lý tài sản ");
        $worksheet->setCellValue('Q2', "Ngày gửi thư thông báo xử lý tài sản thông qua đấu giá");
        $worksheet->setCellValue('R2', "Ngày đấu giá");
              
        $worksheet->setCellValue('S2', 'Chi phí thẩm định giá');
        $worksheet->setCellValue('T2', "Chi phí đấu giá");
        $worksheet->setCellValue('U2', "Chi phí khác (gửi xe,…)");
        $worksheet->setCellValue('V2', "Tổng số tiền còn lại chuyển về TK Khách hàng");
        $worksheet->setCellValue('W2', "Ngày tiền về TK khách hàng đợt 1");
        $worksheet->setCellValue('X2', "Ngày trừ tiền để thanh toán quá hạn sau khi xử lý tài sản");
    
        $worksheet->setCellValue('Y2', 'Ngày tiền về TK khách hàng đợt cuối (nếu có)');
        $worksheet->setCellValue('Z2', "Ngày trừ tiền để giảm dư nợ gốc sau khi xử lý tài sản");
        $worksheet->setCellValue('AA2', "Ngày Yêu cầu IT xóa các bills và giữ lại 1 kỳ bill cuối");

        $worksheet->setCellValue('AB2', 'Số tiền kỳ bill cuối cùng');

       
        $worksheet->setCellValue('AC2', 'Ngày đến hạn của kỳ bills cuối cùng');

        foreach(range('A','AC') as $columnID) {
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
        $row = 2;
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
                        if($model[$field]['field']=='ngay_thu'){
                            if ($value != '') {
                                $value = date("d/m/Y",strtotime($value));
                            }
                            $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                        switch ($model[$field]["type"]) {
                           
                            case 'string': case 'name':
                            
                                $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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
        $worksheet->getStyle("A1:AC".$maxCell['row'])->applyFromArray($headerStyle);
        $worksheet->getStyle("A1:AC".$maxCell['row'])->getBorders()
        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $file_path = UPLOAD_PATH . "loan/export/" . 'ThuHoiXeReport.xlsx';
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