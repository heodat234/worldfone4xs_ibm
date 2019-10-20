<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Preference extends WFF_Controller {

	private $collection = "User";
	private $sub = "";
	private $fields = array("theme", "language", "page_preloader", "ringtone", "avatar", "email", "phone","sound_effect","text_tool");

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection("");
		$this->collection = $this->sub . $this->collection;
	}

	function detail($extension)
	{
		$this->load->library("crud");
		$this->load->config("_mongo");
		$_db = $this->config->item("_mongo_db");
		$this->crud->select_db($_db);

		$response = $this->crud->where(array("extension" => $extension))->getOne($this->collection, $this->fields);
		$this->config->load('proui');
		if(empty($response["language"])) $response["language"] = "eng";
		if(empty($response["theme"])) $response["theme"] = $this->config->item("template")["theme"];
        if(!isset($response["page_preloader"])) $response["page_preloader"] = $this->config->item("template")["page_preloader"];
        // By session
        foreach (["text_tool"] as $value) {
        	$response[$value] = $this->session->userdata($value);
        }
		echo json_encode($response);
	}

	function update($extension)
	{
		try {
			$request = json_decode(file_get_contents('php://input'), TRUE);
			$this->load->library("crud");
			$this->load->config("_mongo");
			$_db = $this->config->item("_mongo_db");
			$this->crud->select_db($_db);

			$data = array();
			foreach ($this->fields as $field) {
				if(isset($request[$field])) {
					$data[$field] = $request[$field];
				}
			}

			$result = $this->crud->where(array("extension" => $extension))->update($this->collection, array('$set' => $data));
			if(!$result) throw new Exception("Error Processing Request", 1);
			
			foreach ($data as $key => $value) {
				$this->session->set_userdata($key, $value);
			}

			$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
	    	$my_session_id = $this->session->userdata("my_session_id");
	    	$this->cache->delete($my_session_id . "_nav");

			echo json_encode(array("status" => 1));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function upload_ringtone()
	{
		try {
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . RINGTONE_PATH; //str_replace(base_url(), "./", RINGTONE_PATH);
			$file = $_FILES['file'];
			$file_parts = pathinfo($file['name']);
			$allowed_types = 'mp3|wav|mid';
			$filesize = filesize($file['tmp_name']);
			if(strpos($allowed_types, strtolower($file_parts['extension'])) === FALSE) throw new Exception("Wrong file type. Only accept mp3, mid or wav");
			if($filesize > 8000000) throw new Exception("File too large, over 8MB.");
			$new_file_name = str_replace([" ","/"], ["",""], $file['name']);
			$file_path = $path . $new_file_name;
			if(file_exists($file_path)) throw new Exception("Your upload file was exists");
			if (is_uploaded_file($file['tmp_name'])) {
				move_uploaded_file($file['tmp_name'] , $file_path);
			}
			$name = $this->input->get("name");
			$data = array(
				'name'		=> $name ? $name : $file['name'],
				'uploadname'=> $file['name'],
				'filename' 	=> $new_file_name,
				'filepath'	=> RINGTONE_PATH . $new_file_name,
				'size' 		=> $filesize,
				'extension' => $file_parts['extension'],
				'time'		=> (new DateTime())->format('Y-m-d H:i:s')	
			);
			$this->load->library("mongo_db");
			$this->mongo_db->insert("{$this->sub}Ringtone", $data);
			echo json_encode(array("status" => 1, "message" => "Upload successfully", "filename" => $data["filepath"]));
		} catch (Exception $e) {
			//header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function ringtone()
	{
		$this->load->library("crud");
		$request = $_REQUEST;
		$response = $this->crud->read("{$this->sub}Ringtone", $request, array("name", "filepath"));
		echo json_encode($response);
	}
}