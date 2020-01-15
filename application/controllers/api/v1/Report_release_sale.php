<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Report_release_sale extends WFF_Controller {

	private $collection = "Report_release_sale";
	private $ftpFilename = '';

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
        $this->load->library("Excel");
        $this->load->library('mongo_db');
        $this->load->model('ftp_model');
		$this->collection = set_sub_collection($this->collection);
	}

    function importFTP()
    {
        try {
            $request = json_decode(file_get_contents('php://input'), TRUE);
            $checkScriptRunning = $this->mongo_db->where(array('collection' => $this->collection, 'status' => 2))->get(set_sub_collection('Import'));
            if(!empty($checkScriptRunning)) {
                echo json_encode(array("status" => 0, "message" => '@A file is importing. Please try again later@'));
            }
            else {
                $pythonCron = FCPATH . 'cronjob/python/Loan/importReportReleaseSale.py';
                
                if (in_array(ENVIRONMENT, array('UAT', 'development'))) {
                    $command = escapeshellcmd('/usr/bin/python3.6 ' . $pythonCron) . ' > /dev/null &';
                }
                else {
                    $command = escapeshellcmd("/usr/local/bin/python3.6 " . $pythonCron) . ' > /dev/null &';
                }
                $output = shell_exec($command);
                print_r($output);
                echo json_encode(array('status' => 2, "message" => "@Importing... Please check import history for more detail@"));
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function importExcel()
    {
        try {
            $request = json_decode(file_get_contents('php://input'), TRUE);
            $checkScriptRunning = $this->mongo_db->where(array('collection' => $this->collection, 'status' => 2))->get(set_sub_collection('Import'));
            if(!empty($checkScriptRunning)) {
                echo json_encode(array("status" => 0, "message" => '@A file is importing. Please try again later@'));
            }
            else {
                $pythonCron = FCPATH . 'cronjob/python/Loan/importReportReleaseSale.py';
                $importLog = array(
                    'collection'    => $this->collection,
                    'begin_import'  => time(),
                    'file_name'     => basename($request["filepath"]),
                    'file_path'     => $request['filepath'],
                    'source'        => $request['import_type'],
                    'file_type'     => $request['import_file_type'],
                    'status'        => 2,
                    'created_by'    => $this->session->userdata("extension"),
                    'python_cron'   => $pythonCron
                );
                $importLogId = $this->crud->create(set_sub_collection('Import'), $importLog);
                
                if (in_array(ENVIRONMENT, array('UAT', 'development'))) {
                    $command = escapeshellcmd('/usr/bin/python3.6 ' . $pythonCron . ' ' . $importLogId['id']) . ' > /dev/null &';
                }
                else {
                    $command = escapeshellcmd("/usr/local/bin/python3.6 " . $pythonCron . ' ' . $importLogId['id']) . ' > /dev/null &';
                }
                $output = shell_exec($command);
                print_r($output);
                echo json_encode(array('status' => 2, "message" => "@Importing... Please check import history for more detail@"));
            }
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
            $file_path = '/data/upload_file/';
            if (in_array(ENVIRONMENT, array('UAT'))) {
                $today = strtotime("2019-11-20 00:00:00");
            }
            else {
                $today = strtotime('today 00:00:00');
            }
            $todayFile = date('Ymd', $today);
            $file_path = $file_path . $todayFile . '/';
            $file_name = 'ReportReleaseSale.xlsx';
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

    function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $match = [];
            if(!in_array("viewall", $this->data["permission"]["actions"])) {
                $extension = $this->session->userdata("extension");
                $this->load->model("group_model");
                $members = $this->group_model->members_from_lead($extension);
                $telesaleList = $this->crud->distinct(set_sub_collection('Telesalelist'), array(), array('id_no'), array('assign' => array('$in' => $members)));
                if(!empty($telesaleList)) {
                    $listCMND = $telesaleList['data'];
                    $match['cmnd'] = array(
                        '$in'   => $listCMND
                    );
                }
            }
            $response = $this->crud->read($this->collection, $request, [], $match);
            if(!empty($response['data'])) {
                foreach($response['data'] as $key => &$value) {
                    if(!empty($value['cif'])) {
                        $telesaleListInfo = $this->crud->where(array('cif' => $value['cif']))->getOne(set_sub_collection('Telesalelist'));
                        $value['customer_info'] = (!empty($telesaleListInfo)) ? $telesaleListInfo : array();
                    }
                    
                }
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function detail($id)
    {
        try {
            $response = $this->crud->where_id($id)->getOne($this->collection);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function create()
    {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        try {
            $this->load->library("crud");
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["created_at"]	= time();
            $data["created_by"]	= $this->session->userdata("extension");
            $result = $this->crud->create($this->collection, $data);
            echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function update($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["updated_by"]  =   $this->session->userdata("extension");
            $data['updated_at'] = time();
            $result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function delete($id)
    {
        try {
            $result = $this->crud->where_id($id)->delete($this->collection, TRUE);
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}