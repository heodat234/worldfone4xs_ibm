<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Appointment extends WFF_Controller {

	private $collection = "Appointment";
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
        $this->load->model('user_model');
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
                'collection'        => 'Appointment',
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
            $data = $this->excel->convert($filepath, $convert, 0, 1000000, 'P', $titleRow = 0);
            $extension = $this->session->userdata("extension");
            $errorMesg = '';
            $errorCount = 0;
            $isUpdate = false;
            if($data) {
                foreach ($data as $index => $doc) {
                    if(array_filter($doc)) {
                        $doc['tl_code'] = (string)$doc['tl_code'];
                        $doc['cmnd'] = (string)$doc['cmnd'];
                        $doc['cif'] = (string)$doc['cif'];
                        $doc['cus_phone'] = (string)$doc['cus_phone'];
                        $doc['loan_amount'] = (double)$doc['loan_amount'];
                        $doc['sc_phone'] = (string)$doc['sc_phone'];
                        $doc['dc_code'] = (string)$doc['dc_code'];
                        $doc['is_code'] = (string)$doc['is_code'];
                        $errorCell = '';
                        $errorCellType = '';
                        $result = true;
                        $checkExist = $this->crud->where(array('cif' => (string)$doc['cif']))->get($this->collection);
                        if(!empty($checkExist)) {
                            $updateImportId = array();
                            array_push($updateImportId, $importLogResult['id']);
                            $doc["updated_by"]       = $extension;
                            $doc["updated_at"]       = time();
                            $doc["update_import_id"] = $importLogResult['id'];
                            $doc["isUpdate"]         = true;
                        }
                        else {
                            $doc["created_by"]       = $extension;
                            $doc["created_at"]       = time();
                            $doc["import_id"]        = $importLogResult['id'];
                            $doc["isUpdate"]         = false;
                        }
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
                $this->crud->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 0, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Appointment_import_result'), $errorData);
            }
            else {
                $this->mongo_db->where_id($importLogResult['id'])->set(array('complete_import' => $endtime, 'status' => 1, 'id' => $importLogResult['id']))->update(set_sub_collection('Import'));
                $this->mongo_db->batch_insert(set_sub_collection('Appointment_import_result'), $importData);
                foreach ($importData as $key => $value) {
                    if($value['isUpdate']) {
                        $this->mongo_db->where(array('cif' => $value['cif']))->update($this->collection, $value);
                    }
                    else {
                        $this->mongo_db->insert($this->collection, $value);
                    }
                }

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
            $response = $this->crud->read(set_sub_collection('Appointment_import_result'), $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function listFileFTP() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            if(!empty($request)) {
                $file_path = $request['ftp_filepath'];
                $file_name = basename($file_path);
            }
            else {
                $file_path = '';
                $file_name = '';
            }
            echo json_encode(array('data' => array(array('filepath' => $file_path, 'filename' => $file_name)), 'total' => 1));
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
}