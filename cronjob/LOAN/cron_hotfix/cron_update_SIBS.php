<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$dial_details = $mongo_db->where(array('createdAt'=> array('$gt' => $midnight), 'from' => array('$regex' => 'SIBS')))->get('LO_Diallist_detail');
	foreach ($dial_details as $key => $dial) {
		$LNCJ05 = $mongo_db->where("account_number", $dial["account_number"])->order_by(array("_id" => -1))->getOne('LO_LNJC05');
		if(isset($LNCJ05['outstanding_principal'])){
			echo $key;
			godown();
			$mongo_db->where_id($dial['id'])->update('LO_Diallist_detail', array('$set' => array('principal_amount' => number_format((int)$LNCJ05["outstanding_principal"]) )));
		}
	}

}

function godown(){
	echo PHP_EOL;
}
