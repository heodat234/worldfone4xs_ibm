<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Language_model extends CI_Model {

	private $collection = "Language";
	private $time_cache = 60;

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_private');
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        $this->time_cache     = $this->config->item("wff_time_cache");
    }

    function translate($content, $type = "", $language = "", $sub = "", $char = "@") {
    	$content_type = is_array($content) ? "array" : "string";
		if($content_type == "array")
			$content_str = json_encode($content);
		else $content_str = (string) $content;

    	if(!$sub) $sub = set_sub_collection("");
    	if(!$language) $language = $this->session->userdata("language");
    	$language = strtoupper($language);
    	$where = array("language" => $language);
    	if($type) $where["type"] = $type;

		// Cache
		$params_string =  $sub . $type . "_" . $language;
		$cache_id = $params_string . "_languages";

		if ( !$language_data = $this->cache->get($cache_id) ) {
	        $languages = $this->mongo_private->where($where)->select(["key", "value"], ["_id"])->get($sub . $this->collection);
	        $keys = $values = array();
			if($languages) {
				foreach($languages as $lang) {
					if(isset($lang["key"], $lang["value"])) {
						$keys[] = $char . $lang["key"] . $char;
						$values[] = $lang["value"];
					}
				}
			}
			$language_data = ["keys" => $keys, "values" => $values];
	        $this->cache->save($cache_id, $language_data, $this->time_cache);
	    }

	    $keys 	= isset($language_data["keys"]) ? $language_data["keys"] : [];
		$values = isset($language_data["values"]) ? $language_data["values"] : [];

		$content_replace = str_replace($keys, $values, $content_str);

		// Xu ly dac biet ngon ngu tieng anh
		if($language == "ENG") {
			$where["language"] = "VIE";
			// Cache
			$cache_id = $sub . $type . "_" . $where["language"] . "_languages";
			if ( !$language_data = $this->cache->get($cache_id) ) {
				$languages = $this->mongo_private->where($where)->select(["key", "value"], ["_id"])->get($sub . $this->collection);
				$keys = $values = array();
				if($languages) {
					foreach($languages as $lang) {
						if(isset($lang["key"])) {
							$keys[] = $char . $lang["key"] . $char;
							$values[] = $lang["key"];
						}
					}
				}
				$language_data = ["keys" => $keys, "values" => $values];
				$this->cache->save($cache_id, $language_data, $this->time_cache);
			}

			$keys 	= isset($language_data["keys"]) ? $language_data["keys"] : [];
			$values = isset($language_data["values"]) ? $language_data["values"] : [];

			$content_replace = str_replace($keys, $values, $content_replace);
		}

		return ($content_type == "array") ? json_decode($content_replace, TRUE) : $content_replace;
    }

    function translate_old($content, $type = "", $language = "", $sub = "", $char = "@") {
    	if(!$sub) $sub = set_sub_collection("");
    	if(!$language) $language = $this->session->userdata("language");
    	$language = strtoupper($language);
    	$where = array("language" => $language);
    	if($type) $where["type"] = $type;

		// Cache
		$params_string =  $sub . $type . "_" . $language;
		$cache_id = $params_string . "_languages";
		$languages = array();
		if ( !$languages = $this->cache->get($cache_id) ) {
	        $languages = $this->mongo_private->where($where)->select(["key", "value"], ["_id"])->get($sub . $this->collection);
	        $this->cache->save($cache_id, $languages, $this->time_cache);
	    }

		$keys = array();
		$values = array();
		if($languages) {
			foreach($languages as $lang) {
				if(isset($lang["key"], $lang["value"])) {
					$keys[] = $char . $lang["key"] . $char;
					$values[] = $lang["value"];
				}
			}
		}

		$content_type = is_array($content) ? "array" : "string";
		if($content_type == "array")
			$content_str = json_encode($content);
		else $content_str = (string) $content;

		$content_replace = str_replace($keys, $values, $content_str);
		// Xu ly dac biet ngon ngu tieng anh
		if($language == "ENG") {
			$where["language"] = "VIE";
			// Cache
			$cache_id = $sub . $type . "_" . $where["language"] . "_languages";
			if ( !$languages = $this->cache->get($cache_id) ) {
				$languages = $this->mongo_private->where($where)->select(["key", "value"], ["_id"])->get($sub . $this->collection);
				$this->cache->save($cache_id, $languages, $this->time_cache);
			}
			$keys = array();
			$values = array();
			if($languages) {
				foreach($languages as $lang) {
					if(isset($lang["key"])) {
						$keys[] = $char . $lang["key"] . $char;
						$values[] = $lang["key"];
					}
				}
			}
			$content_replace = str_replace($keys, $values, $content_replace);
		}

		return ($content_type == "array") ? json_decode($content_replace, TRUE) : $content_replace;
    }
}