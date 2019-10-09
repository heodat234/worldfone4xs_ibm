<?php
require_once FCPATH . "vendor/autoload.php";

class Pheanstalk extends Pheanstalk\Pheanstalk {
	private $host = '127.0.0.1';
	private $tube = '';
	
	function __construct()
    { 
    	parent::__construct($this->host);
    }

    function openPheanstalk() {
        return new Pheanstalk\Pheanstalk($this->host);
    }

    function createTube($pheanstalk, $tubeName, $data) {
        return $pheanstalk->useTube($tubeName)->put($data);
    }

    function createJob($pheanstalk, $tube) {
	    return $pheanstalk->watch($tube)->ignore('default')->reserve(2);
    }

    function getDataFromJob($pheanstalk, $tube) {
        if (!isset($this->jobs[$tube])) {
            return false;
        }
        $timeout = 3;
        $job = $pheanstalk->watch($tube)->reserve($timeout);
        if (empty($job) || !is_object($job) || $job->getId() == 0 || empty($job->getData())) {
            return false;
        }
        $data = ['job' => $job, 'handle' => $this->jobs[$tube]];
        return serialize($data);
    }

    function deleteJob($job) {

    }

}