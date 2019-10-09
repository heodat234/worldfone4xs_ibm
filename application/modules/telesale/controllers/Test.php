<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MX_Controller {

	public function index() {
		$this->load->model("test_model");
		$this->load->view('welcome_message');
	}
}