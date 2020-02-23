<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Browserpage_model extends CI_Model {

	private $collection = "Browser_page";

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->collection = set_sub_collection($this->collection);
    }

    function run($id, $currentUri) {
    	if($this->getOne($id, $currentUri))
			$this->update($id, $currentUri);
		else $this->start($id, $currentUri); 
    }

    function getOne($id, $currentUri) {
        $my_session_id  = $this->session->userdata("my_session_id");
    	return $this->mongo_db->where(array(
            "tab_id"            => (int) $id, 
            "uri"               => $currentUri,
            "my_session_id"     => $my_session_id, 
            "endtime"           => 0
        ))->getOne($this->collection);
    }

    function start($id, $currentUri, $data = array()) {
    	$my_session_id 	= $this->session->userdata("my_session_id");
    	$extension		= $this->session->userdata("extension");
    	$time = time();

    	$this->update_previous($time);

    	$default_data = array(
    		"tab_id"				=>	(int) $id,
    		"uri"					=> 	$currentUri,
            "starttime"       		=>  $time,
            "endtime"				=> 	0,
            "lastpingtime"			=>  $time,
            "extension"				=> 	$extension,
            "my_session_id"			=> 	$my_session_id
        );
        $insert_data = array_merge($default_data, $data);
        
        $this->mongo_db->insert($this->collection, $insert_data);        
    }

    function update($id, $currentUri, $data = array()) {
        $time = time();
        $my_session_id = $this->session->userdata("my_session_id");
		$default_data = array(
            "lastpingtime"				=>  $time
		);
		$update_data = array_merge($default_data, $data);
		$where = array(
    		"tab_id"		=> (int) $id,
    		"uri"	        => $currentUri,
            "endtime"       => 0
    	);
		$this->mongo_db->where($where)
                ->set($update_data)
                ->update($this->collection);
    }

    function update_previous($time) {
    	$data = $this->mongo_db
    	->where(array("endtime" => 0))
    	->where_lt("lastpingtime", $time - $this->config->item("sess_time_to_update"))
    	->select(["lastpingtime"])
    	->get($this->collection);
    	if($data) {
	    	foreach ($data as $doc) {
	    		$this->mongo_db->where(array("_id" => new MongoDB\BSON\ObjectId($doc["id"])))
	    		->set(array("endtime" => $doc["lastpingtime"]))
	    		->update($this->collection);
	    	}
    	}
    }
}