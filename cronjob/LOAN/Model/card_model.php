<?php 
$mongo_db = new Mongo_db();

function getGroupMappingCampaign(){
	$array = array(
		'debt_type' => array('$regex' => 'card', '$options' => 'i'),
		'debt_group' => 'Group A'
	);
	$group_mapping = mongoGetCustom($array, 'LO_Group_mapping_campaign');

	return $group_mapping;
}

function getCusAssignPartner(){
	global $mongo_db;
	$arr_contractNo_partner = [];
	$cus_assigned_partner   = $mongo_db->select(array("CONTRACTNR"))->get('LO_Cus_assigned_partner');
	foreach ($cus_assigned_partner as $key => $value) {
		$arr_contractNo_partner[] = $value['CONTRACTNR'];
	}
	return $arr_contractNo_partner;
}

function getListOfAccount(){
	global $mongo_db;
	$data = $mongo_db->order_by(array('_id' => 1))->get('LO_List_of_account_in_collection');
	return $data;
}

function getSBV($contract_no, $kydue){
	global $mongo_db;

	$where 		= array('contract_no' => $contract_no, 'kydue' => $kydue);
	$SBV_Stored = mongoGetOne_Custom($where, 'LO_SBV_Stored');
	$SBV      	= mongoGetOne("contract_no", $contract_no, 'LO_SBV');

	if(empty($SBV)){
		$temp   = substr($contract_no,2);
		$SBV    = mongoGetOne("contract_no", $temp, 'LO_SBV');
		
		$where 		= array('contract_no' => $temp, 'kydue' => $kydue);
		$SBV_Stored = mongoGetOne_Custom($where, 'LO_SBV_Stored');
	}else{
		$SBV['license_no'] = trim($SBV['license_no']);
	}
	if(!empty($SBV) && !empty($SBV_Stored)){
		$SBV["overdue_indicator"] = $SBV_Stored["overdue_indicator"];
	}

	return $SBV;
}

function getReportReleaseSale($account_number){
	global $mongo_db;
	return mongoGetOne('account_number', $account_number, 'LO_Report_release_sale');
}

function getZACCF($LIC_NO){
	global $mongo_db;
	return mongoGetOne('LIC_NO', $LIC_NO, 'LO_ZACCF');
}

function getOneCustomer($account_number){
	global $mongo_db;
	$customer = mongoGetOne('account_number', $account_number,'LO_Customer');
	return $customer;
}


function mongoGet($field,$value, $collection){
	global $mongo_db;
	return $mongo_db->where($field, $value)->get($collection);
}

function mongoGetCustom($array, $collection){
	global $mongo_db;
	return $mongo_db->where($array)->get($collection);
}

function mongoGetOne($field,$value, $collection){
	global $mongo_db;
	return $mongo_db->where($field, $value)->order_by(array('_id' => -1))->getOne($collection);
}

function mongoGetOne_Custom($where, $collection){
	global $mongo_db;
	return $mongo_db->where($where)->order_by(array('_id' => -1))->getOne($collection);
}

?>