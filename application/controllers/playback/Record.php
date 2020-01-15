<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

/* 
 * Copyright © 2019 South Telecom
 */

class Record extends CI_Controller{
    public function __construct() {
        parent::__construct();
        @error_reporting(0);
        $this->load->model("readfile_model");
    }
      
    function play() {
        $calluuid=$this->input->get('calluuid');
        $this->readfile_model->play_recording($calluuid);
    }
    function download() {
        $calluuid=$this->input->get('calluuid');
        $this->readfile_model->download_recording($calluuid);
    }
}