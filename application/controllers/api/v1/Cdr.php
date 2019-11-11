<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Cdr extends WFF_Controller {

    private $collection = "worldfonepbxmanager";
    private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
        $this->sub = set_sub_collection("");
        $this->collection = $this->sub . $this->collection;
        $this->permission = $this->data["permission"];
	}

	function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $this->load->library("crud");
            // PERMISSION
            $match = array();
            if(!$this->session->userdata("test_mode") && !in_array("viewall", $this->permission["actions"])) {
                $match["userextension"] = $this->session->userdata("extension");
            }
            $response = $this->crud->read($this->collection, $request, [], $match);

            foreach ($response["data"] as &$doc) {
                if(!empty($doc["customernumber"]) && empty($doc["customer"])) {
                    $phone = $doc["customernumber"];
                    $customers = $this->mongo_db->where_or(array("phone" => $phone, "other_phones" => $phone))->get($this->sub . "Customer");
                    if($customers) {
                        if(count($customers) == 1) {
                            $this->mongo_db->where_id($doc["id"])->set(array("customer" => $customers[0]))
                            ->update($this->collection);
                            $doc["customer"] = $customers[0];
                        } else {
                            $doc["customer"] = $customers;
                        }
                    }
                }
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function LO_index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $this->load->library("crud");
            // PERMISSION
            $match = array();
            if(!$this->session->userdata("test_mode") && !in_array("viewall", $this->permission["actions"])) {
                $match["userextension"] = $this->session->userdata("extension");
            }
            $response = $this->crud->read($this->collection, $request, [], $match);

            foreach ($response["data"] as &$doc) {
                if(!empty($doc["customernumber"]) && empty($doc["customer"])) {
                    $phone = $doc["customernumber"];
                    $customers = $this->mongo_db->where_or(array("phone" => $phone, "other_phones" => $phone))->get($this->sub . "Customer");
                    if($customers) {
                        if(count($customers) == 1) {
                            $this->mongo_db->where_id($doc["id"])->set(array("customer" => $customers[0]))
                            ->update($this->collection);
                            $doc["customer"] = $customers[0];
                        } else {
                            $doc["customer"] = $customers;
                        }
                    }
                }
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function TS_index()
    {
        $this->load->library("crud");
        $request = json_decode($this->input->get("q"), TRUE);
        $requestString = json_encode($request);

        $model = $this->crud->build_model($this->collection);
        // Kendo to aggregate
        $this->load->library("kendo_aggregate", $model);
        $this->kendo_aggregate->set_kendo_query($request)->selecting();
        // PERMISSION
        if(!$this->session->userdata("test_mode") && $this->permission["actions"] && !in_array("viewall", $this->permission["actions"]))
            $this->kendo_aggregate->matching(array("userextension" => $this->session->userdata("extension")));

//        $this->kendo_aggregate->filtering();
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        $this->kendo_aggregate->sorting()->paging();

        $data_aggregate = $this->kendo_aggregate->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
//        print_r($data);
        foreach ($data as $key => &$value) {
            $value['customer'] = array();
            if($value['direction'] == 'inbound' || $value['dialtype'] == 'customer') {
                $customerInfo = $this->crud->where(array('mobile_phone_no' => $value['customernumber']))->getOne("{$this->sub}Telesalelist");
                $value['customer']['name'] = (!empty($customerInfo)) ? $customerInfo['customer_name'] : array();
            }
            elseif($value['direction'] == 'outbound' || $value['dialtype'] == 'sc') {
                $scInfo = $this->crud->where(array('phone' => $value['customernumber']))->getOne("{$this->sub}Sc");
                $value['customer']['name'] = (!empty($scInfo)) ? $scInfo['sc_name'] : array();
            }
        }
        // Result
        $response = array("data" => $data, "total" => $total);
        echo json_encode($response);
    }

    function detail($calluuid)
    {
        try {
            $this->load->library("mongo_db");
            $response = $this->mongo_db->where(array("calluuid" => $calluuid))->getOne($this->collection);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function update($calluuid)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            unset($data["calluuid"]);
            $this->load->library("mongo_db");
            $result = $this->mongo_db->where(array("calluuid" => $calluuid))->update($this->collection, array('$set' => $data));
            echo json_encode(array("status" => $result ? 1 : 0));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}