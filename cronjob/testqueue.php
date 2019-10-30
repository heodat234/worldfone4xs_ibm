<?php
/**
 * Created by PhpStorm.
 * User: oanhl
 * Date: 9/30/2019
 * Time: 2:50 PM
 */
global $pheanstalk, $CI;
$CI->load->library("mongo_db");
if(isset($argv[1])){
    while ($argv[1]) {
        $pheanstalk = $argv[1];
        $job = $argv[2];
        $pheanstalk->bury($job);
        $data = $job->getData();
        $CI->mongo_db->insert("2_TestQueue");
        $pheanstalk->delete($job);
    }
}