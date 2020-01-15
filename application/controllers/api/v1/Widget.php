<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Widget extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("session");
		$this->load->model("language_model");
	}

    function user_list()
    {
    	$request = json_decode($this->input->get("q"), TRUE);
    	$this->load->library("crud");
    	$this->load->model("agentsign_model");
    	$this->crud->select_db($this->config->item("_mongo_db"));
    	$result = $this->crud->read(set_sub_collection("User"), $request, ["extension", "agentname", "avatar","statuscode", "substatus", "chat_statuscode", "chat_substatus"],  array("active" => TRUE));
    	$this->crud->select_db();
    	foreach ($result["data"] as $index => &$doc) {
    		$doc["totalCurrentUser"] = $this->agentsign_model->count_current_by_extension($doc["extension"]);
    	}
    	echo json_encode($result);
    }

    function readNotification($id = "") 
    {
        try {
            if(!$id) throw new Exception("Lack of id");
            $this->load->library("mongo_db");
            $extension = $this->session->userdata("extension");
            $time = time();
            $data = array("extension" => $extension, "time" => $time);
            $this->mongo_db->where_id($id)->push("read", $data)->update(set_sub_collection("Notification"));
            echo json_encode(array("status" => 1));
        } catch(Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function search()
    {
    	$request = json_decode($this->input->get("q"), TRUE);
    	$sub = set_sub_collection();
    	$this->load->library("crud");

        // Page
        $response = array("data" => [], "total" => 0);
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $my_session_id = $this->session->userdata("my_session_id");
        if(!$navigatorData = $this->cache->get($my_session_id . "_permissions")) {
            $this->load->library("authentication");
            $this->authentication->check_permissions();
            $navigatorData = $this->cache->get($my_session_id . "_permissions");
        }
        $navigatorData = $this->language_model->translate($navigatorData, "SIDEBAR");
        if(isset($request["filter"], $request["filter"]["filters"], $request["filter"]["filters"][0])) {
            $search = $request["filter"]["filters"][0]["value"];
            // Escape /
            $search = str_replace('/', '\/', $search);

            foreach ($navigatorData as $key => $doc) {
                if((preg_match("/{$search}/i", $doc["name"]) || preg_match("/{$search}/i", $doc["uri"])) && !empty($doc["visible"])) {
                    $response["data"][] = array(
                        "type"      => "Page",
                        "typeText"  => "@Page@",
                        "name"      => isset($doc["name"]) ? $doc["name"] : "",
                        "uri"       => isset($doc["uri"]) ? $doc["uri"] : "",
                    );
                    $response["total"]++;
                }
            }
        }

    	// Customer
    	$customerData = $this->crud->read($sub . "Customer", $request, ["name", "cif", "phone", "address"]);
    	foreach ($customerData["data"] as $key => $d) {
    		$d["type"] = "Customer";
    		$d["typeText"] = "@Customer@";
    		$d["typeText"] .= !empty($d["cif"]) ? " - cif: ".$d["cif"] : "";
    		$d["typeText"] .= !empty($d["phone"]) ? " - @Phone@: ".$d["phone"] : "";
            $response["data"][] = $d;
            $response["total"]++;
    	}


    	$response = $this->language_model->translate($response, "CONTENT");
    	echo json_encode($response);
    }

    function updateManualGroup()
    {
        try {
            $id = $this->input->post("id");
            $name = $this->input->post("name");
            if(!$id || !$name) throw new Exception("Lack of input", 1);

            $extension = $this->session->userdata("extension");
            $this->load->library("mongo_private");
            $this->mongo_private->where(["extension" => $extension])->update(getCT("User"), ['$set' => ["group_name" => $name, "group_id" => $id]]);
            
            $this->session->set_userdata("group_name", $name);
            $this->session->set_userdata("group_id", $id);
            echo json_encode(array("status" => 1));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}