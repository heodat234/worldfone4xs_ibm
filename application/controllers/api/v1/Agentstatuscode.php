<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agentstatuscode extends CI_Controller {

	private $collection = "Agent_status_code";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->model("language_model");
		$this->collection = set_sub_collection($this->collection);
	}

	function index()
	{
		try {
			$this->load->library("mongo_db");
			$data = $this->mongo_db->where("active", true)->get($this->collection);
			echo json_encode($data);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function get_by_value($value)
	{
		try {
			$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
			$cache_name = $this->collection . "_" . $value;
			if (!$response = $this->cache->get($cache_name)) {
				$this->load->library("mongo_db");
				$response = $this->mongo_db->where("value", (int) $value)->select([],["_id"])->getOne($this->collection);
				$this->cache->save($cache_name , $response, $this->config->item("wff_time_cache"));
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function update($id)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			$this->load->library("crud");
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function init()
	{
		try {
			$this->load->library("mongo_db");
			$data = array(
				array("value" => 0, "text" => "Softphone Unvailable", "code" => "SUN", "active" => true, "iconClass" => "gi gi-ban text-muted"),
				array("value" => 1, "text" => "Available", "code" => "AVA", "active" => true, "iconClass" => "gi gi-headset text-success"),
				array("value" => 2, "text" => "Softphone oncall", "code" => "SOC", "active" => true, "iconClass" => "gi gi-earphone text-primary"),
				array("value" => 3, "text" => "Block", "code" => "UNV", "active" => true, "iconClass" => "gi gi-airplane text-danger", "sub" => ["Other"]),
				array("value" => 4, "text" => "After call work", "code" => "ACW", "active" => true, "iconClass" => "gi gi-briefcase text-warning", "sub" => ["Other"]),
			);
			$resultAll = 1;
			foreach ($data as $doc) {
				$check = $this->mongo_db->where(array("value" => $doc["value"]))->getOne($this->collection);
				if(!$check) {
					$result = $this->mongo_db->insert($this->collection, $doc);
					if(!$result) $resultAll = 0;
				}
			}
			echo json_encode(array("status" => $resultAll ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}