<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Changelog extends WFF_Controller {

	private $collection = "Changelog";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$_db = $this->config->item("_mongo_db");
		$this->mongo_db->switch_db($_db);
	}

	function read()
	{
		try {
			$id = $this->input->get("id");
			$highlight = $this->input->get("highlight");
			$path = FCPATH;
			if($id) 
			{
				$parent = $this->mongo_db->where("_id", new MongoDB\BSON\ObjectId($id))->getOne($this->collection);
				$path = $parent["file_path"]."/";
			}
			// Update new info
			$items = array_diff(scandir($path), array('..', '.'));
			$file_paths = array();
			foreach ($items as $name) {
				$file_path = $path . $name;
				$file_info = new SplFileInfo($file_path);
				$file_name = $file_info->getFilename();
				$ext = $file_info->getExtension();
				$icon = $this->set_icon($ext);
				
				$file = array(
					"parent_id"		=> $id,
					"highlight"		=> ($file_name == $highlight),
					"name" 			=> $name,
					"parent_path"	=> $path,
					"file_path"		=> $file_path,
					"app_path"		=> str_replace(FCPATH, "", $file_path),
					"file_name"		=> $file_name,
					"base_name"		=> $file_info->getBasename("." . $ext),
					"ext"			=> $ext,
					"icon"			=> $icon,
					"modify_time"	=> $file_info->getMTime(),
					"create_time"	=> $file_info->getCTime(),
					"access_time"	=> $file_info->getATime(),
					"is_dir" 		=> $file_info->isDir(),
					"is_readable"	=> $file_info->isReadable(),
					"is_writable"	=> $file_info->isWritable(),
				);
				$this->mongo_db->where("file_path", $file_path)->update($this->collection, array('$set' => $file), array('upsert' => TRUE));
				$file_paths[] = $file["file_path"];
			}
			//
			$where = array("parent_path" => $path);
			if(!$id) {
				$where["file_name"] = array('$in' => array("application", "public", "tests"));
			}
			$response = $this->mongo_db->where($where)->get($this->collection);
			foreach ($response as &$doc) {
				$doc["exists"] = in_array($doc["file_path"], $file_paths) ?  TRUE : FALSE;
			}
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
		}
	}

	function delete($id)
	{
		$result = $this->mongo_db->where(array("_id" => new MongoDB\BSON\ObjectId($id)))->delete($this->collection);
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function update($id) 
	{
		try {
			$data = $_POST;
			$change = $this->input->post("change");
			//if(!$change) throw new Exception("Error Processing Request", 1);
			
			$data = array(
				"access_time" 	=> (int) $this->input->post("access_time"),
				"modify_time" 	=> (int) $this->input->post("modify_time"),
				"change"		=> $change,
				"change_time"	=> time(),
				"code"			=> file_get_contents($data["file_path"])
			);
			$update = array("logs" => $data);
			$result = $this->mongo_db->where("_id", new MongoDB\BSON\ObjectId($id))
			->update($this->collection, array('$addToSet' => $update));
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function file()
	{
		$request = $_GET;
		$this->load->library("crud");
		$this->load->config("_mongo");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);
		$response = $this->crud->read("Changelog", $request, ["app_path","parent_id","file_name"]);
		echo json_encode($response);
	}

	private function set_icon($ext) {
		$icon = "fi fi-txt text-success";
		switch ($ext) {
			case 'php':
				$icon = "fi fi-php text-danger";
				break;

			case 'html':
				$icon = "fi fi-html text-muted";
				break;

			case 'htaccess':
				$icon = "fi fi-htm text-danger";
				break;

			case '':
				$icon = "gi gi-folder_open text-info";
				break;
			
			default:
				# code...
				break;
		}
		return $icon;
	}
}