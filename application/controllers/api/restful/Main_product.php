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
			$select = array("account_number", 'PRODGRP_ID', 'RPY_PRD', 'DT_MAT', 'INT_RATE', 'APPROV_LMT', 'TERM_ID', 'B_ADV', 'CUS_SEX', 'F_PDT');
			$response = $this->crud->read($this->collection, $request, $select);

			$productInfo = $this->crud->get(set_sub_collection('Product'));
			$productList = array_column($productInfo, 'name', 'code');
			$finalData = [];

			foreach ($response['data'] as $key => $value) {
				$LNCJ05 = $this->mongo_db->where("account_number", $value["account_number"])->getOne('LO_LNJC05');
				if(!empty($LNCJ05)){
					$value["debt_group"] 					= $LNCJ05["group_id"];
					$value['first_last_payment_default'] 	= $LNCJ05["installment_type"];
					$value['due_date'] 						= date( 'd/m/Y', $LNCJ05["due_date"]);
					$value['overdue_amount'] 				= (int)$LNCJ05["overdue_amount_this_month"] - (int)$LNCJ05['advance_balance'];
					$value['overdue_amount']  				= number_format((double)$value['overdue_amount'], 2);
					$value['overdue_amount']				= str_replace('.00','',(string)$value['overdue_amount']);
					
					$value['outstanding_balance'] 			= number_format((int)$LNCJ05["current_balance"]);
					$value['name_of_store'] 				= $LNCJ05["dealer_name"];
					$value['principal_amount'] 				= number_format((int)$LNCJ05["outstanding_principal"]);
					$value['no_of_overdue_date'] 			= (int)((time() - $LNCJ05["due_date"]) / 86400);
				}else{
					continue;
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
				
				$value['last_payment_date'] = isset($payment_history[0]["payment_date"]) ? $payment_history[0]["payment_date"] : '';
				if($value['last_payment_date'] != '')
					$value['last_payment_date'] = $value['last_payment_date'][0] . $value['last_payment_date'][1] . '/' . $value['last_payment_date'][2] . $value['last_payment_date'][3] .'/' . $value['last_payment_date'][4] . $value['last_payment_date'][5];

				$value['last_payment_amount'] = isset($payment_history[0]["payment_amount"]) ? $payment_history[0]["payment_amount"] : '';
				$value['last_payment_amount'] = number_format((double)$value['last_payment_amount'], 2);

				$value['time_moving']		= $this->paymentCount($value["account_number"]);

				$diallistDetail = $this->mongo_db->where("account_number", $value["account_number"])
				->where("updatedAt",['$exists'=>TRUE])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
				$value['last_action_code'] = isset($diallistDetail['action_code']) ? $diallistDetail['action_code'] : '';
				$last_action_code_date = (!empty($diallistDetail['updatedAt'])) ? date('d/m/Y', $diallistDetail['updatedAt']) : '';
				$value['last_action_code_date'] = $last_action_code_date;
				$value['staff_in_charge'] = isset($diallistDetail['assign']) ? $diallistDetail['assign'] : '';

				$value['officer_id'] = isset($diallistDetail['officer_id']) ? $diallistDetail['officer_id'] : '';
				$first_payment_date = (!empty($value['F_PDT'])) ? DateTime::createFromFormat('dmY', $value['F_PDT']) : '';
				$value['F_PDT'] = (!empty($first_payment_date)) ? $first_payment_date->format('d/m/Y') : '';
				$finalData[] = $value;
			}
			echo json_encode(array('data' => $finalData, 'total' => count($finalData)));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function paymentCount($account_number){
		$count = 0;
		$payment_historys 	= $this->mongo_db->where(array("account_number" => $account_number))->get('LO_Payment_history');
		foreach ($payment_historys as $i_key => $i_payment) {
			if($i_payment['"payment_amount"'] <= $i_payment['overdue_amount']) continue;

			if(gettype($i_payment['payment_date']) == 'string'){
                    // 271219 -> 27/12/2019
				$payment_date = $i_payment['payment_date'];
				$newstr = substr_replace($payment_date, $this_year, 4, 0);
				$newstr = substr_replace($newstr, "/", 2, 0);
				$newstr = substr_replace($newstr, "/", 5, 0);

				$dt = DateTime::createFromFormat('d/m/Y', $newstr);
				$payment_date_timestamp = $dt->getTimestamp();

				$overdue_days = (int)($i_payment['due_date'] - $payment_date_timestamp) / 86400;
			}else{
				$overdue_days = (int)($i_payment['due_date'] - $i_payment['payment_date']) / 86400;
			}
			if($overdue_days >= 10) $count++;
		}

		return $count;
	}
}