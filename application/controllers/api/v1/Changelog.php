<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Changelog extends WFF_Controller {

	private $collection = "Changelog";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		if(!$this->session->userdata("issysadmin")) exit();
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
				if(strpos($name, "servlet") === 0) continue;

				$file_path = $path . $name;
				if(in_array($name, ["public","upload"])) 
				{
					$time = time();
					$permissions = substr(sprintf('%o', @fileperms($file_path)), -4);
					$icon = $this->set_icon("");

					$file = array(
						"parent_id"		=> $id,
						"highlight"		=> ($name == $highlight),
						"name" 			=> $name,
						"parent_path"	=> $path,
						"file_path"		=> $file_path,
						"app_path"		=> str_replace(FCPATH, "", $file_path),
						"file_name"		=> $name,
						"base_name"		=> $name,
						"ext"			=> "",
						"icon"			=> $icon,
						"modify_time"	=> $time,
						"create_time"	=> $time,
						"access_time"	=> $time,
						"is_dir" 		=> true,
						"is_readable"	=> true,
						"is_writable"	=> true,
						"permissions"	=> $permissions,
					);
				} else {
					$file_info = new SplFileInfo($file_path);
					$file_name = $file_info->getFilename();
					$ext = $file_info->getExtension();
					$icon = $this->set_icon($ext, $file_info->isDir());
					$permissions = substr(sprintf('%o', @fileperms($file_path)), -4);
					
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
						"permissions"	=> $permissions,
					);
				}
				$this->mongo_db->where("file_path", $file_path)->update($this->collection, array('$set' => $file), array('upsert' => TRUE));
				$file_paths[] = $file["file_path"];
			}
			//
			$where = array("parent_path" => $path);
			/*if(!$id) {
				$where["file_name"] = array('$in' => array("application", "public", "tests"));
			}*/
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
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$change = isset($data["change"]) ? $data["change"] : "";
			if(!isset($data["file_path"])) throw new Exception("Error Processing Request", 1);
			
			$data = array(
				"access_time" 	=> (int) (isset($data["access_time"]) ? $data["access_time"] : 0),
				"modify_time" 	=> (int) (isset($data["modify_time"]) ? $data["modify_time"] : 0),
				"change"		=> $change,
				"change_time"	=> time(),
				"code"			=> file_get_contents($data["file_path"])
			);
			$update = array("logs" => $data);
			$result = $this->mongo_db->where("_id", new MongoDB\BSON\ObjectId($id))
			->update($this->collection, array('$push' => $update));
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

	private function set_icon($ext, $id_dir = FALSE) {
		
		if($id_dir) return "gi gi-folder_open text-info";

		$ext = strtolower($ext);
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
				$exts = "|txt|doc|rtf|log|tex|msg|text|wpd|wps|docx|page|csv|dat|tar|xml|vcf|pps|key|ppt|pptx|sdf|gbr|ged|mp3|m4a|waw|wma|mpa|iff|aif|ra|mid|m3v|e_3gp|shf|avi|asx|mp4|e_3g2|mpg|asf|vob|wmv|mov|srt|m4v|flv|rm|png|psd|psp|jpg|tif|tiff|gif|bmp|tga|thm|yuv|dds|ai|eps|ps|svg|pdf|pct|indd|xlr|xls|xlsx|db|dbf|mdb|pdb|sql|aacd|app|exe|com|bat|apk|jar|hsf|pif|vb|cgi|css|js|php|xhtml|htm|html|asp|cer|jsp|cfm|aspx|rss|csr|less|otf|ttf|font|fnt|eot|woff|zip|zipx|rar|targ|sitx|deb|e_7z|pkg|rpm|cbr|gz|dmg|cue|bin|iso|hdf|vcd|bak|tmp|ics|msi|cfg|ini|prf|";
				if(strpos($exts, "|" . $ext . "|") !== FALSE) {
					$icon = "fi fi-${ext} text-success";
				}
				break;
		}
		return $icon;
	}

	function readfile()
	{
		header("Content-Type: text/plain");
		$filepath = $this->input->get("filepath");
		$full_path = FCPATH . $filepath;
		$content = file_get_contents($full_path);
		echo $content;
	}

	function savefile()
	{
		try {
			$filepath = $this->input->post('filepath');
			$content = $this->input->post('content');
			$change = $this->input->post('change');

			$dataLog = array(
				"access_time" 	=> (int) $this->input->post("access_time"),
				"modify_time" 	=> (int) $this->input->post("modify_time"),
				"change"		=> $change,
				"change_time"	=> time(),
				"code"			=> file_get_contents($filepath)
			);
			
			$check = file_put_contents($filepath, html_entity_decode($content)) ? TRUE : FALSE;
			if(!$check) throw new Exception("Error Processing Request", 1);
			$dataUpdate = array(
				"updatedBy" => $this->session->userdata("extension"),
				"updatedAt" => time()
			);

			$this->mongo_db->where(array("app_path" => $filepath))->update($this->collection, 
				array('$set' => $dataUpdate, '$addToSet' => array("logs" => $dataLog)));
			echo json_encode(array("status" => 1, "message" => "Success"));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function chmod()
	{
		try {
			$filepath = $this->input->post('filepath');
			$result = chmod($filepath, 0755);
			if(!$result) throw new Exception("Can't chmod", 1);
			
			echo json_encode(array("status" => 1));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}