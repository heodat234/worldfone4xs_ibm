<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class View extends CI_Controller {
	function v1()
	{
		$uri_string = $this->uri->uri_string();
		$path = str_replace("template/view/v1/", "", $uri_string);
		$data = $_GET;
		$this->load->model("language_model");
		$view = $this->load->view("templates/{$path}", $data, TRUE);
        echo $this->language_model->translate($view, "CONTENT");
	}
}