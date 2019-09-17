<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

Class Agentstatusdetail extends WFF_Controller {

    private $collection = "Agent_status_log";

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
            $match = array();
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

            $statusCodeACW = $this->mongo_db->where(array('value' => 4))->getOne(set_sub_collection('Agent_status_code'));
            $group = array(
                '_id'                   => array(
                    'extension'         => '$extension',
                    'starttime'         => array(
                        '$subtract'     => array(
                            '$starttime', array(
                                '$mod'  => array(
                                    array('$add' => array('$starttime', 25200)), 86400
                                )
                            )
                        )
                    )
                ),
                'unavailable'           => array(
                    '$sum'              => array(
                        '$cond'         => array(
                            array(
                                '$eq'   => array('$statuscode', 0)
                            ),
                            '$subStartEnd',
                            0
                        )
                    )
                ),
                'available'             => array(
                    '$sum'              => array(
                        '$cond'         => array(
                            array(
                                '$eq'   => array('$statuscode', 1)
                            ),
                            '$subStartEnd',
                            0
                        )
                    )
                ),
                'oncall'                => array(
                    '$sum'              => array(
                        '$cond'         => array(
                            array(
                                '$eq'   => array('$statuscode', 2)
                            ),
                            '$subStartEnd',
                            0
                        )
                    )
                ),
            );
            foreach ($statusCodeACW['sub'] as $key => $value) {
                $group[vn_to_str($value)] = array(
                    '$sum'              => array(
                        '$cond'         => array(
                            array(
                                '$eq'   => array('$substatus', $value)
                            ),
                            '$subStartEnd',
                            0
                        )
                    )
                );
            }

            $group['acw'] = array(
                '$sum'              => array(
                    '$cond'         => array(
                        array(
                            '$eq'   => array('$substatus', 'ACW')
                        ),
                        '$subStartEnd',
                        0
                    )
                )
            );

            $group['continueacw'] = array(
                '$sum'              => array(
                    '$cond'         => array(
                        array(
                            '$eq'   => array('$substatus', 'Continue ACW')
                        ),
                        '$subStartEnd',
                        0
                    )
                )
            );

            $pipeline = array(
                array(
                    '$project'          => array(
                        'extension'     => 1,
                        'statuscode'    => 1,
                        'agentstate'    => 1,
                        'substatus'     => 1,
                        'starttime'     => 1,
                        'endtime'       => 1,
                        'subStartEnd'   => array(
                            '$subtract' => array('$endtime', '$starttime')
                        ),
                        '_id'           => 1
                    )
                ),
                array(
                    '$match'            => $match
                ),
                array(
                    '$group'            => $group
                ),
                array(
                    '$sort'             => array(
                        '_id.starttime' => 1
                    )
                )
            );

            $pipelineCount = $pipeline;

            $pipelineCount[] = array(
                '$count'                => 'count'
            );

            $pipeline[] = array(
                '$skip'                 => $request['skip']
            );

            $pipeline[] = array(
                '$limit'                => $request['take']
            );

            $resultData = $this->crud->aggregate_pipeline($this->collection, $pipeline);

            $userInfo = $this->mongo_private->get(set_sub_collection('User'));
            $listExtName = array_column($userInfo, 'agentname', 'extension');
            foreach ($resultData as $resuleKey => &$resultValue) {
                $resultValue['agentname'] = (!empty($resultValue['_id']['extension'])) ? $listExtName[$resultValue['_id']['extension']] : '';
            }

            $resultTotal = $this->crud->aggregate_pipeline($this->collection, $pipelineCount);

            $response = array(
                'data'      => (!empty($resultData)) ? $resultData : array(),
                'total'     => (!empty($resultTotal) && !empty($resultTotal[0]['count'])) ? $resultTotal[0]['count'] : 0,
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
        $totalTime = array(
            'unavailable'       => 0,
            'available'         => 0,
            'oncall'            => 0
        );
        $request = json_decode($this->input->get("q"), TRUE);
        $match = array();
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

        $statusCodeACW = $this->mongo_db->where(array('value' => 4))->getOne(set_sub_collection('Agent_status_code'));
        $group = array(
            '_id'                   => array(
                'extension'         => '$extension',
                'starttime'         => array(
                    '$subtract'     => array(
                        '$starttime', array(
                            '$mod'  => array(
                                array('$add' => array('$starttime', 25200)), 86400
                            )
                        )
                    )
                )
            ),
            'unavailable'           => array(
                '$sum'              => array(
                    '$cond'         => array(
                        array(
                            '$eq'   => array('$statuscode', 0)
                        ),
                        '$subStartEnd',
                        0
                    )
                )
            ),
            'available'             => array(
                '$sum'              => array(
                    '$cond'         => array(
                        array(
                            '$eq'   => array('$statuscode', 1)
                        ),
                        '$subStartEnd',
                        0
                    )
                )
            ),
            'oncall'                => array(
                '$sum'              => array(
                    '$cond'         => array(
                        array(
                            '$eq'   => array('$statuscode', 2)
                        ),
                        '$subStartEnd',
                        0
                    )
                )
            ),
        );
        foreach ($statusCodeACW['sub'] as $key => $value) {
            $group[vn_to_str($value)] = array(
                '$sum'              => array(
                    '$cond'         => array(
                        array(
                            '$eq'   => array('$substatus', $value)
                        ),
                        '$subStartEnd',
                        0
                    )
                )
            );
            $totalTime[vn_to_str($value)] = 0;
        }

        $group['acw'] = array(
            '$sum'              => array(
                '$cond'         => array(
                    array(
                        '$eq'   => array('$substatus', 'ACW')
                    ),
                    '$subStartEnd',
                    0
                )
            )
        );

        $totalTime['acw'] = 0;

        $group['continueacw'] = array(
            '$sum'              => array(
                '$cond'         => array(
                    array(
                        '$eq'   => array('$substatus', 'Continue ACW')
                    ),
                    '$subStartEnd',
                    0
                )
            )
        );

        $totalTime['continueacw'] = 0;

        $pipeline = array(
            array(
                '$project'          => array(
                    'extension'     => 1,
                    'statuscode'    => 1,
                    'agentstate'    => 1,
                    'substatus'     => 1,
                    'starttime'     => 1,
                    'endtime'       => 1,
                    'subStartEnd'   => array(
                        '$subtract' => array('$endtime', '$starttime')
                    ),
                    '_id'           => 1
                )
            ),
            array(
                '$match'            => $match
            ),
            array(
                '$group'            => $group
            ),
            array(
                '$sort'             => array(
                    '_id.starttime' => 1
                )
            )
        );

        $pipelineCount = $pipeline;

        $pipelineCount[] = array(
            '$count'                => 'count'
        );

        $pipeline[] = array(
            '$skip'                 => $request['skip']
        );

        $pipeline[] = array(
            '$limit'                => $request['take']
        );

        $resultData = $this->crud->aggregate_pipeline($this->collection, $pipeline);

        $userInfo = $this->mongo_private->get(set_sub_collection('User'));
        $listExtName = array_column($userInfo, 'agentname', 'extension');
        foreach ($resultData as $resuleKey => &$resultValue) {
            $resultValue['agentname'] = (!empty($resultValue['_id']['extension'])) ? $listExtName[$resultValue['_id']['extension']] : '';
        }

        $dataExport = $this->crud->aggregate_pipeline($this->collection, $pipeline);

        $userInfo = $this->mongo_private->get(set_sub_collection('User'));
        $listExtName = array_column($userInfo, 'agentname', 'extension');
        foreach ($dataExport as $resuleKey => &$resultValue) {
            $resultValue['agentname'] = (!empty($resultValue['_id']['extension'])) ? $listExtName[$resultValue['_id']['extension']] : '';
            $resultValue['extension'] = $resultValue['_id']['extension'];
            $resultValue['starttime'] = date('d/m/Y', $resultValue['_id']['starttime']);
            foreach ($resultValue as $resultValueKey => $resultValueValue) {
                if(!in_array($resultValueKey, array('agentname', 'extension', 'starttime', '_id'))) {
                    $totalTime[$resultValueKey] += $resultValueValue;
                }
            }
        }

        $filename = "Bao_Cao_Chi_Tiet_Thoi_Gian_Hoat_Dong_Cua_DTV.xlsx";

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
            $fieldToCol[$columnVal['field']] = $col;
            $worksheet->setCellValue($col . $row, str_replace('@', '', $columnVal['title']));
            $worksheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
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
                    if(isset($fieldToCol[ $field ])) {
                        $col = $fieldToCol[ $field ];
                        if(!in_array($field, array('extension', 'agentname', 'starttime', '_id'))) {
                            $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        }
                        else {
                            $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                    }
//                    if($field === '')
                }
                if($row % 2 == 1) {
                    $worksheet->getStyle("A{$row}:{$maxCol}{$row}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('F0F6DA');
                }
                $row++;
            }
            $worksheet->mergeCells('A' . $row . ':C' . $row);
            $worksheet->setCellValueExplicit('A' . $row, 'Total', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->getStyle("A{$row}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('8db4e3');
            $worksheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            foreach ($totalTime as $field => $value) {
                $col = $fieldToCol[ $field ];
                if(!in_array($field, array('extension', 'agentname', 'starttime', '_id'))) {
                    $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                }
                else {
                    $worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $file_path = UPLOAD_PATH . "excel/" . $filename;
        $writer->save($file_path);
        echo json_encode($file_path);
        // PRINT EXCEL WAY 2
    }

    function getAgentStatusCode() {
        try {
            echo json_encode($this->crud->where(array('value' => 4))->getOne(set_sub_collection('Agent_status_code')));
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}