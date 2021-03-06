<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Sc_schedule extends WFF_Controller {

	private $collection = "Sc_schedule";
	private $ftpFilename = '';

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
        $this->load->library("Excel");
        $this->load->library("csv");
        $this->load->library('mongo_db');
        $this->load->model('ftp_model');
		$this->collection = set_sub_collection($this->collection);
	}

//    function importExcel()
//    {
//        try {
//            $request = json_decode(file_get_contents('php://input'), TRUE);
//            $checkScriptRunning = $this->mongo_db->where(array('collection' => 'Sc_schedule', 'status' => 2))->get(set_sub_collection('Import'));
//            if(!empty($checkScriptRunning)) {
//                echo json_encode(array("status" => 0, "message" => '@A file is importing. Please try again later@'));
//            }
//            else {
//                $pythonUrl = FCPATH . 'cronjob/python/Telesales/importSCSchedule.py';
//                $command = escapeshellcmd("python3.6 " . $pythonUrl . " > /dev/null &");
//                $output = shell_exec($command);
//                echo json_encode(array('status' => 2, "message" => "@Importing... Please check import history for more detail@"));
//            }
//        } catch (Exception $e) {
//            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
//        }
//    }

    function importExcel()
    {
        try {
            $request = json_decode(file_get_contents('php://input'), TRUE);
            $checkScriptRunning = $this->mongo_db->where(array('collection' => 'Sc_schedule', 'status' => 2))->get(set_sub_collection('Import'));
            if(!empty($checkScriptRunning)) {
                echo json_encode(array("status" => 0, "message" => '@A file is importing. Please try again later@'));
            }
            else {
                $importLog = array(
                    'collection'    => "Sc_schedule",
                    'begin_import'  => time(),
                    'file_name'     => basename($request["filepath"]),
                    'file_path'     => $request['filepath'],
                    'source'        => $request['import_type'],
                    'file_type'     => $request['import_file_type'],
                    'status'        => 2,
                    'created_by'    => $this->session->userdata("extension")
                );
                $importLogId = $this->crud->create(set_sub_collection('Import'), $importLog);
                $pythonCron = FCPATH . 'cronjob/python/Telesales/importSCSchedule.py ';
                if (in_array(ENVIRONMENT, array('UAT', 'development'))) {
                    $command = escapeshellcmd("python3.6 " . $pythonCron . $importLogId['id']) . ' > /dev/null &';
                }
                else {
                    $command = escapeshellcmd("/usr/local/bin/python3.6 " . $pythonCron . $importLogId['id']) . ' > /dev/null &';
                }
                $output = shell_exec($command);
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
            $response = $this->crud->read(set_sub_collection('Sc_schedule_result'), $request);
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
            $file_name = 'Lich_lam_viec_SC.xlsx';
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

    function read() {
        try {
            $result = array();
            $request = json_decode($this->input->get("q"), TRUE);
            $model = $this->crud->build_model($this->collection);
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_kendo_query($request)->selecting();
            $this->kendo_aggregate->filtering();
            if(empty($request['filter'])) {
                $endtime              = strtotime(date('Y-m-20'));
                $lastDayOneMonth        = date("Y-m-21",strtotime(date("Y-m-d", $endtime) . " -1 month"));
                
                // $month = date('m', strtotime('last month'));
                // $starttime = strtotime('21-' . $getDate['mon'] . '-2019 00:00:00');
                // $endtime = strtotime('20-' . $getDate['mon'] . '-2019 23:59:59');
                $match = array(
                    '$match'    => array(
                        'from_date' => array(
                            '$gte'  => strtotime($lastDayOneMonth),
                            '$lte'  => $endtime
                        )
                    )
                );
                $this->kendo_aggregate->adding($match);
            }
            $group = array('$group' => array(
                '_id'                       => '$dealer_code',
                'schedule'                  => array(
                    '$push'                 => array(
                        'sc_code'           => '$sc_code',
                        'kendoGridField'    => '$kendoGridField'
                    )
                )
            ));
            $this->kendo_aggregate->adding($group);
            $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
            $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
            $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
            $this->kendo_aggregate->sorting();
            $this->kendo_aggregate->paging();
            $data_aggregate = $this->kendo_aggregate->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
            foreach ($data as $key => &$value) {
                $listSchedule = array();
                foreach ($value['schedule'] as $key1 => $value1) {
                    $listSchedule[$value1['kendoGridField']] = implode(", ", $value1['sc_code']);
                }
                $value = array_merge($value, $listSchedule, array('dealer_code' => $value['_id']));
                unset($value['schedule']);
                array_push($result, $value);
            }
            echo json_encode(array('data' => $result, 'total' => $total));
        }
        catch(Exception $e) {
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