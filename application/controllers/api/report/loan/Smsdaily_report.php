<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Smsdaily_report extends WFF_Controller {

    private $collection = "LNJC05";
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
            // print_r($request);
            $response = $this->crud->read($this->collection, $request);

            $data = array();
            print_r($response);
            foreach ($response['data'] as &$value) {
               if ( ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] > 40000) || ((int)$value['overdue_amount_this_month'] - (int)$value['advance_balance'] < 40000 && $value['installment_type'] == 'n') ){
                  array_push($data, $value);
               }
            }
            echo json_encode(array('data'=> $data, 'total' => count($data)));

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function saveAsExcel()
    {
        try {
            $request    = $this->input->post();
            $start      = strtotime($request['startDate']);
            $end        = strtotime(str_replace('/', '-', $request['endDate'])) ;

            $match = array(
                     '$and' => array(
                        array('created_at'=> array( '$gte'=> $start, '$lte'=> $end))
                     )
                 );
            $response = $this->crud->read($this->collection, array(),'',$match);
            $data = $response['data'];

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
            // $this->excel->write($data,$model);

            $filename = "export.xlsx";
            $file_template = "templateLawsuit.xlsx";

            //  Tiến hành đọc file excel
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(UPLOAD_PATH . "excel/" . $file_template);
            /**  Create a new Reader of the type that has been identified  **/
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            // loads the whole workbook into a PHP object
            $excelWorkbook = $reader->load(UPLOAD_PATH . "excel/" . $file_template);

            // makes the sheet 'data' available as an object
            $worksheet = $excelWorkbook->setActiveSheetIndex(0);

            $fieldToCol = array();
            // Title row
            $col = "B";
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
                    $worksheet->setCellValue('A' . $row, $i+1);
                    foreach ($doc as $field => $value) {
                        if(isset($fieldToCol[ $field ], $model[$field]) ) {
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
                                    $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
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

            $file_path = UPLOAD_PATH . "excel/" . $filename;
            $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelWorkbook, $inputFileType);
            $objWriter->save($file_path);
            echo json_encode(array("status" => 1, "data" => $file_path));
            // var_dump($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}