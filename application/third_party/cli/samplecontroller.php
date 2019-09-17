<?php
//Sample code for controller use libary beanstalk

if (!defined('BASEPATH')) exit('No direct script access allowed');

class samplecontroller extends WFF_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index($tube = 'all') {
        $this->load->library('beanstalk');
        $queue = $this->beanstalk->queue;
        if ($tube === "all") {
            $stats[] = $queue->stats();
            foreach ($queue->listTubes() as $tube) {
                $stats[] = $queue->statsTube($tube);
            }
            echo json_encode($stats, JSON_PRETTY_PRINT);
        }  else {
            $stats = $queue->statsTube($tube);
            echo json_encode($stats, JSON_PRETTY_PRINT);
        }
    }
    public function put(){
        $this->load->library('beanstalk');
        for($i=1;$i<=3;$i++){
            $callJob = new stdClass();
            $callJob->secret = "0f5577cdd0a2075b998fd3793e71a5aa";
            $callJob->callernum = "8801";
            $callJob->destnum="0969631171";
            $callJob->startTimestamp = time() + 10;
            echo json_encode($callJob) . "<br>";
            // put($data, $priority, $delay,$ttr);
            $this->beanstalk->queue->useTube("calljobs")->put(json_encode($callJob),0, $callJob->startTimestamp-time(),300);
            sleep($i);
        }
        
    }
    public function get(){
        $this->load->library('beanstalk');
        while ($job =  $this->beanstalk->queue->watch("calljobs")->ignore('default')->reserve(2)) {
        try {
                $this->beanstalk->queue->bury($job);
                
                $callJob = json_decode($job->getData(), false);
                if($callJob->startTimestamp <= time()){
                    echo "Job is on time -> run job <br>";
                    //do some thing
                    
                    //and delete job
                    //$this->beanstalk->queue->bury($job);
                    $this->beanstalk->queue->delete($job);
                }else{
                    echo "Job is not on time -> kick job <br>";
                    
                    $this->beanstalk->queue->kickJob($job);
                }
                
            }  catch (Exception $ex){
                echo $ex;
            }
        }
        echo "End of queues <br>";
        
    }
}