<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */

class peoples extends WFF_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db4x');
        $this->username = $this->session->userdata("user");
        $this->name = $this->session->userdata('name');
        $this->userextension    = $this->session->userdata('extension');
    }

    public function addPeople($version = 'v1') {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $username = $this->input->post('user');
            $email = $this->input->post('email');
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $role = $this->input->post('role');
            $group_id = $this->input->post('group_id');
            $this->mongo_db4x->insert('people',array('email' => $email, 'firstname' => $firstname, 'username' => $username, 'lastname' => $this->input->post('lastname'), 'date_added' => time() ));
            $json['success'] = 'Thêm Official Account thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function editPeople($version = 'v1') {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $_id = $this->input->post('_id');
            $name = $this->input->post('name');
            $value = $this->input->post('value');
            $this->mongo_db4x->where(array( '_id' => new mongoId($_id) ))->set(array($name => $value))->update('people');
            $json['success'] = 'Edit peoples thành công';
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function addPeopleProperty($version = 'v1') {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $_id = $this->input->post('_id');
            $name = $this->input->post('name');
            $value = $this->input->post('value');
            $people_info = $this->mongo_db4x->where(array('_id' => new mongoId($_id) ))->getOne('people');
            if (!empty($people_info)) {
                $properties = isset($people_info['properties']) ? $people_info['properties'] : array();
                $properties[] = array(
                    'name'  => $name,
                    'value' => $value,
                );
            }else{
                $properties = array();
                $properties[] = array(
                    'name'  => $name,
                    'value' => $value,
                );
                
            }

            $this->mongo_db4x->where(array('_id' => new mongoId($_id) ))->set(array( 'properties' => $properties ))->update('people');
            
            /*if (!empty($json['properties'])) {
                $properties = 
            }*/
            $json['success'] = 'Edit Property thành công';
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function deletePeople() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $this->mongo_db4x->where(array( '_id' => new mongoId($id) ))->delete('people');
            $json['success'] = 'Delete Official Account thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getPeoples() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $json['users'] = $this->mongo_db4x->where(array('username' => $username))->order_by(array('date_added' => -1))->get('people');
            
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    public function getPeople() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $room_id = $this->input->get('room_id');
            $room_info = $this->mongo_db4x->where(array("_id" => new MongoId($room_id)))->getOne('chatGroups');  
//         print_r($room_info);
            // if ($room_info['from']['id'] == $this->userextension) {
                $people_id = $room_info['to']['user_id'];
            /*}else{
                $people_id = $room_info['from']['id'];
            }*/
//            var_dump($people_id);
//            exit();
//            $page_id   = $room_info['page_id'];
//            $people_id = $people_id;
            $json = $this->mongo_db4x->where(array('people_id' => $people_id))->getOne('people');
//            var_dump($json);
//            print_r($people_id);
//            echo "<pre>";
//            print_r($page_id);
            $properties = array();
            if (!empty($json['properties'])) {
                
            }
            if ($room_info['source'] == 'messenger' || $room_info['source']=='facebook') {
                $json['profile_pic'] =  $json['profile_pic'];
            }elseif($room_info['source']=='livechat'){
                $json['profile_pic'] = base_url('assets/images/avatar_default.jpg');
            }elseif($room_info['source']=='livechat_remote'){
                $json['profile_pic'] = base_url('assets/images/avatar_default.jpg');
            }
            $json['profile_pic'] = str_replace('http:', 'http:', $json['profile_pic']);
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    
   
}
