<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallist extends WFF_Controller {

	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection("");
	}

	function diallistDetailField($subtype) {
		$request = $_GET;
		$this->load->library("crud");
		$this->crud->select_db($this->config->item("_mongo_db"));
		$request["sort"] = array(array("field" => "index", "dir" => "asc"));
		$match = array("sub_type" => $subtype, "collection" => $this->sub . "Diallist_detail");
		$response = $this->crud->read("Model", $request, ["field", "title", "type"], $match);
		echo json_encode($response);
	}

	function getStatistic($diallist_id) 
	{
		$collection = $this->sub . "Diallist_detail";
		$this->load->library("mongo_db");
		$diallist = $this->mongo_db->where_id($diallist_id)->select([], ["columns"])->getOne($this->sub . "Diallist");
		$total = $this->mongo_db->where_object_id("diallist_id", $diallist_id)->count($collection);
		$assigned = $this->mongo_db->where_object_id("diallist_id", $diallist_id)->where(array("assign" => ['$exists' => true]))->count($collection);
		$notAssigned = $this->mongo_db->where_object_id("diallist_id", $diallist_id)->where(array("assign" => ['$exists' => false]))->count($collection);
		$response = array_merge($diallist, array(
			"total" => $total,
			"assigned" => $assigned,
			"notAssigned" => $notAssigned,
		));
		echo json_encode($response);
	}

	function listDataBasket()
	{
		$this->load->library("mongo_db");
		$this->mongo_db->switch_db('LOAN_campaign_list');
		$months = [];
		for ($i = 0; $i < 30; $i++) {
			$timestamp = strtotime("-$i days");
		    $months[] = array(
		    	"value" => date("Y-m-d", $timestamp),
		    	"text" => date("d-m-Y", $timestamp),
		    );
		}
		
		$list = $this->mongo_db->command(["listCollections"=>1, "authorizedCollections"=> true, "nameOnly"=>true]);
		$list_collections = array_column($list, "name");
		$data = [];
		foreach ($months as $month) {
			$data[$month["value"]] = array("name" => $month["text"]);
			foreach ($list_collections as $name) {
				if(strpos($name, $month["value"])) {
					if(!isset($data[$month["value"]]["items"])) {
						$data[$month["value"]]["items"] = [];
					}
					$data[$month["value"]]["items"][] = array("name" => $name, "type" => "collection");
				}
			}
		}
		$response = array_filter($data, function($v) {
		    return isset($v["items"]);
		});
		echo json_encode(array_values($response));
	}

	function assign() {
		try {
			$collection = $this->sub . "Diallist_detail";
			$request = json_decode(file_get_contents('php://input'), TRUE);
			if(empty($request["members"]) || empty($request["diallist_id"]))
				throw new Exception("Lack of input", 1);

			$members = $request["members"];
			$assign_type = isset($request["type"]) ? $request["type"] : "notAssigned";
			$count = 0;
			switch ($assign_type) {
				case 'notAssigned': default:
					$data = $this->mongo_db->where(['assign'=>['$exists'=>false]])->where_object_id("diallist_id", $request["diallist_id"])->get($collection);
					foreach ($data as $doc) {
						$count++;
						$extension = $members[$count % count($members)];
						$this->mongo_db->where_id($doc["id"])->set(array("assign" => $extension))->update($collection);
					}
					break;
				
				case 'total':
					# code...
					break;
			}
			echo json_encode(array("status" => 1, "message" => "Success assign {$count} @case@"));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function getDialConfig(){
		$this->load->library("mongo_db");
		$config = $this->mongo_db->where("type", $this->sub)->getOne($this->sub . "Dial_config");
		$config = isset($config) ? $config : array('conditionDonotCall' => 40000);
		echo json_encode($config);
	}

	function updateDialConfig(){
		$this->load->library("mongo_db");
		try{
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data['type'] = $this->sub;
			$check =  $this->mongo_db->where('type', $this->sub)->count($this->sub . "Dial_config");
			if($check == 0)
				$result = $this->mongo_db->where('type', $this->sub)->insert($this->sub . "Dial_config", $data);
			else
				$result = $this->mongo_db->where('type', $this->sub)->update($this->sub . "Dial_config",array('$set' => $data));

			echo json_encode(array("status" => 1, "message" => "Success"));
		}
		catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}