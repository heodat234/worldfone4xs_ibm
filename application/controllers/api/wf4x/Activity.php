<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Activity extends CI_Controller {

	private $collection = "Activity";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
	}

	function moveToLog()
	{
		try {
			$time = time(); //86400
			$data = $this->mongo_db->limit(10)
    		->where(array("createdAt" => array('$lt' => $time - 3600)))->get($this->collection);
    		if($data) {
    			foreach ($data as $doc) {
	                $id = $doc["id"];
	                unset($doc["id"]);
	                $this->mongo_db->insert($this->collection . "_log", $doc);
	                $this->mongo_db->where_id($id)->delete($this->collection);
	            }
    		}
			echo json_encode(array("status" => 1, "count" => count($data)));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}