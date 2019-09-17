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

class quickchat_model extends CI_Model{
    public function __construct() {
        parent::__construct();
        // $this->load->library('mongo_db');
        $this->load->library('mongo_db4x');
        $this->collection = "quickChat";
    }
    
    function getProfile($_id) {
        return $this->mongo_db4x->where(array('_id' => new mongoId($_id)))->getOne('people');
    }

    function getQuickChatDynamicValue($condition) {
        return $this->mongo_db4x->where($condition)->getOne('dropDownListValue');
    }
}

