<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Sc extends WFF_Controller {

    private $collection = "Sc";
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
                'collection'        => 'Sc',
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
                    if(array_filter($doc)) {
                        $doc['sc_code'] = (string)$doc['sc_code'];
                        $errorCell = '';
                        if(empty($doc['sc_code']) || empty($doc['sc_name'])) {
                            if(empty($doc['sc_code'])) {
                                $errorCell = $columnStringByIndex['sc_code'] . ($index + 1);
                                $errorCellType = $columnModel['sc_code'];
                                $errorMesg = "Thiếu thông tin Mã SC";
                            }
                            if(empty($doc['sc_name'])) {
                                $errorCell = $columnStringByIndex['sc_name'] . ($index + 1);
                                $errorCellType = $columnModel['sc_name'];
                                $errorMesg = "Thiếu thông tin Tên SC";
                            }
                            $result = false;
                        }
                        else {
                            $doc["name"] =        $doc['sc_name'];
                            $checkExist = $this->crud->where(array('sc_code' => (string)$doc['sc_code']))->getOne($this->collection, array('sc_code', 'update_import_id'));
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
                $this->mongo_db->batch_insert(set_sub_collection('Sc_import_result'), $errorData);
            }
            else {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 1, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Sc_import_result'), $importData);
                $this->mongo_db->batch_insert($this->collection, $importData);
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
            $response = $this->crud->read(set_sub_collection('Sc_import_result'), $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function listFileFTP() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $file_path = '/data/upload_file/';
            if (in_array(ENVIRONMENT, array('UAT', 'development'))) {
                $today = strtotime("2019-11-20 00:00:00");
            }
            else {
                $today = strtotime('today 00:00:00');
            }
            $todayFile = date('Ymd', $today);
            $file_path = $file_path . $todayFile . '/';
            $file_name = 'DANH SACH SC.csv';
            $existFile = file_exists($file_path . $file_name);
            if($existFile) {
                echo json_encode(array('data' => array(array('filepath' => $file_path . $file_name, 'filename' => $file_name)), 'total' => 1));
            }
            else {
                echo json_encode(array('data' => array(array('filepath' => $file_path . $file_name, 'filename' => '')), 'total' => 0));
            }
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function downloadFileFromFTP() {
        try {
            $result = array();
            $ftpInfo = $this->crud->where(array('collection' => $this->collection))->getOne(set_sub_collection('ftp_config'));
            if(!empty($ftpInfo)) {
                if (!file_exists(FCPATH . $ftpInfo['locallink'])) {
                    mkdir(FCPATH . $ftpInfo['locallink'], 0777, true);
                }
                $result = $this->ftp_model->downloadFileFromFTP(FCPATH . $ftpInfo['locallink'] . $ftpInfo['filename'], $ftpInfo['ftplink'] . $ftpInfo['filename'], FTP_BINARY);
            }
            else {
                $result['data'] = null;
            }
            echo json_encode(array("status" => 1, "message" => '', 'data' => $result['data']));
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function update_import_log($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data['complete_import'] = time();
            $data["updated_by"]  =   $this->session->userdata("extension");
            $data['updated_at'] = time();
            $result = $this->crud->where_id($id)->update(set_sub_collection("Import"), array('$set' => $data));
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}