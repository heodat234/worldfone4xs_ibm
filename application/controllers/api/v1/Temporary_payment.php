<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Temporary_payment extends WFF_Controller {

	private $collection = "Temporary_payment";

	function __construct()
    {
    	parent::__construct();
        header('Content-type: application/json');
		$this->load->library("crud");
		$this->load->library("mongo_db");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
    }

    function read() {
    	try {
			$request = json_decode($this->input->get("q"), TRUE);
			$response = $this->crud->read($this->collection, $request);
			if(!empty($response)) {
				foreach($response['data'] as $key => &$value) {
					$mainInfo = $this->mongo_db->where(array('account_number' => $value['account_number']))->getOne(set_sub_collection('ZACCF'));
					if(!empty($mainInfo)) {
						$value['type'] = 'ZACCF';
						$debtInfo = $this->mongo_db->where(array('account_number' => $value['account_number']))->getOne(set_sub_collection('LNJC05'));
						$value['due_date'] = $debtInfo['due_date'];
						$value['overdue_amount'] = $debtInfo['overdue_amount_this_month'];
						
					}
					else {
						$mainInfo = $this->mongo_db->where(array('contract_no' => $value['account_number']))->getOne(set_sub_collection('SBV'));
						if(!empty($mainInfo)) {
							$value['type'] = 'SBV';
							$debtInfo = $this->mongo_db->where(array('account_number' => $value['account_number']))->getOne(set_sub_collection('List_of_account_in_collection'));
							$value['due_date'] = $debtInfo['due_date'];
							$value['overdue_amount'] = $debtInfo['overdue_amt'];
						}
					}
					$value['payment_amount'] = $value['amt'];
					$value['remain_amount'] = (!empty($value['overdue_amount'])) ? $value['overdue_amount'] - $value['payment_amount'] : 0;
					$value['payment_date'] = $value['created_at'];
				}
			}
	    	echo json_encode($response);
    	} catch(Exception $e) {
    		echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    	}
    }
}