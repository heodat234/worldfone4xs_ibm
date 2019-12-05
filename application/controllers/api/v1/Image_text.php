<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Image_text extends CI_Controller {
	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->model("language_model");
		$this->sub = set_sub_collection();
	}

	function getDiallistTypeName($value = "")
	{
		$collection = "Jsondata";
		$collection = $this->sub . $collection;
		$tags = ["Diallist","type"];
		$this->load->library("mongo_private");
		$doc = $this->mongo_private->where(array("tags" => $tags))->getOne($collection);
		$text = "@Common@";
		if(isset($doc["data"])) {
			foreach ($doc["data"] as $row) {
				if($row["value"] == $value) {
					$text = $row["text"];
				}
			}
		}
		$text = $this->language_model->translate($text);
		$this->load->library("text_to_image");
		$this->text_to_image->createImage($text);
		$this->text_to_image->showImage();
	}
}