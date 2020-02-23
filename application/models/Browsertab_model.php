<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Browsertab_model extends CI_Model {

	private $collection = "Browser_tab";

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->collection = set_sub_collection($this->collection);
    }

    function run($id) {
        if($this->getOne($id))
            $this->update($id);
        else $this->start($id);
    }

    function getOne($id) {
        $my_session_id = $this->session->userdata("my_session_id");
    	return $this->mongo_db->where(array("_id" => (int) $id, "my_session_id" => $my_session_id))->getOne($this->collection);
    }

    function start($id, $data = array()) {
    	$my_session_id = $this->session->userdata("my_session_id");

    	$time = time();
    	$default_data = array(
    		"_id"					=>	(int) $id,
            "starttime"       		=>  $time,
            "endtime"				=>  $time,
            "my_session_id"			=> 	$my_session_id
        );
        $insert_data = array_merge($default_data, $data);
        
        $this->mongo_db->insert($this->collection, $insert_data);        
    }

    function update($id, $data = array()) {
        $time = time();
        $my_session_id = $this->session->userdata("my_session_id");
		$default_data = array(
            "endtime"				=>  $time
		);
		$update_data = array_merge($default_data, $data);
		$this->mongo_db->where(array('_id' => (int) $id, "my_session_id" => $my_session_id))
                ->set($update_data)
                ->update($this->collection);
    }
}