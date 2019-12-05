<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Diallist_detail extends WFF_Controller {

	private $collection = "Diallist_detail";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
	}

	function insertFromBasket()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$collection = $this->input->get("collection");
			$diallist_id = $this->input->get("diallist_id");

			$index = $this->mongo_db->where_object_id("diallist_id", $diallist_id)->count($this->collection);

			if(strpos($collection, 'SIBS') === 0 || strpos($collection, 'CARD') === 0 || strpos($collection, 'WO') === 0 ):
				$count = $this->importFrom_Loan_campaign_list($collection, $diallist_id, $index);

				$type = $this->getTypeCampaignList($collection);

				echo json_encode(array("status" => 1, "message" => $count));
				return true;
			endif;
			
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function importFrom_Loan_campaign_list($collection, $diallist_id, $index) {

		$this->mongo_db->switch_db('LOAN_campaign_list');

		$i 				= 0;
		$diallist_id 	= new MongoDB\BSON\ObjectId($diallist_id);
		$phoneField 	= $this->getPhoneField($collection);
		$data 			= $this->mongo_db->get($collection);

		for($i=0; $i < count($data); $i++){

			$data[$i]["diallist_id"] = $diallist_id;
			$data[$i]["createdBy"] = $this->session->userdata("extension");
			$data[$i]["index"] = ++$index;
			if(isset($data[$i][$phoneField])) {
				$data[$i]["phone"] = ($data[$i][$phoneField][0] == '0') ? (string) $data[$i][$phoneField] : '0'. $data[$i][$phoneField];
			}

		}
		$this->mongo_db->switch_db('worldfone4xs');
		$this->mongo_db->where('_id', $diallist_id)->update(getCT('Diallist'), array('$set' => array('loan_campaign_name' => $collection)));
		$this->mongo_db->batch_insert($this->collection, $data);
		return $i;

	}

	function getPhoneField($collection){
		if(strpos($collection, 'SIBS') === 0){
			return "mobile_num";
		}else if(strpos($collection, 'CARD') === 0){
			return "phone";
		}else if(strpos($collection, 'WO') === 0){
			return "PHONE";
		}
	}

	function getTypeCampaignList($collection) {
		if(strpos($collection, 'SIBS') === 0){
			return "SIBS";
		}else if(strpos($collection, 'CARD') === 0){
			return "CARD";
		}else if(strpos($collection, 'WO') === 0){
			return "WO";
		}
	}

	function update($id)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			$this->sortCall($id, $data);
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	private function sortCall($diallistdetail_id, $data)
	{	
		$diallist = $this->mongo_db->where_id($data["diallist_id"])->getOne(getCT("Diallist"));

		if(!$diallist || !isset($diallist["team"])) return;

		if(isset($diallist["mode"]) && $diallist["mode"] == "auto") {

			if(isset($data["action_code"])) 
			{
				$dialQueueCollection = getCT("Dial_queue");
				$dialQueue = $this->mongo_db->where(array(
					"diallistdetail_id"	=> $diallistdetail_id,
					"spin"				=> 1,
				))->getOne($dialQueueCollection);

				if(!$dialQueue) return;

				$diallistDetail = $this->mongo_db->where_id($diallistdetail_id)->getOne(getCT("Diallist_detail"));

				if(!$diallistDetail || !isset($diallistDetail["spin"])) return;
				if($diallistDetail["spin"] == 1) {

					$dialQueue["spin"] = 2;
					$dialQueue["createdAt"] = $this->mongo_db->date();
					if($diallist["team"] == "SIBS") {

						if($data["action_code"] == "CHECK") {
							$dialQueue["priority"] = 100;
						} elseif(isset($data["PRODGRP_ID"]) && in_array($data["PRODGRP_ID"], ["401","701","103"])) {
							$dialQueue["priority"] = 200;
						} elseif(isset($data["installment_type"]) && $data["installment_type"] == "n") {
							$dialQueue["priority"] = 300;
							$action_arr = ['LOC','NOT','BPTP','PTP','LM'];
							$idx = array_search($data["action_code"], $action_arr);
							if($idx !== FALSE) {
								$dialQueue["priority"] += $idx * 10;
							} else {
								$dialQueue["priority"] += count($action_arr) * 10;
							}
						} else {
							$dialQueue["priority"] = 400;
							$action_arr = ['LOC','NOT'];
							$idx = array_search($data["action_code"], $action_arr);
							if($idx !== FALSE) {
								$dialQueue["priority"] += $idx * 10;
							} else {
								$dialQueue["priority"] += count($action_arr) * 10;
							}
						}

					} elseif($diallist["team"] == "CARD") {
						$dialQueue["priority"] = 100;
						$action_arr = ['CHECK','LOC','NOT','BPTP','PTP','LM'];
						$idx = array_search($data["action_code"], $action_arr);
						if($idx !== FALSE) {
							$dialQueue["priority"] += $idx * 10;
						} else {
							$dialQueue["priority"] += count($action_arr) * 10;
						}
					}
					// Clear. TH nhan dup hoac thay doi 
					$this->mongo_db->where(array(
						"diallistdetail_id"	=> $diallistdetail_id,
						"spin"				=> 2,
					))->delete_all($dialQueueCollection);
					// Create

					$this->mongo_db->where_id($diallistdetail_id)->set("priority", $dialQueue["priority"])->update(getCT("Diallist_detail"));

					if(empty($data["reCall"]))  {
						// Tao spin 2
						unset($dialQueue["id"], $dialQueue["called"], $dialQueue["calledAt"]);
						$this->mongo_db->insert($dialQueueCollection, $dialQueue);

						// House_NO

						if(!empty($diallistDetail["House_NO"]) && strlen($diallistDetail["House_NO"]) > 7) {
							$dialQueue["phone"] = $diallistDetail["House_NO"];
							$dialQueue["index"]++;
							$this->mongo_db->insert($dialQueueCollection, $dialQueue);
						}

						// REFERENCE

						if(!empty($diallistDetail["LIC_NO"])) {
							$REFS = $this->mongo->where("LIC_NO", $diallistDetail["LIC_NO"])->get(getCT("Relationship"));
							foreach ($REFS as $doc) {
								if(!empty($doc["phone"]) && strlen($doc["phone"]) > 7) {
									$dialQueue["phone"] = $doc["phone"];
									$dialQueue["index"]++;
									$this->mongo_db->insert($dialQueueCollection, $dialQueue);
								}
							}
						}
					}
				}
			}

		} else {

			// MANUAL

			$priority = 100;
			$action_arr = ['CHECK','LOC','NOT','BPTP','PTP','LM'];
			$idx = array_search($data["action_code"], $action_arr);
			if($idx !== FALSE) {
				$priority += $idx * 10;
			} else {
				$priority += count($action_arr) * 10;
			}

			$this->mongo_db->where_id($diallistdetail_id)->set("priority", $priority)->update(getCT("Diallist_detail"));
		}
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		
		if(!empty($response)) {
			$reportReleaseSale = $this->crud->where(array('account_number' => $response['account_number']))->getOne($this->sub . 'Report_release_sale', array('temp_address', 'address'));
			$cus_birthday = (!empty($response['BIR_DT8'])) ? DateTime::createFromFormat('dmY', $response['BIR_DT8']) : null;
			$response['cus_birthday'] = (!empty($cus_birthday)) ? $cus_birthday->format('d/m/Y') : '';
			if(empty($response['temp_address'])) {
				$response['temp_address'] = (!empty($reportReleaseSale['temp_address'])) ? $reportReleaseSale['temp_address'] : '';
			}
			if(empty($response['permanent_address'])) {
				$response['permanent_address'] = (!empty($reportReleaseSale['address'])) ? $reportReleaseSale['address'] : '';
			}
		}                        
		echo json_encode($response);
	}
}