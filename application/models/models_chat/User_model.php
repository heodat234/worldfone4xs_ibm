<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of people model
 *
 * @author Tran Dang Duy Tien
 */

class user_model extends CI_Model{
    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
    }
    
    function getProfile($_id) {
        $user_info = $this->mongo_db->where(array('_id' => new mongoId($_id)))->getOne('users');
        return array(
        	'id'	=> $user_info['_id']->{'$id'},
        	'name'	=> $user_info['lastname'].' '.$user_info['firstname'],
        	'profile_pic'	=> isset($user_info['profile_pic']) && !empty($user_info['profile_pic']) ? $user_info['profile_pic'] : base_url().'/assets/images/avatar_default.svg',
        );

    }

    function getProfileByUserName($username) {
        $user_info = $this->mongo_db->where(array('username' => $username))->getOne('users');
        return array(
        	'id'	=> $user_info['_id']->{'$id'},
        	'name'	=> $user_info['lastname'].' '.$user_info['firstname'],
        	'profile_pic'	=> isset($user_info['profile_pic']) && !empty($user_info['profile_pic']) ? $user_info['profile_pic'] : base_url().'/assets/images/avatar_default.svg',
        );

    }
}

