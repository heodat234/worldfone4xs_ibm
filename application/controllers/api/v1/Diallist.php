<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallist extends CI_Controller {

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
		$total = $this->mongo_db->where_object_id("diallist_id", $diallist_id)->count($collection);
		$assigned = $this->mongo_db->where_object_id("diallist_id", $diallist_id)->where(array("assign" => ['$exists' => true]))->count($collection);
		$notAssigned = $this->mongo_db->where_object_id("diallist_id", $diallist_id)->where(array("assign" => ['$exists' => false]))->count($collection);
		$response = array(
			"total" => $total,
			"assigned" => $assigned,
			"notAssigned" => $notAssigned
		);
		echo json_encode($response);
	}

	function listDataBasket()
	{
		$this->load->library("mongo_db");
		$months = [];
		for ($i = 0; $i < 12; $i++) {
		    $months[] = array(
		    	"value" => date("Y-m", strtotime( date( 'Y-m-01' )." -$i months")),
		    	"text" => date("m-Y", strtotime( date( 'Y-m-01' )." -$i months")),
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
}