<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Avatar extends WFF_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function agent($extension = "")
	{
		try {
			if(!$extension) throw new Exception("No extension");
			$this->load->library("mongo_private");
			$user = $this->mongo_private->where(array("extension" => $extension))->getOne(set_sub_collection("User"));
			$file_path = !empty($user["avatar"]) ? $user["avatar"] : "undefined";
			if(!file_exists(FCPATH . $file_path)) {
				$file_path = "public/proui/img/placeholders/avatars/avatar.jpg";
			}
			$image = imagecreatefromstring(file_get_contents(FCPATH . $file_path));
			$file_parts = @pathinfo($file_path);
			$extension = $file_parts["extension"];
			$seconds_to_cache = 3600;
			$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
			header("Expires: $ts");
			header("Pragma: cache");
			header("Cache-Control: max-age=$seconds_to_cache");
			header('Content-Type: image/' . $extension);
			($extension == "png") ? imagepng($image) : imagejpeg($image);
		} catch (Exception $e) {
			header('Content-type: application/json');
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function customer($id = "")
	{
		try {
			if(!$id) throw new Exception("No id");
			$this->load->library("mongo_db");
			$user = $this->mongo_db->where_id($id)->getOne(set_sub_collection("Customer"));
			$file_path = !empty($user["avatar"]) ? $user["avatar"] : "undefined";
			if(!file_exists(FCPATH . $file_path)) {
				$file_path = "public/proui/img/placeholders/avatars/avatar.jpg";
			}
			$image = imagecreatefromstring(file_get_contents(FCPATH . $file_path));
			$file_parts = @pathinfo($file_path);
			$extension = $file_parts["extension"];
			$seconds_to_cache = 3600;
			$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
			header("Expires: $ts");
			header("Pragma: cache");
			header("Cache-Control: max-age=$seconds_to_cache");
			header('Content-Type: image/' . $extension);
			($extension == "png") ? imagepng($image) : imagejpeg($image);
		} catch (Exception $e) {
			header('Content-type: application/json');
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}