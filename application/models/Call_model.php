<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Call_model extends CI_Model {

	private $collection = "worldfonepbxmanager";
    private $collection_realtime = "worldfonepbxmanager_realtime";

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_db');
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
    }

    function get_current_call($extension) {
    	$where = array(
    		"userextension" => $extension,
    		"workstatus"	=> array('$in' => ["Ring", "On-Call"])
    	);
    	$data = $this->mongo_db->where($where)->order_by(array("workstatus" => 1, "starttime" => -1))->getOne($this->collection."_realtime");
    	return $data;
    }

    function get_total_today_by_extension($extension, $match = array()) {
    	$where = array(
    		"userextension" => $extension,
    		"starttime"	=> array('$gte' => strtotime('today midnight'))
    	);
    	$where = array_merge($where, $match);
    	$data = $this->mongo_db->where($where)->count($this->collection);
        return $data;
    }

    function get_call_by_id($calluuid) {
        $where = array(
            "calluuid" => $calluuid
        );
        $data = $this->mongo_db->where($where)->getOne($this->collection);
        return $data;
    }

    function get_call_in_queue() {
        $collection = $this->sub . $this->collection_realtime;
        $where = array(
            "direction"     => "inbound",
            "callstatus"    => "Start"
        );
        $data = $this->mongo_db->where($where)->select(array("starttime","customernumber","queue", "extension_available","dnis"))->get($collection);
        return $data;
    }

    function inc_show_popup($calluuid) {
        $where = array(
            "calluuid" => $calluuid
        );
        return  $this->mongo_db->where($where)->inc("show_popup", 1)->update($this->collection);
    }
}