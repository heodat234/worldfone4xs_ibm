<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */

class users extends WFF_Controller {

    public function __construct() {
        parent::__construct();
       // $this->username = $this->session->userdata("username");
        $this->username = $this->session->userdata('extension');
        $this->name = $this->session->userdata('name');
         //print_r("dd");exit();
    }
   
    public function adduserAccount($version = 'v1') {
        wff_checkPermission('edit');
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // var_dump($this->input->post());exit();
            $username = $this->input->post('username');
            $email = $this->input->post('email');
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $role = $this->input->post('role');
            $groups = $this->input->post('groups') ? $this->input->post('groups') : array();
            $password = $this->input->post('password');
            $user_info = $this->mongo_db->where(array('username' => $username ))->getOne('users');
            if (!empty($user_info)) {
                $json['error'] = 'Username đã tồn tại';
            }
            if (empty( $json['error'])) {
                $data_insert = array(
                    'parent_user' => $this->username,
                    'firstname'  => $firstname,
                    'username'   => $username, 
                    'lastname'   => $lastname,
                    'email'      => $email, 
                    'role'       => $role,
                    'password'   => md5($password),
                    'status'     => 1,
                    'date_added' => time(),
                );
                $user_info = $this->mongo_db->insert('users', $data_insert);
                $id = $user_info->{'$id'};
                // if (!empty($groups)) {
                //     var_dump($groups);
                //     foreach ($groups as $key => $group_id) {
                //         $group_info = $this->mongo_db->where(array('_id' => new mongoId($group_id)))->getOne('groups');
                //         if ($group_info) {
                //            var_dump($group_info);
                //         }
                //     }
                // }
                $json['success'] = 'Thêm Account thành công';
            }

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function edituserAccount($version = 'v1') {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $email = $this->input->post('email');
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $role = $this->input->post('role');
            $groups = $this->input->post('groups') ? $this->input->post('groups') : array();
            $password = $this->input->post('password');
            
            if (empty( $json['error'])) {
                $data_update = array(
                    'parent_user' => $this->username,
                    'firstname'  => $firstname,
                    'lastname'   => $lastname,
                    'email'      => $email, 
                    'role'       => $role,                    
                    'status'     => 1,
                );
                $user_info = $this->mongo_db->where(array( '_id' => new mongoId($id) ))->set($data_update)->update('users');
                
                if (!empty($password)) {
                    $this->mongo_db->where(array( '_id' => new mongoId($id) ))->set(array('password'   => md5($password)))->update('users');
                }
                $user_info = $this->mongo_db->where(array( '_id' => new mongoId($id) ))->getOne('users');
                $get_group_by_user = $this->mongo_db->where(array('users' => $user_info['username']))->get('groups');
                if (!empty($groups)) {
                    //code thêm
                    foreach ($groups as $key => $group_id) {
                        $group_info = $this->mongo_db->where(array('_id' => new mongoId($group_id)))->getOne('groups');
                        if ($group_info) {
                            $group_users = (array)$group_info['users']; 
                            $user_info_1 = $this->mongo_db->where(array( '_id' => new mongoId($id) ))->getOne('users');
                            if (($key1 = array_search($user_info_1['username'], $group_users)) !== false) {
                                unset($group_users[$key1]);                                
                            }
                            //push và sort array bởi vì khi key ko bắt đầu bằng 0 thì mảng trong mongo sẽ được chuyển thành obj và k search dc user
                            array_push($group_users, $user_info_1['username']);
                            sort($group_users);

                            $this->mongo_db->where(array('_id' => new mongoId($group_id)))->set(array( 'users' => $group_users ))->update('groups');
                        }
                    }
                    //code xóa 
                    foreach ($get_group_by_user as $key => $group) {
                        $group_users = (array)$group['users'];
                        // Kiểm tra nếu không chọn thì xóa nó đi
                        if (!in_array($group['_id']->{'$id'}, $groups)) {
                            // array_push($group_users, $user_info['username']);
                            if (($key1 = array_search($user_info['username'], $group_users)) !== false) {
                                unset($group_users[$key1]);                                
                            }
                            // var_dump($group['_id']->{'$id'});          
                            // var_dump($group_users);
                            sort($group_users);
                            $this->mongo_db->where(array('_id' => new mongoId($group['_id']->{'$id'})))->set(array( 'users' => $group_users ))->update('groups');
                        
                        }
                        
                        
                    }



                }else{//Nếu không chọn nhóm nào
                    foreach ($get_group_by_user as $key => $group) {
                        $group_users = (array)$group['users'];
                        // var_dump($group_users);
                        
                        // Kiểm tra nếu không chọn thì xóa nó đi
                        if (!in_array($group['_id']->{'$id'}, $groups)) {                         
                            sort($group_users);
                            $this->mongo_db->where(array('_id' => new mongoId($group['_id']->{'$id'})))->set(array( 'users' => array() ))->update('groups');                        
                        }
                        
                        
                    }
                }
                
                $json['success'] = 'Sửa Account thành công';
            }
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function deleteuserAccount() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $this->mongo_db->where(array( '_id' => new mongoId($id) ))->delete('users');
            $json['success'] = 'Delete Account thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

   
    public function getuserAccounts() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $id = $this->input->get('id');
            $json = $this->mongo_db->where(array('parent_user' => $this->username ))->get('users');
            foreach ($json as $key => $value) {
                $get_group_by_user = $this->mongo_db->where(array( 'users' => $value['username']))->get('groups');
                foreach ($get_group_by_user as $group) {
                    $json[$key]['groups'][] = $group['name'];
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    public function getuserAccount() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $id = $this->input->get('id');
            $json = $this->mongo_db->where(array('_id' => new mongoId($id) ))->getOne('users');
            if ($json) {
                $get_group_by_user = $this->mongo_db->where(array( 'users' => $json['username']))->get('groups');
                foreach ($get_group_by_user as $group) {
                    $json['groups'][] = $group['_id']->{'$id'};
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getAssigns() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $data_assigns = array();
            $user_info = $this->mongo_db->where(array('username' => $this->username))->getOne('users');
            if (isset($user_info['parent_user'])) {
                $data_assigns_query = $this->mongo_db->where(array( 'parent_user' => $user_info['parent_user'] ))->get('users');
            }else{
                $data_assigns_query = $this->mongo_db->where(array( 'parent_user' => $this->username ))->get('users');
            }
            foreach ($data_assigns_query as $user) {
                if ($user['username']==$this->username) {
                    continue;
                }
                $data_assigns[] = array(
                    'username'  => $user['username'],
                    'name'      => $user['lastname'].''.$user['firstname'],
                );
            }
            // var_dump($user_info);
        }
        header('Content-Type: application/json');
        echo json_encode($data_assigns);
    }
    public function transferTo() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $room_id = $this->input->post('room_id');
            $username = $this->input->post('username');
            $transfer_message = $this->input->post('transfer_message');
            
            $insert_noti = array(
                    'source'    => 'transfer',
                    'room_id'   => $room_id,
                    'sender_id' => $this->username,
                    'send_to'   => $username,
                    'sender_info' => array(
                        'user_id'     => $this->username,
                        'type'        => 'agent',
                    ),
                    'title'       => 'transfer từ '. $this->username,
                    'text'       => $transfer_message,
                    'users'     => array($username),
                    'date_added' => time(),
            );
            
            $user_info = $this->getUserInfoByUsername($this->username);
            $insert_noti['avatar'] = $user_info['profile_pic'];
            $this->mongo_db->insert('chatNotifi',$insert_noti);

            $json['success'] = $insert_noti;
            
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getUserInfoByUsername($username){
        $user_info = $this->mongo_db->where(array( 'username' => $username ))->getOne('users');
        if (!empty($user_info['profile_pic'])) {
            $profile_pic = base_url().$user_info['profile_pic'];
        }else{
            $profile_pic = base_url('assets/images/avatar_default.jpg');
        }
        $info = array(
            'name'        => $user_info['lastname'].' '.$user_info['firstname'],
            'username'   => $username,
            'profile_pic' => $profile_pic,
        );
        return $info;
    }
    
    public function getInfo(){
       // print_r($this->username);exit();
        $user_info = $this->mongo_db->where(array( 'username' => $this->username ))->getOne('users');
        if (!empty($user_info['profile_pic'])) {
            $profile_pic = $user_info['profile_pic'];
        }else{
            $profile_pic = base_url('assets/images/avatar_default.jpg');
        }
        $info = array(
            'name'        => $user_info['lastname'].' '.$user_info['firstname'],
            'username'    => $user_info['username'],
            'profile_pic' => $profile_pic,
            'logoutKey'   => logoutLink(),
        );
        header('Content-Type: application/json');
        echo json_encode($info);
    }
   
}
