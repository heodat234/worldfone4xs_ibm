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
			$finalData = [];
			foreach ($response['data'] as $key => $value) {
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
					$sbv_stored = $this->mongo_db->where('contract_no', $listofacc['account_number'])->getOne('LO_SBV_Stored');
					if(!empty($sbv_stored)){
						if(isset($sbv_stored['overdue_indicator'])){
							$value['overdue_indicator'] = $sbv_stored['overdue_indicator'];
						}
					}
					$value["debt_group"] = $value['overdue_indicator'] . '-' . $kydue;
				}else{
					continue;
				}
				
				$report_release_sale 		= $this->mongo_db->where("account_number", $value["contract_no"])->getOne('LO_Report_release_sale');
				$sale_consultant_name 		= isset($report_release_sale['sale_consultant_name']) ? $report_release_sale['sale_consultant_name'] : '';
				$sale_consultant_code 		= isset($report_release_sale['sale_consultant_code']) ? $report_release_sale['sale_consultant_code'] : '';
				$value['sale_consultant'] 	= $sale_consultant_code . ' - ' . $sale_consultant_name;

				$payment_history = $this->mongo_db->where("account_number", $value["contract_no"])->order_by(array('_id' => -1))->get('LO_Payment_history');

				$value['last_payment_date'] = isset($payment_history[0]["payment_date"]) ? $payment_history[0]["payment_date"] : '';

				$value['last_payment_date'] = $value['last_payment_date'][0] . $value['last_payment_date'][1] . '/' . $value['last_payment_date'][2] . $value['last_payment_date'][3] .'/' . $value['last_payment_date'][4] . $value['last_payment_date'][5];

				$value['last_payment_amount'] = isset($payment_history[0]['payment_amount']) ? number_format($payment_history[0]['payment_amount']) : '';
				
				$value['time_moving']		= $this->paymentCount($value["contract_no"]);

				$diallistDetail = $this->mongo_db->where("account_number", $value["contract_no"])->where("updatedAt",['$exists'=>TRUE])->order_by(array("_id" => -1))->get('LO_Diallist_detail');
				$value['last_action_code'] = isset($diallistDetail[0]['action_code']) ? $diallistDetail[0]['action_code'] : '';
				$value['last_action_code_date'] = isset($diallistDetail[0]["updatedAt"]) ? date('d-m-Y', $diallistDetail[0]["updatedAt"]) : '';
				$value['staff_in_charge'] = isset($diallistDetail[0]['assign']) ? $diallistDetail[0]['assign'] : '';
				$value['officer_id'] = isset($diallistDetail[0]['officer_id']) ? $diallistDetail[0]['officer_id'] : '';
				$value['expiry_date']		= $value['expiry_date'][2] . $value['expiry_date'][3] . '/' . $value['expiry_date'][4] . $value['expiry_date'][5] . $value['expiry_date'][6] . $value['expiry_date'][7];
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
				$newstr = substr_replace($newstr, "/",5, 0);

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