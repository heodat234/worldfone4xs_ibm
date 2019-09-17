<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Agentstate_model extends CI_Model {

	private $collection = "Agent_state";

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->collection = set_sub_collection($this->collection);
    }

    function getOne($select = array(), $unselect = array()) {
 		$extension = $this->session->userdata("extension");
 		$data = $this->mongo_db->where(array("extension" => $extension, "endtime" => 0))
 			->select($select, $unselect)
 			->order_by(array('starttime' => -1))
 			->getOne($this->collection);
 		return $data;
 	}

    function start($data = array()) {
    	$extension 		= $this->session->userdata("extension");
        $my_session_id 	= $this->session->userdata("my_session_id");

        $this->update_previous($extension);
    	$time = time();
    	$default_data = array(
            "starttime"       			=>  $time,
            "endtime"					=>  0,
            "lastpingtime"      		=>  $time,
            "my_session_ids"         	=>  [$my_session_id],
            "extension"					=>	$extension
        );
        $insert_data = array_merge($default_data, $data);
        
        $this->mongo_db->insert($this->collection, $insert_data);      
    }

    function end($data = array()) {
        $time = time();
        $extension  =   $this->session->userdata("extension");

		$default_data = array(
			"endtime" 		=> $time
		);
		$update_data = array_merge($default_data, $data);
		$this->mongo_db->where(array('extension' => $extension, "endtime" => 0))
                ->set($update_data)
                ->update_all($this->collection);
    }

    function update($data = array()) {
        $time = time();
        $my_session_id = $this->session->userdata("my_session_id");
        $extension = $this->session->userdata("extension");

		$default_data = array(
			"lastpingtime" => $time
		);
		$update_data = array_merge($default_data, $data);
		$this->mongo_db->where(array('extension' => $extension, "endtime" => 0))
                ->set($update_data)
                ->addtoset("my_session_ids", $my_session_id)
                ->update($this->collection);
    }

    private function update_previous($extension)
    {
        $this->load->config("_mongo");
        $time = time();
        // Truong hop user khong logout, tat trinh duyet
        $data = $this->mongo_db->where(array("extension" => $extension, "endtime" => 0))
        ->select(["_id", "lastpingtime"])
        ->get($this->collection);
        foreach ($data as $doc) {
            if( $time > $doc["lastpingtime"] + $this->config->item("sess_time_to_update")) 
            {
                // Khi session het han
                $this->mongo_db->where(array("_id" => new MongoDB\BSON\ObjectId($doc["id"])))
                ->set(array("endtime" => $doc["lastpingtime"], "endnote" => "No connect too long"))
                ->update($this->collection);
            }
        }
    }
}