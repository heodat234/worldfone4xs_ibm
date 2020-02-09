<?php

/*
 * Copyright © 2019 South Telecom
 */
require_once dirname(__DIR__) . "/Header.php";

use Pheanstalk\Pheanstalk;

$queue = new Pheanstalk('127.0.0.1');
$mongo_db = new Mongo_db();
$_mongo_db = new Mongo_db();
$tube="";

 if(isset($argv)){
    if (sizeof($argv) < 2) {
        $tube="all";
    } else {
        $tube=$argv[1];
    }    
}
else{
    if(isset($_GET['tube'])){
         $tube=$_GET['tube'];
    }
    else{
         $tube="all";
    }
    header('Content-Type: application/json');
}

if($tube==="all"){
    $stats[] = $queue->stats();
    foreach ($queue->listTubes() as $tube)
    {
        $stats[] = $queue->statsTube($tube);
    }
    echo json_encode($stats, JSON_PRETTY_PRINT);
} else if($tube===""){
    $stats = $queue->stats();
    echo json_encode($stats, JSON_PRETTY_PRINT);
} else if($tube==="quick"){
    $stats = $queue->stats();
    $stats2 = array();
    $stats2['current-jobs-urgent']=$stats['current-jobs-urgent'];
    $stats2['current-jobs-ready']=$stats['current-jobs-ready'];
    $stats2['current-jobs-reserved']=$stats['current-jobs-reserved'];
    $stats2['current-jobs-delayed']=$stats['current-jobs-delayed'];
    $stats2['cmd-put']=$stats['cmd-put'];
    $stats2['cmd-delete']=$stats['cmd-delete'];
    
    echo json_encode($stats2, JSON_PRETTY_PRINT);
}
else{ 
    $stats = $queue->statsTube($tube);
    echo json_encode($stats, JSON_PRETTY_PRINT);
}


