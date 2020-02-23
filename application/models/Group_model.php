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
		$members = [$extension];
		$groups = $this->mongo_db->where("lead", $extension)->get($this->collection);
		if($groups) {
			foreach ($groups as $group) {
				if(!empty($group["members"])) {
					foreach ($group["members"] as $ext) {
						if(!in_array($ext, $members)) {
							$members[] = $ext;
						}
					}
				}
			}
		}
		return $members;
	}

	function queues_of_extension($extension = null)
	{
		$queues = $this->mongo_db->where("members", $extension)->where("type", "queue")->distinct($this->collection, "queuename");
		return $queues;
	}
}