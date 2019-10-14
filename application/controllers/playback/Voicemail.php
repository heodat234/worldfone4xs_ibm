<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

/* 
 * Copyright © 2014 South Telecom
 */

class Voicemail extends WFF_Controller{
    public function __construct() {
        parent::__construct();
        $this->load->model('readfile_model');
    }
      
    function play() {
        $voicemailid=$this->input->get('voicemailid');
        $this->readfile_model->play_voicemail($voicemailid);
    }
    
    function download() {
        $voicemailid=$this->input->get('voicemailid');
        $this->readfile_model->download_voicemail($voicemailid);
    }
}

