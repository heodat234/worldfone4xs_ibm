<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();

$from 	= $argv[1];
$limit 	= $argv[2];

$today_midnight = strtotime('today midnight');
echo $from .' '. $limit;
$dial_detail = $mongo_db->
where(array('createdAt'		=> array('$gt' 		=> $today_midnight)	))->
// where(array('sync_cus_name' => array('$exists' 	=> true)			))->
offset($from)->
limit($limit)->
order_by(array('_id' => -1))->
get('LO_Diallist_detail');

foreach ($dial_detail as $key => $value) {
	if(!isset($value['LIC_NO'])) continue;

	$LIC_NO 		= $value['LIC_NO'];
	$release_sale 	= $mongo_db->where("cus_id", $LIC_NO)->getOne('LO_Report_release_sale');
	if(empty($release_sale)) 				continue;
	if(!isset($release_sale['cus_name'])) 	continue;

	$cus_name 			= $release_sale['cus_name'];
	$mongo_db->where_id($value['id'])->update('LO_Diallist_detail', array('$set' => array("cus_name" => $cus_name, 'sync_cus_name' => true)));
}

// print_r($argv);
