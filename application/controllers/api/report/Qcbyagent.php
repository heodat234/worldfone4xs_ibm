<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

Class Qcbyagent extends WFF_Controller {

    private $collection = "worldfonepbxmanager";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_private");
        $this->collection = set_sub_collection($this->collection);
    }

    function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $match = array(
                'qcstatus'  => "1"
            );
            // Thông tin search
            foreach ($request['filter']['filters'] as $key => $value) {
                if($value['operator'] === 'gte' || $value['operator'] === 'gt') {
                    $match[$value['field']] = array(
                        '$' . $value['operator'] => strtotime($value['value']),
                        '$' . $request['filter']['filters'][$key + 1]['operator'] => strtotime($request['filter']['filters'][$key + 1]['value'])
                    );
                }
                elseif ($value['operator'] !== 'lte' && $value['operator'] !== 'lt') {
                    switch ($value['operator']) {
                        case 'eq':
                            $match[$value['field']] = $value['value'];
                            break;
                        case 'neq':
                            $match[$value['field']] = array('$ne' => $value['value']);
                            break;
                        case 'isnull':
                            $match[$value['field']] = null;
                            break;
                        case 'isnotnull':
                            $match[$value['field']] = array('$ne' => null);
                            break;
                        case 'startswith':
                            $match[$value['field']] = new MongoDB\BSON\Regex ('^' . $value['value']);
                            break;
                        case 'in':
                            $match[$value['field']] = array('$in' => $value['value']);
                            break;
                    }
                }
            }
            // Thông tin search

            $group = array(
                '_id'                   => array(
                    'extension'         => '$userextension',
                ),
                'totalCall'             => array(
                    '$sum'              => 1
                ),
                'totalMark'             => array(
                    '$sum'              => '$endPoint'
                ),
            );

            $pipeline = array(
                array(
                    '$match'            => $match,
                ),
                array(
                    '$group'            => $group
                ),
            );

            $pipelineCount = $pipeline;
            $pipelineCount[] = array(
                '$count'         => 'count'
            );
            $pipeline[] = array(
                '$skip'         => $request['skip']
            );
            $pipeline[] = array(
                '$limit'        => $request['take']
            );

            $resultData = $this->crud->aggregate_pipeline($this->collection, $pipeline);
            $resultTotal = $this->crud->aggregate_pipeline($this->collection, $pipelineCount);

            $userInfo = $this->mongo_private->get(set_sub_collection('User'));
            $listExtName = array_column($userInfo, 'agentname', 'extension');
            foreach ($resultData as $keyData => &$valueData) {
                $valueData['agentname'] = (!empty($valueData['_id']['extension'])) ? $listExtName[$valueData['_id']['extension']] : '';
            }
            $response = array(
                'data'      => (!empty($resultData)) ? $resultData : array(),
                'total' => (!empty($resultTotal) && !empty($resultTotal[0]['count'])) ? $resultTotal[0]['count'] : 0,
                'group'     => array(),
            );

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function getGroupBy() {
    	$this->load->library("crud");
        $request = json_decode($this->input->get("q"), TRUE);
        $model = $this->crud->build_model($this->collection);
        // Kendo to aggregate
        $this->load->library("kendo_aggregate", $model);   
        $this->kendo_aggregate->set_kendo_query($request)->selecting();
        $match = array(
        	"endtime" => array('$ne' => 0)
        );
        $this->kendo_aggregate->filtering()->matching($match);
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;


        $lookup = array('$lookup' => array(
        		"from" => set_sub_collection("Agent_status_code"),
			    "localField" => "statuscode",
			    "foreignField" => "value",
			    "as" => "status"
        	)
    	);
        $unwind = array('$unwind' => array(
    			'path'							=> '$status',
		    	'preserveNullAndEmptyArrays'	=> TRUE
    		)
    	);
    	$project = array('$project' => array(
    			"_id"				=> 0,
    			"extension" 		=> 1,
    			"statuscode" 		=> 1,
    			"substatus" 		=> 1,
    			"starttime" 		=> 1,
    			"endtime"			=> 1,
    			"statusText"		=> '$status.text',
    		)
    	);
    	$this->kendo_aggregate->adding($lookup, $unwind, $project);
        if(!empty($request["group"])) {
            $requestGroup = $request["group"];
            $groupArr = array();
            $concatArr = array();
            if(count($requestGroup) == 1) {
                $field = $requestGroup[0]["field"];
                $groupArr = '$' . $field;
                $concatArr = ['$' . $field];
                $project = array('$project' => array('idFields' => '$_id', 'count' => 1, "sum" => 1));
            } else {
                foreach ($requestGroup as $index => $doc) {
                    $groupArr[$doc["field"]] = '$' . $doc["field"];
                    $concatArr[] = '$_id.' . $doc["field"];
                    if($index + 1 < count($requestGroup)) {
                        $concatArr[] = " - ";
                    }
                }
                $project = array('$project' => array('idFields' => array('$concat' => $concatArr), 'count' => 1, "sum" => 1));
            }
            $group = array('$group' => array(
                    '_id' => $groupArr,
                    'count' => array('$sum' => 1),
                    'sum'	=> array('$sum' => array('$subtract' => ['$endtime', '$starttime']))
                )
            );
            $this->kendo_aggregate->adding($group, $project);
        }
        // Get data
        $this->kendo_aggregate->sorting();
        $data_aggregate = $this->kendo_aggregate->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
        // Result
        $response = array("data" => $data, "total" => $total);
        echo json_encode($response);
    }

    public function groupByExtensionAndStatus()
    {
    	$this->load->library("crud");
        $request = json_decode($this->input->get("q"), TRUE);
        $model = $this->crud->build_model($this->collection);
        // Kendo to aggregate
        $this->load->library("kendo_aggregate", $model);   
        $this->kendo_aggregate->set_kendo_query($request)->selecting();
        $match = array(
        	"endtime" => array('$ne' => 0)
        );
        $this->kendo_aggregate->filtering()->matching($match);
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;


        $lookup = array('$lookup' => array(
        		"from" => set_sub_collection("Agent_status_code"),
			    "localField" => "statuscode",
			    "foreignField" => "value",
			    "as" => "status"
        	)
    	);
        $unwind = array('$unwind' => array(
    			'path'							=> '$status',
		    	'preserveNullAndEmptyArrays'	=> TRUE
    		)
    	);
    	$project = array('$project' => array(
    			"_id"				=> 0,
    			"extension" 		=> 1,
    			"statuscode" 		=> 1,
    			"substatus" 		=> 1,
    			"starttime" 		=> 1,
    			"endtime"			=> 1,
    			"statusText"		=> '$status.text',
    			"statusCode"		=> '$status.code',
    		)
    	);
    	$this->kendo_aggregate->adding($lookup, $unwind, $project);
    	$group = array('$group' => array(
                '_id' => array(
                	"extension" => '$extension',
                	"statusCode" => '$statusCode'
                ),
                'sum'	=> array('$sum' => array('$subtract' => ['$endtime', '$starttime']))
            )
        );
        $this->kendo_aggregate->adding($group);
       
        // Get data
        $this->kendo_aggregate->sorting();
        $data_aggregate = $this->kendo_aggregate->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
        //pre($data);
        $data_response = array();
        foreach ($data as $doc) {
        	$extension = $doc["_id"]["extension"];
        	$statusCode = $doc["_id"]["statusCode"];
        	if(!isset($data_response[$extension]))
	        	$data_response[$extension] = array("extension" => $extension);

        	if(!isset($data_response[$extension][$statusCode])) 
        		$data_response[$extension][$statusCode] = 0;

        	$data_response[$extension][$statusCode] += $doc["sum"];

        	if(!isset($data_response[$extension]["total"])) 
        		$data_response[$extension]["total"] = 0;

        	$data_response[$extension]["total"] += $doc["sum"];
        }
        $data_response = array_values($data_response);
        // Result
        $response = array("data" => $data_response, "total" => count($data_response));
        echo json_encode($response);
    }

    function exportExcel() {
        ini_set("memory_limit","256M");
        $request = json_decode($this->input->get("q"), TRUE);
        $dataExport = array();
        $match = array(
            'qcdata'    => array(
                '$ne'   => null
            )
        );
        // Thông tin search
        foreach ($request['filter']['filters'] as $key => $value) {
            if($value['operator'] === 'gte' || $value['operator'] === 'gt') {
                $match[$value['field']] = array(
                    '$' . $value['operator'] => strtotime($value['value']),
                    '$' . $request['filter']['filters'][$key + 1]['operator'] => strtotime($request['filter']['filters'][$key + 1]['value'])
                );
            }
            elseif ($value['operator'] !== 'lte' && $value['operator'] !== 'lt') {
                switch ($value['operator']) {
                    case 'eq':
                        $match[$value['field']] = $value['value'];
                        break;
                    case 'neq':
                        $match[$value['field']] = array('$ne' => $value['value']);
                        break;
                    case 'isnull':
                        $match[$value['field']] = null;
                        break;
                    case 'isnotnull':
                        $match[$value['field']] = array('$ne' => null);
                        break;
                    case 'startswith':
                        $match[$value['field']] = new MongoDB\BSON\Regex ('^' . $value['value']);
                        break;
                    case 'in':
                        $match[$value['field']] = array('$in' => $value['value']);
                        break;
                }
            }
        }
        // Thông tin search

        $group = array(
            '_id'                   => array(
                'extension'         => '$userextension',
            ),
            'totalCall'             => array(
                '$sum'              => 1
            ),
            'totalMark'             => array(
                '$sum'              => '$endPoint'
            ),
        );

        $pipeline = array(
            array(
                '$match'            => $match,
            ),
            array(
                '$group'            => $group
            ),
        );

        $dataExport = $this->crud->aggregate_pipeline(set_sub_collection('worldfonepbxmanager'), $pipeline);
        if(!empty($dataExport)) {
            foreach ($dataExport as $dataKey => &$dataValue) {
                $dataValue['extension'] = $dataValue['_id']['extension'];
                $dataValue['agentname'] = $dataValue['_id']['agentname'];
                $dataValue['no'] = $dataKey + 1;
                $dataValue['averageMark'] = round(($dataValue['totalMark'] / $dataValue['totalCall']), 2);
            }
        }
        $filename = "QC_by_agent.xlsx";

        // PRINT EXCEL WAY 2
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("South Telecom")
            ->setLastModifiedBy("Tri Dung")
            ->setTitle("Report")
            ->setSubject("Report")
            ->setDescription("Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Report");

        $worksheet = $spreadsheet->getActiveSheet();

        $fieldToCol = array();
        $col = "A";
        $row = 1;

        foreach ($request['column'] as $columnNum => $columnVal) {
            if(!empty($columnVal['field'])) {
                $fieldToCol[$columnVal['field']] = $col;
                $worksheet->setCellValue($col . $row, str_replace('@', '', $columnVal['title']));
                $worksheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }
        }

        --$col;
        $maxCol = $col;
        $worksheet->getStyle("A1:{$maxCol}1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');

        if($dataExport) {
            $row = 2;
            foreach ($dataExport as $doc) {
                foreach ($doc as $field => $value) {
//                    print_r($field);
                    if(isset($fieldToCol[ $field ])) {
                        $col = $fieldToCol[ $field ];
                        $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
                if($row % 2 == 1) {
                    $worksheet->getStyle("A{$row}:{$maxCol}{$row}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('F0F6DA');
                }
                $row++;
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $file_path = UPLOAD_PATH . "excel/" . $filename;
        $writer->save($file_path);
        echo json_encode($file_path);
        // PRINT EXCEL WAY 2
    }
}