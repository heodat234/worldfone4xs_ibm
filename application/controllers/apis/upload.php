<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class upload extends CI_Controller {
	// protected $username;	
 	function __construct() {
        parent::__construct();
        $this->load->config('worldui');
        //     $this->load->model("models_chat/chat_model");
        $this->load->model('models_chat/wfpbx_model');
        $this->omni_webhook_socket_url = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/chat';
        $this->omni_webhook_notifi_createroom = $this->config->item('OMNI_WEBHOOK_SOCKET_URL') . '/api/v2/loadnewroom';
        $this->OMNI_WEBHOOK_LIVECHAT_REMOTE = $this->config->item('OMNI_WEBHOOK_LIVECHAT_REMOTE');
    }

    public function index(){
        // var_dump($_POST);
        // var_dump($_FILES);
        // exit();
        $json = array();
        $config['upload_path']          = FCPATH.'upload/livechat';
        // $config['allowed_types']        = 'gif|jpg|png|mp3|image/jpe|image/jpeg|png|doc|docx|pdf|xlsx|xls|zip|7zip|rar';//
        // $config['allowed_types']        = 'gif|jpg|png|mp3|image/jpe|image/jpeg|jpeg|png|doc|docx|xls|xlsx|application/vnd.ms-excel|zip|7zip|rar|application/x-rar-compressed|application/rar|application/x-rar|application/octet-stream|application/force-download|pdf|application/pdf';
        $config['allowed_types']        = 'gif|jpg|png|mp3|image/jpe|image/jpeg|jpeg|png|doc';
        $config['max_size']             = 25000;

        $new_name = "file".time();
        $config['file_name'] = $new_name;
        if (file_exists(FCPATH.'upload/chatnode') == "") {
            mkdir( FCPATH.'upload/chatnode', 0777, true );
        }

        $this->load->library('upload', $config);
        if ( !$this->upload->do_upload('file')){
            $error = array('error' => $this->upload->display_errors());
            $json['error'] = $error['error'];
        }
        else{
            /*$room_id = $this->input->get('room_id');
            $room_query = $this->mongo_db->where(array("_id" => new MongoId($room_id)))->getOne('chatGroups');
            if ($room_query['from']['id']==$this->username) {
                $receiver_id = $room_query['to']['user_id'];
            }else{
                $receiver_id = $room_query['from']['id'];
            }
            $data = array('upload_data' => $this->upload->data());*/
            $duoifile = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $link = 'http://115.146.126.84/'.'upload/livechat/'.$config['file_name'].'.'.$duoifile;
            // $text = $_FILES['file']['name'];

            if (empty($response['error'])) {
            //Nếu không lỗi
                // $json['success']     = $json['link'];
                $json = array(
                    'name'        =>  $_FILES['file']['name'], 
                    'fileName'    =>  $_FILES['file']['name'],
                    'content'     =>  $link,
                    'contentType' =>  $_FILES['file']['type'],
                    'size'        => $_FILES['file']['size']
                );
                /*
                */
            }else{
            //Nếu lỗi
            }
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
 		
}



?>