<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	private $collection = "User";

	function __construct()
	{
		parent::__construct();
		$this->load->library("mongo_private");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
	}

	function set_sub($sub)
	{
		$this->sub = $sub;
	}

    function all()
	{
		$data = $this->mongo_private->where(array("active" => true))->get($this->collection);
		return $data;
	}

	function extensions($issupervisor = 0, $isadmin = 0, $type = "or")
	{
		$where = array("active" => true);
		if($isadmin || $issupervisor) {
			$logic = '$' . $type;
			$where[$logic] = array();
			if($isadmin) {
				$where[$logic][] = array("isadmin" => true);
			}
			if($issupervisor) {
				$where[$logic][] = array("issupervisor" => true);
			}
		}
		
		return $this->mongo_private->where($where)->distinct($this->collection, "extension");
	}
}