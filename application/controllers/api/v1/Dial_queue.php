<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Dial_queue extends WFF_Controller {

	private $collection = "Dial_queue";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
	}

	function createDialQueue($diallist_id){
		try {
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);

			$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
			$cache_name = $diallist_id . "_start_diallist";
			if ($this->cache->get($cache_name)) {
				throw new Exception("On process");
			}
			$this->cache->save($cache_name , TRUE, 30);
	// -1st Spin: Touch Main phone number only 
	// 	Priority rule:
	// 		MAIN PRODUCT
	// 			1)	Overdue 5-9 days last month
	// 			2)	Product code = 401, 701
	// 			3)	Column V – LNJC05 = 1 AND not overdue last month
	// 			4)	Others
	// 		CARD PRODUCT
	// 			1)	Overdue 5-9 days last month
	// 			2)	not overdue last month
	// 			3)	Others
			$diallist = $this->mongo_db->where_id($diallist_id)->getOne("LO_Diallist");

			$group_name = isset($diallist["group_name"]) ? $diallist["group_name"] : "";
			// print_r($diallist['team']);exit;
			$type = isset($diallist['team']) ? $diallist['team'] : '';
			$diallist_id    = new MongoDB\BSON\ObjectId($diallist_id);

			$count = 0;
			switch ($type) {
				case "SIBS": //case "CARD":
					$count = $this->SIBS($diallist_id);
				break;

				case "CARD": //case "CARD":
					$count = $this->CARD($diallist_id);
				break;
				
				default:
					throw new Exception("Can't find type", 1);
					break;
			}

			$this->mongo_db->where_object_id("diallist_id" , $diallist_id)->update_all('LO_Diallist_detail', array('$set' => array('syncDialQueue' => true)));
			echo json_encode(array("status" => 1, "count" => $count));
			$this->cache->delete($cache_name);
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function SIBS($diallist_id){
		$idx 			= 0;
		$arrData		= [];
		$createdAt = $this->mongo_db->date();

//Priority rule 2:
		$diallistDetailPrio2 = $this->mongo_db->select(array(),array('id'))->where(array(
			"diallist_id" 	=> $diallist_id,
			"PRODGRP_ID" 	=> array('$in' => ['401', '701']),
			"syncDialQueue" => array('$exists' => false),
		))->get('LO_Diallist_detail');

		if(!empty($diallistDetailPrio2)):
			foreach ($diallistDetailPrio2 as $key => $value) {

				$checkConditionDoNotCall = $this->conditionDoNotCall($value);
				if($checkConditionDoNotCall == 0) continue;

				$temp 							= [];
				$temp['phone'] 					= $value['phone'];
				$temp['diallist_id'] 			= (isset($value['diallist_id'])) ? (string)$value['diallist_id'] : '';
				$temp['diallistdetail_id'] 		= $value['id'];
				$temp['priority']				= 100;
				$temp['spin'] 					= 1;
				$temp['index'] 					= $idx;
				$temp['rule'] 					= "2_MAIN";
				$temp['createdAt']				= $createdAt;

				$arrData[] 						= $temp;
				$idx++;
			}
			if($arrData) $this->mongo_db->batch_insert('LO_Dial_queue', $arrData);
		endif;

//Priority rule 3:
		$arrData		= [];

		$diallistDetailPrio3 = $this->mongo_db->where(
			array(
				"diallist_id" 	=>$diallist_id,
				"PRODGRP_ID" 	=> array('$nin' => ['401', '701']),
				"installment_type" => array('$in' => [1,'1']),
				"syncDialQueue" => array('$exists' => false),
			)
		)->get('LO_Diallist_detail');

		if(!empty($diallistDetailPrio3)):
			foreach ($diallistDetailPrio3 as $key => $value) {

				$checkConditionDoNotCall = $this->conditionDoNotCall($value);
				if($checkConditionDoNotCall == 0) continue;

				$temp 							= [];
				$temp['phone'] 					= $value['phone'];	
				$temp['diallist_id'] 			=  (string)$value['diallist_id'] ;
				$temp['diallistdetail_id'] 		= $value['id'];
				$temp['priority']				= 100;
				$temp['spin'] 					= 1;
				$temp['index'] 					= $idx;
				$temp['rule'] 					= "3_MAIN";
				$temp['createdAt']				= $createdAt;

				$arrData[] 						= $temp;
				$idx++;
			}
			if($arrData) $this->mongo_db->batch_insert('LO_Dial_queue', $arrData);
		endif;

//Priority rule 4:
		$arrData		= [];
		$diallistDetailPrio4 = $this->mongo_db->where(
			array(
				"diallist_id" 	=>$diallist_id,
				"PRODGRP_ID" 	=> array('$nin' => ['401', '701']),
				"installment_type" => array('$nin' => [1,'1']),
				"syncDialQueue" => array('$exists' => false),
			)
		)->get('LO_Diallist_detail');

		if(!empty($diallistDetailPrio4)):
			foreach ($diallistDetailPrio4 as $key => $value) {

				$checkConditionDoNotCall = $this->conditionDoNotCall($value);
				if($checkConditionDoNotCall == 0) continue;

				$temp 							= [];
				$temp['phone'] 					= $value['phone'];	
				$temp['diallist_id'] 			=  (string)$value['diallist_id'] ;
				$temp['diallistdetail_id'] 		= $value['id'];
				$temp['priority']				= 100;
				$temp['spin'] 					= 1;
				$temp['index'] 					= $idx;
				$temp['rule'] 					= "4_MAIN";
				$temp['createdAt']				= $createdAt;

				$arrData[] 						= $temp;
				$idx++;
			}
			if($arrData) $this->mongo_db->batch_insert('LO_Dial_queue', $arrData);
		endif;
		return $idx + 1;
	}

	function CARD($diallist_id) {

		$idx 			= 0;
		$arrData		= [];
		$createdAt = $this->mongo_db->date();
	//Priority rule 3:

		$diallistDetailPrio4 = $this->mongo_db->where(
			array(
				"diallist_id" 	=> $diallist_id,
				// "syncDialQueue" => false,
				"syncDialQueue" => array('$exists' => false),
			)
		)->get('LO_Diallist_detail');

		if(!empty($diallistDetailPrio4)):
			foreach ($diallistDetailPrio4 as $key => $value) {
				$temp 							= [];
				$temp['phone'] 					= $value['phone'];	
				$temp['diallist_id'] 			=  (string)$value['diallist_id'] ;
				$temp['diallistdetail_id'] 		= $value['id'];
				$temp['priority']				= 100;
				$temp['spin'] 					= 1;
				$temp['index'] 					= $idx;
				$temp['rule'] 					= "3_CARD";
				$temp['createdAt']				= $createdAt;

				$arrData[] 						= $temp;
				$idx++;
			}
			if($arrData) $this->mongo_db->batch_insert('LO_Dial_queue', $arrData);
		endif;
		return $idx + 1;
	}

	private function getTypeCampaignList($collection) {
		if(strpos($collection, 'SIBS') === 0){
			return "SIBS";
		}else if(strpos($collection, 'CARD') === 0){
			return "CARD";
		}else if(strpos($collection, 'WO') === 0){
			return "WO";
		}
	}

	function conditionDoNotCall($data){
		if (isset($data['PRODGRP_ID'])):
			if($data['PRODGRP_ID'] == '103' || $data['PRODGRP_ID'] == '602' || $data['PRODGRP_ID'] == '802')
				return 1;

			$check40k = $data["overdue_amount_this_month"] - $data["advance_balance"];
			if($check40k < 40000){
				
				if($data["installment_type"] == 'n' && $data["outstanding_principal"] == 0){
					return 0;
				}else if($data["installment_type"] != 'n' && $data["outstanding_principal"] > 0){
					return 0;
				}
				return 1;
			}else{
				return 1;
			}

		endif;
	}
} 