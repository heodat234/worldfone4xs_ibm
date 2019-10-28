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
        $this->load->library('pheanstalk');
        $this->load->model('ftp_model');
		$this->collection = set_sub_collection($this->collection);
	}

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
                $command = escapeshellcmd("python3.6 /var/www/html/python/importSCSchedule.py " . $importLogId['id']) . ' > /dev/null &';
                $output = shell_exec($command);
//                $checkImport = $this->crud->where_id($importLogId['id'])->where(array('status' => 1))->get(set_sub_collection('Import'));
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
            $response = $this->crud->read(set_sub_collection('Scschedule_import_result'), $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function listFileFTP() {
        try {
            echo json_encode(array('data' => array(array('filepath' => '/var/www/html/worldfone4xs_ibm/upload/excel/Danhsachquaytuvan.xlsx', 'filename' => 'Danhsachquaytuvan.xlsx')), 'total' => 1));
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
    function read() {
	    try {
	        $result = array();
            $request = json_decode($this->input->get("q"), TRUE);
            $pipeline = array();
            if(!empty($request['filter']['filters'][0]['filters'])) {
                $match = array('from_date' => array());
                foreach ($request['filter']['filters'][0]['filters'] as $key => $value) {
                    if($value['operator'] == 'gte') {
                        $match['from_date']['$gte'] = strtotime($value['value']);
                    }
                    if($value['operator'] == 'lte') {
                        $match['from_date']['$lte'] = strtotime($value['value']);
                    }
                }
                $pipeline[] = array(
                    '$match' => $match
                );
            }
            $pipeline[] = array(
                '$group'                        => array(
                    '_id'                       => '$dealer_code',
                    'schedule'                  => array(
                        '$push'                 => array(
                            'dealer_code'       => '$dealer_code',
                            'sc_code'           => '$sc_code',
                            'kendoGridField'    => '$kendoGridField'
                        )
                    )
                )
            );
            $pipelineTotal = $pipeline;
            array_push($pipelineTotal, array('$count' => "total"));
            array_push($pipeline, array('$skip' => $request['skip']));
            array_push($pipeline,array('$limit' => $request['take']));
            $data = $this->crud->aggregate_pipeline($this->collection, $pipeline);
            foreach ($data as $key => &$value) {
                $listSchedule = array();
                foreach ($value['schedule'] as $key1 => $value1) {
                    $listSchedule[$value1['kendoGridField']] = implode(", ", $value1['sc_code']);
                }
                $value = array_merge($value, $listSchedule, array('dealer_code' => $value['_id']));
                unset($value['schedule']);
                array_push($result, $value);
            }
            $total = $this->crud->aggregate_pipeline($this->collection, $pipelineTotal);
            if(!empty($total)) {
                $total = $total[0]['total'];
            }
            else {
                $total = 0;
            }
            echo json_encode(array('data' => $result, 'total' => $total));
        }
        catch(Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}