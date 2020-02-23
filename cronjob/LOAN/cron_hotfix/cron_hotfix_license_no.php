<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

Run();

function Run(){
	global $mongo_db;
	$midnight = strtotime('today midnight');
	$diallists = $mongo_db->where(array("createdAt" => array('$gte' => $midnight), 'team' => 'CARD'))->get('LO_Diallist');
	foreach ($diallists as $key => $diallist) {
		$dial_details = $mongo_db->where_object_id('diallist_id', $diallist['id'])->
		where("other_phones", ['$exists' => false])->
		get('LO_Diallist_detail');

		foreach ($dial_details as $key => $dial) {
			// if($dial['account_number'] !== '0028070000007360') continue;
			echo $dial['id'];
			godown();
			$dial['LIC_NO'] = rtrim($dial['LIC_NO']);
			$relationship = $mongo_db->where('LIC_NO', $dial['LIC_NO'])->get('LO_Relationship');
			var_dump($dial['LIC_NO']);
			$other_phones = [];
			if(count($relationship) >0){
				foreach ($relationship as $key => $re) {
					$other_phones[] = $re["phone"];
				}
			print_r($other_phones);
				$mongo_db->where_id($dial['id'])->update('LO_Diallist_detail', array('$set' => array('other_phones' => $other_phones)));

			}
		}
	}
	

}

function godown(){
	echo PHP_EOL;
}
