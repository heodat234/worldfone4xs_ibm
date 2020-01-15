<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Group extends CI_Controller {

	private $sub = "";
	private $collection = "Group";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
        $this->load->library("mongo_db");
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$this->load->library("crud");
		$response = $this->crud->read($this->collection, $request);
		echo json_encode($response);
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
		$data = $this->mongo_db->where(array("customGroups" => $id, "type" => "queue"))->distinct($this->collection, "queuename");
		echo json_encode($data);
	}

	function getMembersOfGroupId($id = "")
	{
		$group = $this->mongo_db->where_id($id)->getOne($this->collection);
		$members = isset($group["members"]) ? $group["members"] : [];
		$data = array();
		if($members) {
			$this->load->library("mongo_private");
			foreach ($members as $extension) {
				$doc = $this->mongo_private->where(["extension"=>$extension])->select(["extension","agentname","statuscode"])->getOne($this->sub . "User");
				$data[] = $doc ? $doc : [];
			}
		}
		echo json_encode($data);
	}

	function getQueueMembersOfGroupId($id = "")
	{
		$group = $this->mongo_db->where(array("customGroups" => $id, "type" => "queue"))->getOne($this->collection);
		$members = isset($group["members"]) ? $group["members"] : [];
		$data = array();
		if($members) {
			$this->load->library("mongo_private");
			foreach ($members as $extension) {
				$doc = $this->mongo_private->where(["extension"=>$extension])->select(["extension","agentname","statuscode"])->getOne($this->sub . "User");
				$data[] = $doc ? $doc : [];
			}
		}
		echo json_encode($data);
	}
}