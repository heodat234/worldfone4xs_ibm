<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Interactive_model extends CI_Model {

	private $collection = "Interactive";

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("crud");
    }

    function create($type = "", $key = "", $data = array(), $config = array())
	{
		$sub = !empty($config["type"]) ? $config["type"] . "_" : "";
		$collection = $sub . $this->collection;
		try {
			switch ($type) {
				case 'call':
					if(!isset($data["customernumber"])) throw new Exception("Error Processing Request", 1);
					
					$phone = $data["customernumber"];

					$title = !empty($data["direction"]) ? ($data["direction"] == "inbound" ? "Call in" : "Call out") : "Call";
					$doc = array(
						"title"			=> $title,
						"content" 		=> $phone . " - " . $data["disposition"],
						"type" 			=> $type,
						"active" 		=> true,
						"other_id"		=> $data["calluuid"],
						"foreign_key"	=> $phone
					);
					break;

				case 'email':
					$email = $key;
					
					$title = !empty($data["sendedAt"]) ? "Email out" : "Email in";
					$content = isset($data["subject"]) ? $data["subject"] : "";

					$doc = array(
						"title"			=> $title,
						"content" 		=> $content,
						"type" 			=> $type,
						"active" 		=> true,
						"other_id"		=> isset($data["id"]) ? $data["id"] : "",
						"foreign_key"	=> $email
					);
					break;
				
				default:
					# code...
					break;
			}
			$this->crud->create($collection, $doc);
			return TRUE;
		} catch (Exception $e) {
			return FAlSE;
		}
	}
}