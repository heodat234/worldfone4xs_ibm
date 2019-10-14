<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Search extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->sub = set_sub_collection();
		header('Content-type: application/json');
		$this->load->library("session");
		$this->load->model("language_model");
		$this->load->library("crud");
	}

    function page()
    {
    	$request = json_decode($this->input->get("q"), TRUE);

    	try {
	    	$this->load->library("crud");
	    	if(empty($request["keyword"])) {
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
		    	$customerData = $this->crud->read($this->sub . "Customer", $request, ["name", "cif", "phone", "address"]);
		    	foreach ($customerData["data"] as $key => $d) {
		    		$d["type"] = "Customer";
		    		$d["typeText"] = "@Customer@";
		    		$d["typeText"] .= !empty($d["cif"]) ? " - cif: ".$d["cif"] : "";
		    		$d["typeText"] .= !empty($d["phone"]) ? " - @Phone@: ".$d["phone"] : "";
		            $response["data"][] = $d;
		            $response["total"]++;
		    	}
	    	} else {
	    		// Page
	    		$search = $request["keyword"];
		        $response = array("data" => [], "total" => 0);
		        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
		        $my_session_id = $this->session->userdata("my_session_id");
		        if(!$navigatorData = $this->cache->get($my_session_id . "_permissions")) {
		            $this->load->library("authentication");
		            $this->authentication->check_permissions();
		            $navigatorData = $this->cache->get($my_session_id . "_permissions");
		        }
		        $navigatorData = $this->language_model->translate($navigatorData, "SIDEBAR");

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

		    	// Customer
		    	$where = array('$or' => array(
		    		["name" => $search],
		    		["cif" => $search],
		    		["phone" => $search],
		    		["address" => $search],
		    	));
		    	$customerData = $this->mongo_db->where($where)->select(["name", "cif", "phone", "address"])->get($this->sub . "Customer");
		    	foreach ($customerData as $key => $d) {
		    		$d["type"] = "Customer";
		    		$d["typeText"] = "@Customer@";
		    		$d["typeText"] .= !empty($d["cif"]) ? " - cif: ".$d["cif"] : "";
		    		$d["typeText"] .= !empty($d["phone"]) ? " - @Phone@: ".$d["phone"] : "";
		            $response["data"][] = $d;
		            $response["total"]++;
		    	}
	    	}

	    	$response = $this->language_model->translate($response, "CONTENT");
	    	echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
    }

    function library()
    {
    	try {
			$request = json_decode($this->input->get("q"), TRUE);
			$collection = $this->sub . "Library";
			$filterVisible = array(
				"logic" => "and",
				"filters" => array(
					array("field" => "visible", "operator" => "eq", "value" => true)
				)
			);
			if(isset($request["filter"])) {
				$request["filter"]["logic"] = "and";
				$request["filter"]["filters"][] = $filterVisible;
			} else {
				$request["filter"] = array();
				$request["filter"]["logic"] = "and";
				$request["filter"]["filters"] = array();
				$request["filter"]["filters"][] = $filterVisible;
			}
			$response = $this->crud->read($collection, $request);
			foreach ($response["data"] as &$doc) {
				if( isset($doc["parent_id"]) )
				{
					$parent = $this->crud->where(array("id" => $doc["parent_id"]))->getOne($collection);
					$doc["parent_name"] = isset($parent["name"]) ? $parent["name"] : "";
				}
				if( !empty($doc["hasChild"]) )
				{
					$doc["children"] = $this->crud->where(array("parent_id" => $doc["id"]))->get($collection);
				}
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
    }
}