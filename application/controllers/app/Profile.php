<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */


class profile extends WFF_Controller
{
    private $pbx_customer_code, $extension, $agentname, $username;

    public function __construct()
    {
        parent::__construct();
        // $this->load->config('worldui');
        //$xcrm = $this->session->userdata('externalcrm_type');
        //$this->pbx_customer_code = $this->session->userdata('pbx_customer_code');
        $this->name = $this->session->userdata("name");
        $this->agentname = $this->session->userdata('agentname');
        $this->username = $this->session->userdata('extension');
        // $this->load->model('xcrm/local_xmodel', 'xmodel');
        // $this->load->model('api_model', 'apimodel');
    }

    public function index($version = 'v1') {
        // $phone = array('0984763100', '984763100', '+84984763100');
        // foreach($phone as $ele) {
        //    echo 'non'.MobileNumberFormat::normalize($ele).'-';
        //    echo 'have'.MobileNumberFormat::normalize('0'.$ele).'-';
        // }
        // if( !$this->input->get('id') ) {
        //     redirect(base_url());
        //     exit();
        // }

        // if ($version === 'v1') {
        //     $data['title'] = 'Diallist';
        //     $data['template'] = $this->config->item('template');
        //     $data['template']['header'] = 'navbar-fixed-top';
        //     $data['template']['footer'] = 'footer-fixed';
        //     $this->load->view('templates/worldui/template_start', $data);
        //     $this->load->view('templates/worldui/page_head', $data);
        //     if( $this->input->get('service_type') ) {
        //         $this->load->view('customers/local/detail_kim_xview');
        //     } else {
        //         $this->load->view('customers/local/detail_xview');
        //     }
            
        //     $this->load->view('templates/worldui/page_footer');
        //     $this->load->view('templates/worldui/template_end');
        // }
    }
    public function uploadProfilePic(){
        $json = array();
        $config['upload_path']          = './upload/users/avatar';
        $config['allowed_types']        = 'image/jpe|jpg|image/jpeg|jpeg|png|sgv';

        $config['max_size']             = 25000;

        $new_name = "file".time();
        
        $config['file_name'] = $new_name;

        if (file_exists(FCPATH.'/upload/users/avatar') == "") {
            mkdir( FCPATH.'/upload/users/avatar', 0777, true );
        }

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('file')){
            $error = array('error' => $this->upload->display_errors());
            $json['error'] = $error['error'];
        }
        else{
            $room_id = $this->input->get('room_id');
            $data = array('upload_data' => $this->upload->data());
            $duoifile = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $json['link'] = 'upload/users/avatar/'.$config['file_name'].'.'.$duoifile;
            $text = $_FILES['file']['name'];
            /**/
            if(in_array($duoifile,array("jpg","jpe","jpeg","gif","png")) ) {
                $type = 'image';
            }else{
                $type = 'file';
            }            
            $this->mongo_db->where(array( 'username' => $this->username ))->set(array( 'profile_pic' => base_url().$json['link'] ))->update('users');
            $json['success']    = base_url().$json['link'];
            // header('Content-Type: application/json');
            // echo json_encode($json);
            /**/
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getUserProfile(){
//        $user_info = $this->mongo_db->where(array('username' => $this->session->userdata('extension')))->select(array("lastname", "firstname","profile_pic","email","telephone","username"))->getOne('users');
//        if (!empty($user_info)) {
//            $user_info['name'] = $user_info['lastname']. ' '.$user_info['firstname'];
//            if (!empty($user_info['profile_pic'])) {
//                $user_info['profile_pic'] = $user_info['profile_pic'];
//            }else{
//                $user_info['profile_pic'] = base_url('assets/images/avatar_default.jpg');
//            }
//            
//        }
//        header('Content-Type: application/json');
//        echo json_encode($user_info);
        $user_info['name'] = $this->agentname;
		$user_info['username'] = $this->username;
		$user_info['profile_pic'] = base_url('assets/images/avatar_default.jpg');
        header('Content-Type: application/json');
        echo json_encode($user_info);
    }

    public function getUserAppInformation(){
        $user_info = $this->mongo_db->where(array('username' => $this->session->userdata('username')))->getOne('users');
        header('Content-Type: application/json');
        if (!isset($user_info['app_id'])) {
            $this->mongo_db->where(array( 'username' => $this->session->userdata('username') ))->set(array('app_secret' => $this->create_secretkey(), 'app_id' => $this->create_appid()))->update('users');
        }
        $user_info = $this->mongo_db->where(array('username' => $this->session->userdata('username')))->select(array("app_id", "app_secret","webhook_url"))->getOne('users');
        header('Content-Type: application/json');
        echo json_encode($user_info);
    }

    public function updateProfile(){       
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $username = $this->username;
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $telephone = $this->input->post('telephone');
            $email = $this->input->post('email');
            $this->mongo_db->where(array( 'username' => $username ))->set(array('firstname' => $firstname, 'lastname' => $lastname, 'telephone' => $telephone, 'email' => $email ))->update('users');
            $json['success'] = 'Edit User thành công';
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function change_password(){        
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $username = $this->username;
            $password = $this->input->post('password');
            $newpassword = $this->input->post('newpassword');
            $renrepassword = $this->input->post('renrepassword');
            // $telephone = $this->input->post('telephone');
            // $email = $this->input->post('email');
            $check_password = $this->mongo_db->where(array( 'username' => $username, 'password' => md5($password) ))->getOne('users');
            if (empty($newpassword)) {
                $json['error'] = 'Vui lòng nhập thông tin bắt buộc';
            }
            if (empty($json['error'])) {
                if ($check_password) {
                    $this->mongo_db->where(array( 'username' => $username ))->set(array( 'password' => md5($renrepassword) ))->update('users');
                    $json['success'] = 'Cập nhật mật khẩu thành công';
                }else{
                    $json['error'] = 'Sai mật khẩu';
                }
            }
            
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function create_appid(){
        $characters = '1234567890';
        $key = '';
        for ($i = 0; $i < 15; $i++){
            $rand = $characters[mt_rand(0, strlen($characters) - 1)];
            if ($rand==0 && $i==0) {
                $i--;
            }else{
                $key .= $rand;
            }
            
        }
        $check_appid_exist = $this->mongo_db->where(array('app_id' => $key))->getOne('users');
        if (!empty($check_appid_exist)) {
            $key = $this->create_appid();
        }
        return $key;
    }
    public function create_secretkey(){
        $key = md5(microtime().rand());
        $check_key_exist = $this->mongo_db->where(array('app_secret' => $key))->getOne('users');
        if (!empty($check_key_exist)) {
            $key = $this->create_secretkey();
        }
        return $key;
    }

    
}
