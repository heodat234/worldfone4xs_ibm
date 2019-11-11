<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Group_model extends CI_Model {

	private $collection = "Group";

	function __construct()
	{
		parent::__construct();
		$this->load->library("mongo_db");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
	}

	function members_from_lead($extension = null)
	{
		$doc = $this->mongo_db->where("lead", $extension)->getOne($this->collection);
		$members = isset($doc["members"]) ? $doc["members"] : [];
		if(!in_array($extension, $members)) {
			$members[] = $extension;
		}
		return $members;
	}
}