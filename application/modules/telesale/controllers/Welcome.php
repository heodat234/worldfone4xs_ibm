<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MX_Controller {

	public function index() {
		$this->load->model("pbx_model");
		//$this->pbx_model->make_call_2();
		$this->load->view('welcome_message');
	}
}