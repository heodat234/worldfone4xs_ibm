<?php

/*
 * Copyright © 2014 South Telecom
 */

require_once dirname(__DIR__) . '/beanstalk/autoload.php';

$queue = new Pheanstalk\Pheanstalk('127.0.0.1');
//var_dump($queue);
while ($job = $queue->watch("calljobs")->ignore('default')->reserve(30)) {
    try {
        $queue->bury($job);

        $callJob = json_decode($job->getData(), false);
        echo json_encode($callJob) . PHP_EOL;
        if ($callJob->startTimestamp <= time()) {
            echo " ==> Job is on time " . date('c',$callJob->startTimestamp) . "[". date('c',time()) . "]" . " -> run job " . PHP_EOL;
            //do some thing
            $callernum=$callJob->callernum;
            $destnum=$callJob->destnum;
            $secret=$callJob->secret;
            $urlDiallistId=$callJob->suburl;
            $curl = curl_init();          
            curl_setopt_array($curl, array(
              CURLOPT_URL => "http://192.168.16.59/externalcrm/makecall2.php?callernum=".$callernum."&destnum=".$destnum.$urlDiallistId."&secrect=".$secret,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode(array("secret" => $secret)),
              CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "authorization: Basic ",
                    "cache-control: no-cache",
                    "content-type: application/json"
              ),
            ));
            $response = curl_exec($curl);
            $responseArr = json_decode($response,true);
            $err = curl_error($curl);
            curl_close($curl);
             if (!$err) {
                if($response!=null){
                    if($response==="200"){
                        $queue->delete($job);
                     }else{
                          echo " ==> Job is not on excute -> kick job ". PHP_EOL;
                         $queue->kickJob($job);
                     }
                }else{
                    echo " ==> Job is not on excute -> kick job ". PHP_EOL;
                   $queue->kickJob($job);
               }         
            }else{
                echo " ==> Job is not on excute -> kick job ". PHP_EOL;
               $queue->kickJob($job);
           }
             
            //and delete job
            
        } else {
            echo " ==> Job is not on time -> kick job ". PHP_EOL;
            $queue->kickJob($job);
        }
    } catch (Exception $ex) {
             echo " ==> Job is not on excute -> kick job ".$ex. PHP_EOL;
    }
}
