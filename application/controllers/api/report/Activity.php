<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Activity extends WFF_Controller {

	private $collection = "Activity_log";
	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection();
		$this->load->library("crud");
		$this->load->model("language_model");
	}

	function read()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$response = $this->crud->read($this->collection, $request, ["extension","agentname","directory","class","function","method", "uri", "ajaxs", "createdAt"]);
			foreach ($response["data"] as &$doc) {
				$where = array();
				foreach (["directory","class","function","method"] as $field) {
					$where[$field] = $doc[ $field ];
				}
				$definitionDoc = $this->mongo_db->where($where)->getOne("Activity_definition");
				if($definitionDoc) 
				{
					$doc["definition"] = $definitionDoc["definition"];
				} 
				else 
				{
					$navDoc = $this->mongo_db->where(array(
						"uri" => array('$in' => [$doc["uri"], $doc["uri"]."/"]), 
						"visible" => TRUE
					))->getOne($this->sub . "Navigator");
					$doc["definition"] = "@Access@ @page@ " . ($navDoc ? $navDoc["name"] : $doc["uri"]);
				}
				if(!empty($doc["ajaxs"])) {
					foreach ($doc["ajaxs"] as &$ajax) {
						$ajax = (array) $ajax;
						$where = array();
						if($ajax["directory"] == "template") 
						{
							$where["uri"] = $ajax["uri"];
						} 
						else 
						{
							foreach (["directory","class","function","method"] as $field) {
								$where[$field] = $ajax[ $field ];
							}
						}
						$definitionDoc = $this->mongo_db->where($where)->getOne("Activity_definition");
						if($definitionDoc) {
							$ajax["definition"] = $definitionDoc["definition"];
						} else $ajax["definition"] = ucfirst($ajax["method"]) . " @page@ " . $ajax["uri"];
					}
				}
			}
			$response = $this->language_model->translate($response,  "SIDEBAR");
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}