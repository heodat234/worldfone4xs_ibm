<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Dealer extends WFF_Controller {

	private $collection = "Dealer";
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
                'collection'        => 'Dealer',
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
                $this->mongo_db->delete_all($this->collection);
                foreach ($data as $index => $doc) {
                    $doc['dealer_code'] = (string)$doc['dealer_code'];
                    $errorCell = '';
                    if(array_filter($doc)) {
                        if(empty($doc['dealer_code']) || empty($doc['dealer_name']) || empty($doc['location'])) {
                            if(empty($doc['dealer_code'])) {
                                $errorCell = $columnStringByIndex['dealer_code'] . ($index + 1);
                                $errorCellType = $columnModel['dealer_code'];
                                $errorMesg = "Thiếu thông tin Mã quầy";
                            }
                            if(empty($doc['dealer_name'])) {
                                $errorCell = $columnStringByIndex['dealer_name'] . ($index + 1);
                                $errorCellType = $columnModel['dealer_name'];
                                $errorMesg = "Thiếu thông tin Tên quầy";
                            }
                            if(empty($doc['location'])) {
                                $errorCell = $columnStringByIndex['location'] . ($index + 1);
                                $errorCellType = $columnModel['location'];
                                $errorMesg = "Thiếu thông tin khu vực";
                            }
                            $result = false;
                        }
                        else {
                            $checkExist = $this->crud->where(array('dealer_code' => (string)$doc['dealer_code']))->getOne($this->collection, array('dealer_code', 'update_import_id'));
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
                        }
                        $doc["import_id"] = $importLogResult['id'];
                        if(!empty($result)) {
                            $doc["result"] = 'success';
                            array_push($importData, $doc);
                        }
                        else {
                            $doc["error_cell"] = $errorCell;
                            $doc["type"] = $errorCellType;
                            $doc["error_mesg"] = $errorMesg;
                            $doc["result"] = 'error';
                            array_push($errorData, $doc);
                            $errorCount++;
                        }
                    }
                    else continue;
                }
            }

            $endtime = time();

            if($errorCount > 0) {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 0, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Dealer_import_result'), $errorData);
            }
            else {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 1, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Dealer_import_result'), $importData);
                $this->mongo_db->batch_insert(set_sub_collection('Dealer'), $importData);
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
            $response = $this->crud->read(set_sub_collection('Dealer_import_result'), $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function listFileFTP() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $file_path = $request['ftp_filepath'];
            $file_name = basename($file_path);
            echo json_encode(array('data' => array(array('filepath' => $file_path, 'filename' => $file_name)), 'total' => 1));
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function downloadFileFromFTP() {
        try {
            $ftpInfo = $this->crud->where(array('collection' => $this->$collection))->getOne(set_sub_collection('ftp_config'));
            $result = $this->ftp_model->downloadFileFromFTP($ftpInfo['locallink'] . $ftpInfo['filename'], $ftpInfo['filename'], FTP_BINARY);
            echo json_encode(array("status" => 1, "message" => '', 'data' => $result['data']));
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
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
}