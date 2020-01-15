<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$collection = "LO_Cross_sell";
$key_field 	= "contract_no";

// SIBS
$SIBS = $mongo_db->get("LO_LNJC05");

foreach ($SIBS as $doc) {
	$zaccf = $mongo_db->where('account_number', $doc["account_number"])->getOne("LO_ZACCF");

	$temp 						= [];
	// $temp['debt_group'] 		= $doc['group_id'];
	$temp['debt_group'] 		= "SIBS";
	$temp['contract_no'] 		= $doc['account_number'];
	$temp['LIC_NO'] 			= $zaccf['LIC_NO'];
	$temp['principal_amount'] 	= isset($zaccf['W_ORG']) ? $zaccf['W_ORG'] : 0;
	$temp["type"] 				= isset($zaccf['PRODGRP_ID']) ? $zaccf['PRODGRP_ID'] : 'Not Found';
	
	if($temp['type'] != 'Not Found'){
		$product 	= $mongo_db->where('code', $temp["type"])->getOne("LO_Product");
		$temp['prod_name'] 		= isset($product['name']) ? $product['name'] : 'Not Found';
	}

	$queueData = array(
		"collection"	=> $collection,
		"doc"			=> $temp,
		"key_field"		=> $key_field,
		"startTimestamp"=> time()
	);

	$queue->useTube('import')->put(json_encode($queueData));
}

// CARD
$CARD = $mongo_db->get("LO_List_of_account_in_collection");

foreach ($CARD as $doc) {
	$sbv = $mongo_db->where("contract_no", $doc["account_number"])->getOne("LO_SBV");

	if(empty($sbv)){
		// "0028080000007153" => "28080000007153"
		$remove00 	= substr($doc["account_number"], 2);
		$sbv 		= $mongo_db->where("contract_no", $remove00)->getOne("LO_SBV");
	}

	$temp 					= [];
	$temp['contract_no'] 	= $doc["account_number"];

	$ob_principal_sale 		= isset($sbv["ob_principal_sale"]) ? $sbv["ob_principal_sale"] : 0;
	$ob_principal_cash 		= isset($sbv["ob_principal_cash"]) ? $sbv["ob_principal_cash"] : 0;

	$temp['debt_group'] 		= 'CARD';
	$temp['LIC_NO'] 		= $sbv['license_no'];
	$temp['principal_amount'] 	= $ob_principal_sale + $ob_principal_cash;
	$temp["type"] 				= isset($sbv['card_type']) ? $sbv['card_type'] : 'Not Found';

	if($temp['type'] != 'Not Found'){
		$product 	= $mongo_db->where('code', $temp["type"])->getOne("LO_Product");
		$temp['prod_name'] 		= isset($product['name']) ? $product['name'] : 'Not Found';
	}

	$queueData = array(
		"collection"	=> $collection,
		"doc"			=> $temp,
		"key_field"		=> $key_field,
		"startTimestamp"=> time()
	);

	$queue->useTube('import')->put(json_encode($queueData));

}
