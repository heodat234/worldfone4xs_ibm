<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$dial_details = $mongo_db->where(array('createdAt'=> array('$gt' => $midnight), 'from' => array('$regex' => 'CARD')))->get('LO_Diallist_detail');
	foreach ($dial_details as $key => $dial) {
		$sbv = $mongo_db->where('contract_no', $dial['account_number'])->getOne('LO_SBV');
		$ob_principal_sale = isset($sbv['ob_principal_sale']) ? $sbv['ob_principal_sale'] : 0;
		$ob_principal_cash = isset($sbv['ob_principal_cash']) ? $sbv['ob_principal_cash'] : 0;
		$principal_amount =  number_format($ob_principal_sale + $ob_principal_cash);
		echo $dial['account_number'];godown();
		$mongo_db->where_id($dial['id'])->update('LO_Diallist_detail', array('$set' => array('principal_amount' => $principal_amount )));
	}

}

function godown(){
	echo PHP_EOL;
}
