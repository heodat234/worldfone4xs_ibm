<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of people model
 *
 * @author Le Thi Ngoc Oanh
 */

class people_model extends CI_Model{
    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
       $this->collection = "people";
    }
    
    function getProfile($_id) {
        return $this->mongo_db->where(array('_id' => new mongoId($_id)))->getOne('people');
    }
    
//    function filterProfiles($searchValue) {
//        $
//    }
}

