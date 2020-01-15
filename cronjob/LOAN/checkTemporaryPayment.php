<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$money = $mongo_db->where('type', 'LO_')->getOne('LO_Dial_config');
$money = isset($money["conditionDonotCall"]) ? $money["conditionDonotCall"] : 40000;

$collection 	= "LO_Temporary_payment";
$key_field 		= "account_number";

$LO_Temporary_payment 	= $mongo_db->where(array('processed' => array('$exists' => false)))->get($collection);

foreach ($LO_Temporary_payment as $doc) { 
	if(isset($doc['remain_amount'])){

		if($doc['remain_amount'] < $money){

			$dial_detail = $mongo_db->where("account_number", $doc['account_number'])->order_by(array('_id' => -1))->get('LO_Diallist_detail');
			if(count($dial_detail) > 0){

				$dial_detail = $dial_detail[0];
				$check = conditionDoNotCall($dial_detail);
				if($check){
					$mongo_db->where_id($dial_detail['id'])->update('LO_Diallist_detail', array('$set' => array('Donotcall' => 'Y')));
					$mongo_db->where("diallistdetail_id", $dial_detail['id'])->delete_all('LO_Dial_queue');
				}
			}
		}
	}

	$mongo_db->where_id($doc['id'])->update($collection, array('$set' => array('processed' => true)));
}

function conditionDoNotCall($data){
    if (isset($data['PRODGRP_ID'])):
        if($data['PRODGRP_ID'] == '103' || $data['PRODGRP_ID'] == '602' || $data['PRODGRP_ID'] == '802')
            return false;

        if($data["installment_type"] == 'n' && $data["outstanding_principal"] == 0){
        	return true;
        }else if($data["installment_type"] != 'n' && $data["outstanding_principal"] > 0){
        	return true;
        }

    endif;
}