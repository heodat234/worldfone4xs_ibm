<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();
while(true){
	Run();
	sleep(5);	
}


function Run(){
	global $mongo_db;
	$crons = $mongo_db->where(array('runned'=> false))->get('LO_Cron_scanning');
	foreach ($crons as $key => $cron) {
		exec($cron['dir']);
		$mongo_db->where_id($cron['id'])->update('LO_Cron_scanning', array('$set' => array('runned' => true)));
		var_dump($cron['dir']);
		godown();
	}

}

function godown(){
	echo PHP_EOL;
}
