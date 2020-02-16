<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();



function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$t = $midnight - 86400;

	$dial_details = $mongo_db->
	where('account_number', ['$in' => $account])->
	where('createdAt', ['$gte' => $t])->
	get('LO_Diallist_detail');
	foreach ($dial_details as $key => $dial) {
		echo $dial['account_number'];godown();
		$mongo_db->where_id($dial['id'])->update('LO_Diallist_detail', array('$set' => array('diallist_id' => new MongoDB\BSON\ObjectId('5e17e59f175b625576201190'), 'callResult' => [])));

	}

	
	

}

// function Run(){
// 	global $mongo_db;
// 	$midnight = strtotime('today midnight');
// 	$t = $midnight - 86400;
// 	$account =['0028030000053590','0028030000200241','0020170000024783','0020040000001349','0028030000206909','0028020000321469','0028030000191937','0028020000339941','0028030000186630','0028030000171236','0028020000428199','0028030000171376','0028030000192653','0028030000190897','0028030000184437','0028030000067632','0028030000036843'];
// 	$dial_details = $mongo_db->
// 	where('account_number', ['$in' => $account])->
// 	where('createdAt', ['$gte' => $t])->
// 	get('LO_Diallist_detail');
// // print_r($dial_details);exit;
// 	foreach ($dial_details as $key => $dial) {
// 		echo $dial['account_number'];godown();
// 		$mongo_db->where_id($dial['id'])->update('LO_Diallist_detail', array('$set' => array('diallist_id' => new MongoDB\BSON\ObjectId('5e17e59f175b625576201190'), 'callResult' => [])));

// 	}

	
	

// }

function godown(){
	echo PHP_EOL;
}
