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
            if(!in_array("viewall", $this->data["permission"]["actions"])) {
                $extension = $this->session->userdata("extension");
                $this->load->model("group_model");
                $members = $this->group_model->members_from_lead($extension);
                $match["userextension"] = ['$in' => $members];
            }
            $response = $this->crud->read($this->collection, $request, [], $match);

            foreach ($response["data"] as &$doc) {
                if(isset($doc["dialid"]) && empty($doc["customer"])) {
                    $diallistDetail = $this->mongo_db->where_id($doc["dialid"])->getOne($this->sub . "Diallist_detail");
                    if($diallistDetail) {
                        if(isset($diallistDetail["cus_name"]) && empty($diallistDetail["name"])) 
                            $diallistDetail["name"] = $diallistDetail["cus_name"];
                        $this->mongo_db->where_id($doc["id"])->set(array("customer" => $diallistDetail))
                            ->update($this->collection);
                        $doc["customer"] = $diallistDetail;
                    }
                }
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
        try {
            $this->load->library("crud");
            $request = json_decode($this->input->get("q"), TRUE);
            // PERMISSION
            $match = array();
            if(!in_array("viewall", $this->data["permission"]["actions"])) {
                $extension = $this->session->userdata("extension");
                $this->load->model("group_model");
                $members = $this->group_model->members_from_lead($extension);
                $match["userextension"] = ['$in' => $members];
            }
            $response = $this->crud->read($this->collection, $request, [], $match);
            foreach ($response["data"] as &$doc) {
                if(!empty($doc["customernumber"]) && empty($doc["customer"])) {
                    $phone = $doc["customernumber"];
                    $customers = [];
                    if(isset($doc['dialtype']) && $doc['dialtype'] == 'sc') {
                        $customers = $this->mongo_db->where_or(array("phone" => $phone, "other_phones" => $phone))->get($this->sub . "Sc");
                    } else {
                        $customers = $this->mongo_db->where_or(array("phone" => $phone, "other_phones" => $phone))->get($this->sub . "Telesalelist");
                    }
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