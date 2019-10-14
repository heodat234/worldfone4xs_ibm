<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Config extends WFF_Controller {

	private $collection = "Config";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
	}

	function detail()
	{
		try {
			$config = array();
			$configFields = array("wff_version","wff_env","wff_unique_login","wff_time_cache", "phone_type", "brand_title", "brand_logo", "loader_layer", "wff_auth_redirect", "record_activity", "use_worker", "record_event","login_logout_ipphone", "short_key_ipphone", "ip_sip_server", "login_background_img", "login_background_color", "login_background_img_url", "login_brand_img");
			foreach ($configFields as $value) {
				$config[$value] = $this->config->item($value);
			}
			echo json_encode($config);
		} catch (Exception $e) {
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
		}
	}

	function update() 
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$config = array(
				"wff_version" => isset($data["wff_version"]) ? $data["wff_version"] : "1.0",
				"wff_env" => isset($data["wff_env"]) ? $data["wff_env"] : "DEV",
				"wff_unique_login" => isset($data["wff_unique_login"]) ? $data["wff_unique_login"] : TRUE,
				"wff_auth_redirect" => isset($data["wff_auth_redirect"]) ? $data["wff_auth_redirect"] : TRUE,
				"record_activity" => isset($data["record_activity"]) ? $data["record_activity"] : TRUE,
				"record_event" => isset($data["record_event"]) ? $data["record_event"] : TRUE,
				"use_worker" => isset($data["use_worker"]) ? $data["use_worker"] : TRUE,
				"wff_time_cache" => isset($data["wff_time_cache"]) ? (int) $data["wff_time_cache"] : 60,
				"show_customer" => isset($data["show_customer"]) ? $data["show_customer"] : "ALL",
				"show_cdr" => isset($data["show_cdr"]) ? $data["show_cdr"] : "ALL",
				"phone_type" => isset($data["phone_type"]) ? $data["phone_type"] : "",
				"brand_title" => isset($data["brand_title"]) ? $data["brand_title"] : "",
				"brand_logo" => isset($data["brand_logo"]) ? $data["brand_logo"] : "",
				"loader_layer" => isset($data["loader_layer"]) ? $data["loader_layer"] : TRUE,
				"login_logout_ipphone" => isset($data["login_logout_ipphone"]) ? $data["login_logout_ipphone"] : TRUE,
				"short_key_ipphone" => isset($data["short_key_ipphone"]) ? $data["short_key_ipphone"] : TRUE,
				"ip_sip_server" => isset($data["ip_sip_server"]) ? $data["ip_sip_server"] : "",
				"login_background_img" => isset($data["login_background_img"]) ? $data["login_background_img"] : FALSE,
				"login_background_color" => isset($data["login_background_color"]) ? $data["login_background_color"] : "",
				"login_background_img_url" => isset($data["login_background_img_url"]) ? $data["login_background_img_url"] : "",
				"login_brand_img" => isset($data["login_brand_img"]) ? $data["login_brand_img"] : "",
			);
			$file = BASEPATH . "config/wffdata.json";
			$fd = fopen($file, 'w');
			$content = json_encode($config, JSON_PRETTY_PRINT);
			$result = fwrite($fd, $content);
			fclose($fd);
			//$result = $this->crud->update($this->collection, $data);
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function clear_cache()
	{
		$files = glob(APPPATH.'cache/*');
		$html_file = APPPATH.'cache/index.html';
		$result = TRUE;
		$file_remove_arr = [];
		foreach($files as $file){
			if($file != $html_file && is_file($file)) {
				if(unlink($file)) {
					$file_name = str_replace(APPPATH.'cache/', "", $file);
					$file_remove_arr[] = $file_name;
				} else $result = FALSE;
			}
		}
		echo json_encode(array("status" => $result ? 1 : 0, "count" => count($file_remove_arr), "list_removed_files" => $file_remove_arr));
	}

	function clear_logs()
	{
		$files = glob(FCPATH.'application/logs/*');
		$html_file = FCPATH.'application/logs/index.html';
		$file_remove_arr = [];
		foreach($files as $file){
			if($file != $html_file && is_file($file)) {
				if(unlink($file)) {
					$file_name = str_replace(FCPATH.'application/logs/', "", $file);
					$file_remove_arr[] = $file_name;
				}
			}
		}
		$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
		$result = $this->cache->clean();
		echo json_encode(array("status" => $result ? 1 : 0, "count" => count($file_remove_arr), "list_removed_files" => $file_remove_arr));
	}
}