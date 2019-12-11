<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Card extends WFF_Controller {

	private $collection = "SBV";

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
			$select = array("contract_no", 'interest_rate', 'approved_limit', 'open_card_date', 'ob_principal_sale', 'ob_principal_cash', 'expiry_date', 'overdue_indicator');

			$response = $this->crud->read($this->collection, $request, $select);
			foreach ($response['data'] as $key => &$value) {
				$ob_principal_sale = isset($value['ob_principal_sale']) ? $value['ob_principal_sale'] : 0;
				$ob_principal_cash = isset($value['ob_principal_cash']) ? $value['ob_principal_cash'] : 0;
				$value['principal_amount'] =  number_format($ob_principal_sale + $ob_principal_cash);

				$first_released_date = DateTime::createFromFormat('dmY', $value['open_card_date']);
				$value['first_released_date'] 	= $first_released_date->format('d/m/Y');
				$value['interest_rate'] = ((double) $value['interest_rate']) * 100 . "%";
				$value['approved_limit'] = number_format($value['approved_limit']);

				$listofacc = $this->mongo_db->where("account_number", $value["contract_no"])->getOne('LO_List_of_account_in_collection');
				if(!empty($listofacc)){
					$value['due_date'] 						= date('d/m/Y', $listofacc["overdue_date"]);
					$value['no_of_overdue_date'] 			= floor((int)((time() - $listofacc["overdue_date"])) / 86400);
					$value['overdue_amount'] 				= number_format($listofacc["overdue_amt"]);
					$value['outstanding_balance'] 			= number_format($listofacc["cur_bal"]);
					$check = (int)date('d', $listofacc["overdue_date"]);
					if($check >= 12 && $check <= 21){
						$kydue = '01';
					}else if($check >= 22 && $check <= 27){
						$kydue = '02';
					}else{
						$kydue = '03';
					}
					$value["debt_group"] = $value['overdue_indicator'] . '-' . $kydue;
				}else{
					unset($response['data'][$key]);
					continue;
					$value['due_date'] 						= '';
					$vaue['no_of_overdue_date'] 			= '';
					$value['overdue_amount'] 				= '';
					$value['outstanding_balance'] 			= '';
				}
				
				$report_release_sale 		= $this->mongo_db->where("account_number", $value["contract_no"])->getOne('LO_Report_release_sale');
				$sale_consultant_name 		= isset($report_release_sale['sale_consultant_name']) ? $report_release_sale['sale_consultant_name'] : '';
				$sale_consultant_code 		= isset($report_release_sale['sale_consultant_code']) ? $report_release_sale['sale_consultant_code'] : '';
				$value['sale_consultant'] 	= $sale_consultant_code . ' - ' . $sale_consultant_name;

				$payment_history = $this->mongo_db->where("account_number", $value["contract_no"])->order_by(array('_id' => -1))->get('LO_Payment_history');
				$value['last_payment_date'] = isset($payment_history[0]["payment_date"]) ? date('d-m-Y', (int)$payment_history[0]["payment_date"]) : ' ';
				$value['last_payment_amount'] = isset($payment_history[0]['payment_amount']) ? number_format($payment_history[0]['payment_amount']) : '';

				$appear_in_payment_history 	= $this->mongo_db->where(array("account_number" => $value["contract_no"], "createdAt" => array('$gt' => time() - 10 * 86400)))->count('LO_Payment_history');
				$value['time_moving']		= $appear_in_payment_history;

				$diallistDetail = $this->mongo_db->where("account_number", $value["contract_no"])->order_by(array("updatedAt" => -1))->get('LO_Diallist_detail');
				$value['last_action_code'] = isset($diallistDetail[0]['action_code']) ? $diallistDetail[0]['action_code'] : '';
				$value['last_action_code_date'] = isset($diallistDetail[0]["updatedAt"]) ? date('d-m-Y', $diallistDetail[0]["updatedAt"]) : '';
				$value['staff_in_charge'] = isset($diallistDetail[0]['assign']) ? $diallistDetail[0]['assign'] : '';
				$value['expiry_date']		= $value['expiry_date'][2] . $value['expiry_date'][3] . '/' . $value['expiry_date'][4] . $value['expiry_date'][5] . $value['expiry_date'][6] . $value['expiry_date'][7];

			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}