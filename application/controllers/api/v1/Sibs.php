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
            $checkImport = $this->mongo_db->where(array('collection' => 'Sibs', 'status' => 2))->get(set_sub_collection('Import'));
            if(!empty($checkImport)) {
                echo json_encode(array("status" => 0, "message" => '@A file is importing. Please try again later@'));
            }
            else {
                $pythonUrl = FCPATH . 'cronjob/python/Telesales/importSibs_cron.py ';
                $command = escapeshellcmd('python3.6 ' . $pythonUrl . 'callfromweb') . ' > /dev/null &';
                $output = shell_exec($command);
                echo json_encode(array("status" => 2, "message" => "@Importing... Please check import history for more detail@"));
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
            $response = $this->crud->read(set_sub_collection('Sibs_import_result'), $request);
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
            $file_name = 'ZACCF.txt';
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
}