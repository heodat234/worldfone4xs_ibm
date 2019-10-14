<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Group extends CI_Controller {

	private $collection = "Group";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->collection = set_sub_collection($this->collection);
        $this->load->library("mongo_db");
	}

	function getListGoupIdName()
	{
		$group_info = $this->mongo_db->get($this->collection);
		$list_group = array_column($group_info, 'name', 'id');
		echo json_encode($list_group);
	}
}