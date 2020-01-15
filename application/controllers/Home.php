<?php
defined('BASEPATH') OR exit('No direct script access allowed');
Class Home extends WFF_Controller {
	public function index()
	{
		$this->_build_template();
		$this->output->data["js"][] = KENDOUI_PATH . "js/kendo.timezones.min.js";
		$data["viewAll"] = TRUE;
		$this->load->view('home', $data);
	}
}
