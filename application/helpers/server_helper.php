<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_disk_free_status($disks){
	$res = array();
	$max = 5;
	foreach($disks as $disk){
		if(strlen($disk["name"]) > $max) 
			$max = strlen($disk["name"]);
	}
	
	foreach($disks as $disk){
		$disk_space = disk_total_space($disk["path"]);
		$disk_free = disk_free_space($disk["path"]);
		$res[] = array(
			"disk_space" 	=> $disk_space,
			"disk_free"		=> $disk_free,
			"disk_name"		=> $disk["name"]
		);
	}
	return $res;
}

function getSymbolByQuantity($bytes, $round = 2) {
	$symbol = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	$exp = floor(log($bytes)/log(1024));
	
	return round($bytes/pow(1024, floor($exp)), $round) . $symbol[$exp];
}