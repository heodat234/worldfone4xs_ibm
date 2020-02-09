<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$SIBS_groups = $mongo_db->where('name',['$regex' => 'SIBS'])->get('LO_Group');	
	$CARD_groups = $mongo_db->like('name','card')->get('LO_Group');

	foreach ($SIBS_groups as $key => $value) {
		echo $value['name'] . PHP_EOL;
		$temp = explode('/', $value['name']);
		$arr_data = [
			'name' => $value['name'],
			'debt_type' => $temp[0],
			'debt_group' => $temp[1]
		];
		$mongo_db->insert('LO_Group_mapping_campaign', $arr_data);
	}

	foreach ($CARD_groups as $key => $value) {
		echo $value['name'] . PHP_EOL;
		$temp = explode('/', $value['name']);
		$arr_data = [
			'name' => $value['name'],
			'debt_type' => $temp[0],
			'debt_group' => $temp[1]
		];
		$mongo_db->insert('LO_Group_mapping_campaign', $arr_data);
	}
}

