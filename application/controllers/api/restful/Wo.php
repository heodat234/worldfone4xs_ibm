<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Wo extends WFF_Controller {

	private $collection = "ZACCF";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function read()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$select = array("account_number", 'PRODGRP_ID', 'RPY_PRD', 'DT_MAT', 'INT_RATE', 'APPROV_LMT', 'TERM_ID', 'B_ADV', 'CUS_SEX');
			$response = $this->crud->read($this->collection, $request, $select);

			$productInfo = $this->crud->get(set_sub_collection('Product'));
			$productList = array_column($productInfo, 'name', 'code');

			if($response['total'] == 0){
				$request['filter']['filters'][0]['field'] = 'LICNO';
				// print_r($request);
				$response = $this->crud->read('LO_WO_monthly', $request);
				foreach ($response['data'] as $key => &$value) {
					$value['account_number'] = $value['ACCTNO'];
				}
				echo json_encode($response);
				return;
			}

			foreach ($response['data'] as $key => &$value) {
				$wo = $this->mongo_db->where("ACCTNO", $value["account_number"])->getOne('LO_WO_monthly');
				if(!empty($wo)){
					foreach ($wo as $key => $value2) {
						$value[$key] = $value2;
					}
				}else{
					unset($response['data'][$key]);
					continue;
				}
				$value['PROD_ID'] = (!empty($value['PROD_ID'])) ? $productList[$value['PROD_ID']] : '';
				
		
				if(!empty($value['CUS_SEX'])) {
					$value['CUS_SEX']		= ($value['CUS_SEX'] == 0) ? 'NỮ' : 'NAM';
				} 

				$diallistDetail = $this->mongo_db->where("account_number", $value["account_number"])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
				$value['last_action_code'] = isset($diallistDetail['action_code']) ? $diallistDetail['action_code'] : '';
				$last_action_code_date = (!empty($diallistDetail['updatedAt'])) ? date('d/m/Y', $diallistDetail['updatedAt']) : '';
				$value['last_action_code_date'] = $last_action_code_date;
				$value['staff_in_charge'] = isset($diallistDetail['assign']) ? $diallistDetail['assign'] : '';
				$first_payment_date = (!empty($value['F_PDT'])) ? DateTime::createFromFormat('dmY', $value['F_PDT']) : '';
				$value['first_payment_date'] = (!empty($first_payment_date)) ? $first_payment_date->format('d/m/Y') : '';
				// print_r($value);
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}