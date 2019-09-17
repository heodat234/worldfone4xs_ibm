<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */

class transfer extends WFF_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db4x');
        $this->userextension = $this->session->userdata('extension');
        $this->username = $this->session->userdata('extension');
        $this->name = $this->session->userdata('name');
    }
    public function index($version = 'v1') {
        $data['userextension'] = $this->userextension;
    }
    public function getAssigns() {
        // $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $room_id = $this->input->get('room_id');
            $group_id = $this->mongo_db4x->where('_id',new mongoId($room_id))->getOne('chatGroups');
            $page_app = $this->mongo_db4x->where(array('id' => $group_id['page_id']))->getOne('pageapps');
            $user_info = $this->mongo_db4x->where(array('_id'=>new mongoId($page_app['group_id'])))->getOne('chatGroup_Manager');

            header('Content-Type: application/json');
            echo json_encode(array("data"=> $user_info,"extension"=>$this->userextension));
        }
        
    }

    public function transferTo() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $room_id = $this->input->post('room_id');
            $username = $this->input->post('username');
            $transfer_message = $this->input->post('transfer_message');
            $chat_group = $this->mongo_db4x->where('_id',new mongoId($room_id))->getOne("chatGroups");
            if ($chat_group['type'] == "new_facebook_chat") {
                $data_line = "facebook";
            } else if ($chat_group['type'] == "new_livechat_chat") {
                $data_line = "livechat";
            } else if ($chat_group['type'] == "new_zalo_chat") {
                $data_line = "zalo";
            }
            $insert_noti = array(
                'source'    => 'transfer',
                'trigger'   => $chat_group['trigger'],
                'line'      => 'transfer',
                'page_id'   => $chat_group['page_id'],
                'type'      => 'transfer',
                'room_id'   => $room_id,
                'sender_id' => $this->username,
                'send_to'   => $username,
                'sender_info' => array(
                    'user_id' => $this->username,
                    'type'  => 'agent',
                    'name'  => $chat_group['to']['username'],
                ),
                'title' => 'transfer từ ' . $this->username,
                'text' => $transfer_message,
                'users' => array($username),
                'date_added' => time(),
            );
           
            $user_info = $this->getUserInfoByUsername($this->username);
            $insert_noti['avatar'] = $user_info['profile_pic'];
            $this->mongo_db4x->insert('chatNotifi', $insert_noti);

            $json['success'] = $insert_noti;
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getUserInfoByUsername($username) {
        $user_info = $this->mongo_db4x->where(array('supervisor' => $this->userextension))->getOne('chatGroup_Manager');
        if (!empty($user_info['profile_pic'])) {
            $profile_pic = base_url() . $user_info['profile_pic'];
        } else {
            $profile_pic = base_url('assets/images/avatar_default.jpg');
        }
        $info = array(
//            'name' => $user_info['lastname'] . ' ' . $user_info['firstname'],
            'username' => $username,
            'profile_pic' => $profile_pic,
        );
        return $info;
    }

    public function getInfo() {
        // print_r($this->username);exit();
        $user_info = $this->mongo_db4x->where(array('username' => $this->username))->getOne('users');
        if (!empty($user_info['profile_pic'])) {
            $profile_pic = $user_info['profile_pic'];
        } else {
            $profile_pic = base_url('assets/images/avatar_default.jpg');
        }
        $info = array(
            'name' => $user_info['lastname'] . ' ' . $user_info['firstname'],
            'username' => $user_info['username'],
            'profile_pic' => $profile_pic,
            'logoutKey' => logoutLink(),
        );
        header('Content-Type: application/json');
        echo json_encode($info);
    }

}
