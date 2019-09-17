<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    }

	public function index()
	{
		exit("Đang phát triển");
		$this->output->data["css"][] = STEL_PATH . "css/table.css";;
        //$this->output->data["js"][] = STEL_PATH . "js/report/agentsign.js";
		$this->load->view('ticket/all_view');
	}
}