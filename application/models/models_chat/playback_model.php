<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of playback_model
 *
 * @author tin
 */
class Playback_model extends WFF_Model {

    public function __construct() {
        $this->load->library('mongo_db');
        
    }
    public function get_key( ) {
       $this->load->helper('url');
        $data=$this->mongo_db->getOne('wff_config');
        return $data['secret_key'];
        
    }
}