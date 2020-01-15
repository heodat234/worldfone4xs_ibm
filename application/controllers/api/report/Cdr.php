<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Cdr extends WFF_Controller {

    /**
     * API restful [worldfonepbxmanager] collection.
     * READ from base_url + api/restful/cdr
     * DETAIL from base_url + api/restful/cdr/$id
     */

    private $collection = "worldfonepbxmanager";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
    }

    function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
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
        $this->kendo_aggregate->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;

        if(!empty($request["group"])) {
            $requestGroup = $request["group"];
            $groupArr = array();
            $concatArr = array();
            if(count($requestGroup) == 1) {
                $field = $requestGroup[0]["field"];
                $groupArr = '$' . $field;
                $concatArr = ['$' . $field];
                $project = array('$project' => array('idFields' => '$_id', 'count' => 1));
            } else {
                foreach ($requestGroup as $index => $doc) {
                    $groupArr[$doc["field"]] = '$' . $doc["field"];
                    $concatArr[] = '$_id.' . $doc["field"];
                    if($index + 1 < count($requestGroup)) {
                        $concatArr[] = " - ";
                    }
                }
                $project = array('$project' => array('idFields' => array('$concat' => $concatArr), 'count' => 1));
            }
            $group = array('$group' => array(
                    '_id' => $groupArr,
                    'count' => array('$sum' => 1)
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

    public function groupByExtensionAndDisposition()
    {
        $this->load->library("crud");
        $request = json_decode($this->input->get("q"), TRUE);
        $model = $this->crud->build_model($this->collection);
        // Kendo to aggregate
        $this->load->library("kendo_aggregate", $model);   
        $this->kendo_aggregate->set_kendo_query($request)->selecting();
        $this->kendo_aggregate->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;


        $group = array('$group' => array(
                '_id' => array(
                    "extension" => '$userextension',
                    "disposition" => '$disposition'
                ),
                'billduration_sum'   => array('$sum' => '$billduration'),
                'callduration_sum'   => array('$sum' => '$callduration'),
                'totalduration_sum'  => array('$sum' => '$totalduration'),
                'firstcall_time'     => array('$first' => '$starttime'),
                'lastcall_time'      => array('$last' => '$starttime'),
                'sum'                => array('$sum' => 1)
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
            switch ($doc["_id"]["disposition"]) {
                case 'ANSWERED':
                    $disposition = "ANS";
                    break;

                case 'NO ANSWER':
                    $disposition = "NOA";
                    break;

                case 'BUSY':
                    $disposition = "BUS";
                    break;
                
                default:
                    $disposition = "OTH";
                    break;
            }


            if(!isset($data_response[$extension]))
                $data_response[$extension] = array("extension" => $extension);

            if(!isset($data_response[$extension][$disposition])) 
                $data_response[$extension][$disposition] = 0;

            $data_response[$extension][$disposition] += $doc["sum"];

            if(!isset($data_response[$extension]["total"])) 
                $data_response[$extension]["total"] = 0;

            $data_response[$extension]["total"] += $doc["sum"];

            foreach (["callduration","billduration","totalduration"] as $field) {
                if(!isset($data_response[$extension]["{$field}_total"])) 
                    $data_response[$extension]["{$field}_total"] = 0;

                $data_response[$extension]["{$field}_total"] += $doc["{$field}_sum"];
            }

            if(!isset($data_response[$extension]["firstcall_time"])) 
                $data_response[$extension]["firstcall_time"] = $doc["firstcall_time"];
            elseif($data_response[$extension]["firstcall_time"] > $doc["firstcall_time"]) {
                $data_response[$extension]["firstcall_time"] = $doc["firstcall_time"];
            }

            if(!isset($data_response[$extension]["lastcall_time"])) 
                $data_response[$extension]["lastcall_time"] = $doc["lastcall_time"];
            elseif($data_response[$extension]["lastcall_time"] < $doc["lastcall_time"]) {
                $data_response[$extension]["lastcall_time"] = $doc["lastcall_time"];
            }
        }
        $data_response = array_values($data_response);
        // Result
        $response = array("data" => $data_response, "total" => count($data_response));
        echo json_encode($response);
    }
}