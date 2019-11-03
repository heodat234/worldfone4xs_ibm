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

	function getListGroupIdName()
	{
		$group_info = $this->mongo_db->get($this->collection);
		$list_group = array_column($group_info, 'name', 'id');
		echo json_encode($list_group);
	}

	function getImageNameById($id = "")
	{
		$doc = $this->mongo_db->where_id($id)->getOne(set_sub_collection("Group"));
		$this->load->library("text_to_image");
		$this->text_to_image->createImage($doc["name"]);
		$this->text_to_image->showImage();
	}

	function getQueuesLinkToGroupId($id = "")
	{
		$data = $this->mongo_db->where(array("customGroups" => $id, "type" => "queue"))->distinct(set_sub_collection("Group"), "queuename");
		echo json_encode($data);
	}
}