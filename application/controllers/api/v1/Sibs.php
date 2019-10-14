<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Sibs extends WFF_Controller {

	private $collection = "Sibs";
	private $ftpFilename = '';

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
        $this->load->library("Excel");
        $this->load->library("csv");
        $this->load->library('mongo_db');
        $this->load->library('pheanstalk');
        $this->load->model('ftp_model');
		$this->collection = set_sub_collection($this->collection);
	}

    function importExcel()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') throw new Exception("Wrong method!", 1);
            $importData = array();
            $errorData = array();
            $starttime = time();
            $request = json_decode(file_get_contents('php://input'), TRUE);
            $importLog = array(
                'collection'        => 'Sibs',
                'begin_import'      => $starttime,
                'file_name'         => basename($request["filepath"]),
                'file_path'         => $request["filepath"],
                'source'            => $request['import_type'],
                'file_type'         => $request['import_file_type'],
                'total_row'         => $request['total_data'],
                'error_row'         => 0,
                'success_row'       => 0,
                'status'            => 2
            );

            $importLogResult = $this->crud->create(set_sub_collection('Import'), $importLog);

            if(empty($request["filepath"]) || empty($request["convert"]))
                throw new Exception("Error Processing Request", 1);

            $filepath = $request["filepath"];
            $convert = $request["convert"];
            $columnModel = $request['columnModel'];
            $columnStringByIndex = array();
            foreach ($convert as $key => $value) {
                $columnIndex = $this->excel->stringFromColumnIndex($key + 1);
                $columnStringByIndex[$value] = $columnIndex;
            }
            $data = $this->excel->convert($filepath, $convert, 0, 1000000, 'I', $titleRow = 0);
            $extension = $this->session->userdata("extension");
            $errorMesg = '';
            $errorCount = 0;
            if($data) {
                foreach ($data as $index => $doc) {
                    $doc['account_no'] = (string)$doc['account_no'];
                    $doc['cif'] = (string)$doc['cif'];
                    $doc['current_balance'] = (double)$doc['current_balance'];
                    $errorCell = '';
//                    if(empty($doc['dealer_code']) || empty($doc['dealer_name']) || empty($doc['location'])) {
//                        if(empty($doc['dealer_code'])) {
//                            $errorCell = $columnStringByIndex['dealer_code'] . ($index + 1);
//                            $errorCellType = $columnModel['dealer_code'];
//                            $errorMesg = "Thiếu thông tin Mã quầy";
//                        }
//                        if(empty($doc['dealer_name'])) {
//                            $errorCell = $columnStringByIndex['dealer_name'] . ($index + 1);
//                            $errorCellType = $columnModel['dealer_name'];
//                            $errorMesg = "Thiếu thông tin Tên quầy";
//                        }
//                        if(empty($doc['location'])) {
//                            $errorCell = $columnStringByIndex['location'] . ($index + 1);
//                            $errorCellType = $columnModel['location'];
//                            $errorMesg = "Thiếu thông tin khu vực";
//                        }
//                        $result = false;
//                    }
//                    else {
//                        $checkExist = $this->crud->where(array('dealer_code' => (string)$doc['dealer_code']))->getOne($this->collection, array('dealer_code', 'update_import_id'));
//                        if(!empty($checkExist)) {
//                            $updateImportId = (!empty($checkExist['update_import_id'])) ? $checkExist['update_import_id'] : array();
//                            array_push($updateImportId, $importLogResult['id']);
//                            $doc["updated_by"] =	    $extension;
//                            $doc["updated_at"] =	    time();
//                            $doc["update_import_id"] =  $updateImportId;
//                        }
//                        else {
//                            $doc["created_by"] =        $extension;
//                            $doc["created_at"] =        time();
//                            $doc["import_id"] =         $importLogResult['id'];
//                        }
//                        $result = true;
//                    }
                    $checkExist = $this->crud->where(array('cif' => (string)$doc['cif']))->getOne($this->collection, array('dealer_code', 'update_import_id'));
                    if(!empty($checkExist)) {
                        $updateImportId = (!empty($checkExist['update_import_id'])) ? $checkExist['update_import_id'] : array();
                        array_push($updateImportId, $importLogResult['id']);
                        $doc["updated_by"] =	    $extension;
                        $doc["updated_at"] =	    time();
                        $doc["update_import_id"] =  $updateImportId;
                    }
                    else {
                        $doc["created_by"] =        $extension;
                        $doc["created_at"] =        time();
                        $doc["import_id"] =         $importLogResult['id'];
                    }
                    $result = true;
                    if(!empty($result)) {
                        $doc["result"] = 'success';
                        array_push($importData, $doc);
                    }
                    else {
                        $doc["error_cell"] = $errorCell;
//                        $doc["type"] = $errorCellType;
//                        $doc["error_mesg"] = $errorMesg;
                        $doc["result"] = 'error';
                        array_push($errorData, $doc);
                        $errorCount++;
                    }
                }
            }

            $endtime = time();

            if($errorCount > 0) {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 0, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Sibs_import_result'), $errorData);
            }
            else {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 1, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Sibs_import_result'), $importData);
                $this->mongo_db->batch_insert(set_sub_collection('Sibs'), $importData);
            }
            echo json_encode(array("status" => ($errorCount === 0) ? 1 : 0));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function importHistoryRead() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read(set_sub_collection('Import'), $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function importHistoryById($id) {
        try {
            $response = $this->crud->where_id($id)->getOne(set_sub_collection('Import'));
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function importHistoryDetail() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read(set_sub_collection('Sibs_import_result'), $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function listFileFTP() {
        try {
            echo json_encode(array('data' => array(array('filepath' => '/var/www/html/worldfone4xs_ibm/upload/excel/ZACCF_full.csv', 'filename' => 'ZACCF_full.csv')), 'total' => 1));
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function downloadFileFromFTP() {
        try {
            $this->ftp_model->downloadFileFromFTP(UPLOAD_PATH . "excel/", '');
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function testImport() {
        try {
//            if($_SERVER['REQUEST_METHOD'] !== 'PATCH') throw new Exception("Wrong method!", 1);
            $importData = array();
            $errorData = array();
            $starttime = time();
            $request = json_decode(file_get_contents('php://input'), TRUE);
//            print_r($request);
//            exit();
//            $importLog = array(
//                'collection'        => 'Sibs',
//                'begin_import'      => $starttime,
//                'file_name'         => basename($request["filepath"]),
//                'file_path'         => $request["filepath"],
//                'source'            => $request['import_type'],
//                'file_type'         => $request['import_file_type'],
//                'total_row'         => $request['total_data'],
//                'error_row'         => 0,
//                'success_row'       => 0,
//                'status'            => 2
//            );
//
//            $importLogResult = $this->crud->create(set_sub_collection('Import'), $importLog);

//            if(empty($request["filepath"]) || empty($request["convert"]))
//                throw new Exception("Error Processing Request", 1);

            $filepath = $request["filepath"];
//            $filepath = $this->input->get("filepath");
//            $convert = $request["convert"];
//            $columnModel = $request['columnModel'];
//            $data = $this->excel->convert($filepath, $convert, 0, 1000000, 'I', $titleRow = 0);
            $data1 = $this->getData($filepath, 0, 1000000, 'F', 'I');
//            $data2 = $this->getData($filepath, 0, 1000000, 'DS', 'DT');
//            $data3 = $this->getData($filepath, 0, 1000000, 'DM', 'DN');
//            $dataResult = array();
//            for($i = 0; $i < count($data1); $i++) {
//                $tempResult = array_merge($data1[$i], $data2[$i], $data3[$i]);
//                array_push($dataResult, $tempResult);
//            }
            print_r($data1);
            exit();
            $columnStringByIndex = array();
            foreach ($convert as $key => $value) {
                $columnIndex = $this->excel->stringFromColumnIndex($key + 1);
                $columnStringByIndex[$value] = $columnIndex;
            }
//            $data = $this->excel->convert($filepath, $convert, 0, 1000000, 'I', $titleRow = 0);
            $extension = $this->session->userdata("extension");
            $errorMesg = '';
            $errorCount = 0;
            if($data) {
                foreach ($data as $index => $doc) {
                    $doc['account_no'] = (string)$doc['account_no'];
                    $doc['cif'] = (string)$doc['cif'];
                    $doc['current_balance'] = (double)$doc['current_balance'];
                    $errorCell = '';
                    $checkExist = $this->crud->where(array('cif' => (string)$doc['cif']))->getOne($this->collection, array('dealer_code', 'update_import_id'));
                    if(!empty($checkExist)) {
                        $updateImportId = (!empty($checkExist['update_import_id'])) ? $checkExist['update_import_id'] : array();
                        array_push($updateImportId, $importLogResult['id']);
                        $doc["updated_by"] =	    $extension;
                        $doc["updated_at"] =	    time();
                        $doc["update_import_id"] =  $updateImportId;
                    }
                    else {
                        $doc["created_by"] =        $extension;
                        $doc["created_at"] =        time();
                        $doc["import_id"] =         $importLogResult['id'];
                    }
                    $result = true;
                    if(!empty($result)) {
                        $doc["result"] = 'success';
                        array_push($importData, $doc);
                    }
                    else {
                        $doc["error_cell"] = $errorCell;
//                        $doc["type"] = $errorCellType;
//                        $doc["error_mesg"] = $errorMesg;
                        $doc["result"] = 'error';
                        array_push($errorData, $doc);
                        $errorCount++;
                    }
                }
            }

            $endtime = time();

            if($errorCount > 0) {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 0, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Sibs_import_result'), $errorData);
            }
            else {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 1, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Sibs_import_result'), $importData);
                $this->mongo_db->batch_insert(set_sub_collection('Sibs'), $importData);
            }
            echo json_encode(array("status" => ($errorCount === 0) ? 1 : 0));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getData($file_path, $from_row = 0, $to_row = 1000, $fromColumn = 'A', $to_column = null)
    {
        if(!isset($this->reader)) {
            /**  Identify the type of $file_path  **/
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
            /**  Create a new Reader of the type that has been identified  **/
            $this->inputFileType = $inputFileType;
            $this->reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        }
        if($this->inputFileType == "csv") {
            $collection = $file_path;
            $sheetData = $this->WFF->mongo_db->limit($to_row)->select([], ["_id"])->get($collection);
        } else {
            /**  Advise the Reader that we only want to load cell data  **/
            $this->reader->setReadDataOnly(true);
            // Filter
            $filter = new MyReadFilter();
            $filter->setRows($from_row, $to_row);
            if($to_column) {
                $filter->setColumns("A", $to_column);
            }
//			else {
//                $filter->setColumns("A", $limit_column);
//            }

            $this->reader->setReadFilter( $filter );

            /**  Load $file_path to a Spreadsheet Object  **/
            $spreadsheet = $this->reader->load($file_path);

            $worksheet = $spreadsheet->getActiveSheet();

            $maxCell = $worksheet->getHighestRowAndColumn();

            $sheetData = $worksheet->rangeToArray($fromColumn . '1:' . $maxCell['column'] . $maxCell['row']);

            // $sheetData = $worksheet->rangeToArray();
        }
        return $sheetData;
    }

//    function getFileFromFTP() {
//        try {
//            $connResult = $this->connectToFTP->connectToFTP();
//            if($connResult['status'] == 1) {
//                $connId = $connResult['data'];
//                $listFTPResult = $this->connectToFTP->listFileInFTP($connId, '.');
//                if($listFTPResult['status'] == 1) {
//
//                }
//                else echo json_encode(array("status" => 0, "message" => $listFTPResult['message']));
//            }
//            else {
//                echo json_encode(array("status" => 0, "message" => $connResult['message']));
//            }
//        }
//        catch (Exception $e) {
//            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
//        }
//    }

//    function readChunkFile($file_path, $from_row = 0, $to_row = 300000, $limit_column = null) {
//        $objReader = PHPExcel_IOFactory::createReader('xlsx');
//        $chunkSize = 100;
//        $chunkFilter = new chunkReadFilter();
//        $objReader->setReadFilter($chunkFilter);
//        for ($startRow = 2; $startRow <= 240; $startRow += $chunkSize) {
//            echo 'Loading WorkSheet using configurable filter for headings row 1 and for rows ',$startRow,' to ',($startRow+$chunkSize-1),'<br />';
//            /**  Tell the Read Filter, the limits on which rows we want to read this iteration  **/
//            $chunkFilter->setRows($startRow,$chunkSize);
//            /**  Load only the rows that match our filter from $inputFileName to a PHPExcel Object  **/
//            $objPHPExcel = $objReader->load('xlsx');
//            $this->reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('xlsx');
//            //    Do some processing here
////            $this->reader->setReadFilter( $filter );
//
//            /**  Load $file_path to a Spreadsheet Object  **/
//            $spreadsheet = $this->reader->load($file_path);
//
//            $worksheet = $spreadsheet->getActiveSheet();
////            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
//            $sheetData = $worksheet->rangeToArray('F1:' . $maxCell['column'] . $maxCell['row']);
//            var_dump($sheetData);
//            echo '<br /><br />';
//        }
//    }

    function getCSVData() {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
//        $array = array_map("str_getcsv", file("/var/www/html/worldfone4xs_ibm/upload/excel/ZACCF full.csv"));
        $file = fopen("/var/www/html/worldfone4xs_ibm/upload/excel/ZACCF full.csv","r");
        print_r(fgetcsv($file));
        fclose($file);
//        print_r($array);
    }

    function callPYFromPHP() {
        $importLog = array(
            'collection'        => 'Sibs',
            'begin_import'      => $starttime,
            'file_name'         => basename($request["filepath"]),
            'file_path'         => $request["filepath"],
            'source'            => $request['import_type'],
            'file_type'         => $request['import_file_type'],
            'total_row'         => $request['total_data'],
            'error_row'         => 0,
            'success_row'       => 0,
            'status'            => 2
        );

        $importLogResult = $this->crud->create(set_sub_collection('Import'), $importLog);

        $command = escapeshellcmd("python3.6 /var/www/html/python/readfrommongod.py " . $importLogResult['id'] . " " . $this->session->userdata("extension"));
        $output = shell_exec($command);
        print($output);
    }

}