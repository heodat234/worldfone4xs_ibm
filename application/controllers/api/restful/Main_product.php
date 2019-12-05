<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Main_product extends WFF_Controller {

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
			$select = array("account_number", 'PRODGRP_ID', 'RPY_PRD', 'DT_MAT', 'INT_RATE', 'APPROV_LMT', 'TERM_ID', 'B_ADV');
			$response = $this->crud->read($this->collection, $request, $select);

			$productInfo = $this->crud->get(set_sub_collection('Product'));
			$productList = array_column($productInfo, 'name', 'code');

			foreach ($response['data'] as $key => &$value) {
				$LNCJ05 = $this->mongo_db->where("account_number", $value["account_number"])->getOne('LO_LNJC05');
				if(!empty($LNCJ05)){
					$value["debt_group"] 					= $LNCJ05["group_id"];
					$value['first_last_payment_default'] 	= $LNCJ05["installment_type"];
					$value['due_date'] 						= date( 'd/m/Y', $LNCJ05["due_date"]);
					$value['overdue_amount'] 				= number_format((int)$LNCJ05["overdue_amount_this_month"]);
					$value['outstanding_balance'] 			= number_format((int)$LNCJ05["current_balance"]);
					$value['name_of_store'] 				= $LNCJ05["dealer_name"];
					$value['principal_amount'] 				= number_format((int)$LNCJ05["outstanding_principal"]);
					$value['no_of_overdue_date'] 			= (int)((time() - $LNCJ05["due_date"]) / 86400);
				}else{
					$value["debt_group"] = $value['first_last_payment_default'] = $value['due_date'] = $value['overdue_amount'] = $value['outstanding_balance'] = $value['name_of_store'] = $value['principal_amount'] = $value['no_of_overdue_date'] = '';
				}
				
				$report_release_sale 		= $this->mongo_db->where("account_number", $value["account_number"])->getOne('LO_Report_release_sale');
				$sale_consultant_name	 	= isset($report_release_sale['sale_consultant_name']) ? $report_release_sale['sale_consultant_name'] : '';
				$sale_consultant_code 		= isset($report_release_sale['sale_consultant_code']) ? $report_release_sale['sale_consultant_code'] : '';
				$value['sale_consultant'] 	= $sale_consultant_code . ' - ' . $sale_consultant_name;

				$value['product_name'] 		= (!empty($value['PRODGRP_ID'])) ? $productList[$value['PRODGRP_ID']] : ''; 
				$value['monthy_amount'] 	= number_format((int) $value['RPY_PRD']);
				$maturity_date 				= DateTime::createFromFormat('dmY', $value['DT_MAT']);
				$value['maturity_date'] 	= $maturity_date->format('d/m/Y'); 
				$value['interest_rate'] 	= ((double) $value['INT_RATE']) * 100 . "%";
				$value['approved_limit'] 	= number_format((int) $value['APPROV_LMT']);
				$value['term'] 				= $value['TERM_ID'];
				$value['advance_money'] 	= number_format((int) $value['B_ADV']);

				$payment_history = $this->mongo_db->where("account_number", $value["account_number"])->order_by(array('_id' => -1))->get('LO_Payment_history');
				// $last_payment_date = (!empty($payment_history[0]['date'])) ? DateTime::createFromFormat('dmY', $value['DT_MAT']);
				$value['last_payment_date'] = isset($payment_history[0]['date']) ? $payment_history[0]['date'] : '';
				$value['last_payment_amount'] = isset($payment_history[0]["payment_amount"]) ? $payment_history[0]["payment_amount"] : '';

				$diallistDetail = $this->mongo_db->where("account_number", $value["account_number"])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
				$value['last_action_code'] = isset($diallistDetail['action_code']) ? $diallistDetail['action_code'] : '';
				$last_action_code_date = (!empty($diallistDetail['updatedAt'])) ? date('d/m/Y', $diallistDetail['updatedAt']) : '';
				$value['last_action_code_date'] = $last_action_code_date;
				$value['staff_in_charge'] = isset($diallistDetail['assign']) ? $diallistDetail['assign'] : '';
				$first_payment_date = (!empty($value['F_PDT'])) ? DateTime::createFromFormat('dmY', $value['F_PDT']) : '';
				$value['first_payment_date'] = (!empty($first_payment_date)) ? $first_payment_date->format('d/m/Y') : '';
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}