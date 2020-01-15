<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Cross_sell extends WFF_Controller {

	private $collection = "ZACCF";
    private $collection2 = "SBV";
    private $SBV_group = ['','A','B','C','D','E','F'];

	function __construct()
    {
    	parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
        $this->collection2 = $this->sub . $this->collection2;
    }

    function read() {
    	try {
            $temp = [];
	    	$request = json_decode($this->input->get("q"), TRUE);
	    	$responseZacff = $this->crud->read($this->collection, $request);

            foreach ($responseZacff['data'] as $key => &$value) {
                $debt_group = $this->mongo_db->where('account_number', $value['account_number'])->getOne('LO_LNJC05');
                $debt_group = isset($debt_group['group_id']) ? $debt_group['group_id'] : '';
                $value['debt_group']         = $debt_group;
                $value['contract_no']        = $value['account_number'];
                $value['LIC_NO']             = $value['LIC_NO'];
                $value['prod_name']          = 'SIBS';
                $value['principal_amount']   = isset($value['W_ORG']) ? (int) $value['W_ORG'] : 0;
                $value['B_ADV']              = isset($value['B_ADV']) ? (int) $value['B_ADV'] : 0;
                $value["type"]               = isset($value['PRODGRP_ID']) ? $value['PRODGRP_ID'] : '';
            }

            $request['filter']['filters'][0]['field'] = 'license_no';
            $responseSBV = $this->crud->read($this->collection2, $request);

            foreach ($responseSBV['data'] as $key => &$value) {
                $debt_group = $this->mongo_db->where('account_number', $value['contract_no'])->getOne('LO_List_of_account_in_collection');
                if(count($debt_group) > 0){
                    $check = (int)date('d', $debt_group["overdue_date"]);
                    if($check >= 12 && $check <= 21){
                        $kydue = '01';
                    }else if($check >= 22 && $check <= 27){
                        $kydue = '02';
                    }else{
                        $kydue = '03';
                    }
                    $value["debt_group"] = $value['overdue_indicator'] . '-' . $kydue;
                }else{
                    $value["debt_group"] = '';
                }
                $value['prod_name']          = 'CARD';
                $ob_principal_sale          = isset($value["ob_principal_sale"]) ? $value["ob_principal_sale"] : 0;
                $ob_principal_cash          = isset($value["ob_principal_cash"]) ? $value["ob_principal_cash"] : 0;

                $value['LIC_NO']             = $value['license_no'];
                $value['principal_amount']   = $ob_principal_sale + $ob_principal_cash;
                $value["type"]               = isset($value['card_type']) ? $value['card_type'] : '';
            }

            $finalResponse['data'] = array_merge($responseZacff['data'], $responseSBV['data']);
            $finalResponse['total'] = $responseZacff['total'] + $responseSBV['total'];
	    	echo json_encode($finalResponse);
    	} catch(Exception $e) {
    		echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    	}
    }
}