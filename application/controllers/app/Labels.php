<?php

/*
  Author: Lê Thị Ngọc Oanh
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class labels extends WFF_Controller {

    public function __construct() {
        parent::__construct();

        
        $this->load->library('mongo_db4x');
        $this->load->model("models_chat/chat_model");
        $this->load->model('wfpbx_model');
        $this->username = $this->session->userdata("user");
        $this->name = $this->session->userdata('name');
    }
    public function index(){
        $this->_build_template();
            $data['title'] = 'Labels';
            $data['template'] = $this->config->item('template');
            $data['template']['header'] = 'navbar-fixed-top';
            $data['template']['main_style'] = 'style-alt';
            $data['template']['footer'] = 'footer-fixed';
            $data['username'] = $this->username;
            $data['name'] = $this->name;
            $userdata = $this->session->userdata;

            $this->_build_template();
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue.min.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/httpVueLoader.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-router.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-i18n.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/select2/select2.min.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/socket/socket.io.js";
            $this->output->data["css"][] = CHAT_PATH . "assets/js/select2/select2.min.css";
            
            $this->load->view('chat/labels_view');
      
    }

    public function getLabels() {

        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $json = $this->mongo_db4x->get('labels');
        }
        header('Content-Type: application/json');
        echo json_encode(array('data' => $json));
    }
    
    public function deleteLabel() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $this->mongo_db4x->where(array( '_id' => new mongoId($id) ))->delete('labels');
            $json['success'] = 'Delete thành công';
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    public function getLabel() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $id = $this->input->get('id');
            $json = $this->mongo_db4x->where(array('_id' => new mongoId($id)))->getOne('labels');
            $users = array();
        }
        header('Content-Type: application/json');
        echo json_encode($json);  
    }
    public function addLabel() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $tennhan= $this->input->post('tennhan');
            $mausac = $this->input->post('mausac');
            $stt = $this->input->post('stt');
            //print_r($mausac);exit();
            $this->mongo_db4x->insert('labels',array('tennhan' => $tennhan, 'mausac' => $mausac, 'stt' => $stt, 'date_added' => time() ));
            $json['success'] = 'Thêm nhãn thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    public function editLabel() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id     = $this->input->post('id');
            $tennhan= $this->input->post('tennhan');
            $mausac = $this->input->post('mausac');
            $stt = $this->input->post('stt');
            $data_edit = array(
                'tennhan'     => $tennhan ,
                'mausac'      => $mausac,
                'stt'         => $stt,
            );
            $this->mongo_db4x->where(array( '_id' => new mongoId($id) ))->set($data_edit)->update('labels');
            $json['success'] = 'Edit nhãn thành công';
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }


}
