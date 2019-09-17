<?php
/*
    Author: Lê Thị Ngọc Oanh
*/

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Quickchat extends WFF_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db4x');
        $this->load->model("models_chat/Chat_model");
        $this->load->model('models_chat/wfpbx_model');
        $this->username = $this->session->userdata("user");
        $this->name = $this->session->userdata('name');
        //$this->load->library('mongo_db');
        $this->load->model('models_chat/quickchat_model');
    }

    public function index() {
            $data['title'] = 'Quick Chat';
            $data['template'] = $this->config->item('template');
            $data['template']['header'] = 'navbar-fixed-top';
            $data['template']['main_style'] = 'style-alt';
            $data['template']['footer'] = 'footer-fixed';
            $data['username'] = $this->username;
            $data['name'] = $this->name;
            $userdata = $this->session->userdata;
//            $data['danhbas'] = $this->getDanhba();
            $data['invite_id'] = $this->input->get('invite');

            $data['supervisors'] = $this->wfpbx_model->getAgent(0, 1, 0);
//            $data['rooms'] = $this->loadRoom();
//            $data['room_join'] = array();
//            foreach ($data['rooms'] as $room) {
//                $data['room_join'][] = $room['room_id'];
//            }

           // $data['room_join'] = json_encode($data['room_join']);

            $data['SERVER_NAME'] = $_SERVER['SERVER_NAME'];

            $this->_build_template();
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue.min.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/httpVueLoader.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-router.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-i18n.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/select2/select2.min.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/socket/socket.io.js";
            $this->output->data["css"][] = CHAT_PATH . "assets/js/select2/select2.min.css";
            $this->load->view('chat/quickChat_view');
    }

    function getQuickChat() {
        header('Content-Type: application/json');
        $data = $this->mongo_db4x->get("quickChat");
        echo json_encode($data);
    }
    
    function getQuickChatDynamicValue() {
        header('Content-Type: application/json');
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        $data = $this->quickchat_model->getQuickChatDynamicValue(array('dropDownName' => 'qickChatDynamicValue'));
        if(empty($data)) {
            $data = array(
                "dropDownName" => "qickChatDynamicValue",
                "value" => array(
                    array(
                        "value" => "name",
                        "text" => "TÊN"
                    ),
                    array(
                        "value" => "phone",
                        "text" => "SĐT"
                    ),
                    array(
                        "value" => "address",
                        "text" => "ĐỊA CHỈ"
                    ),
                    array(
                        "value" => "email",
                        "text" => "EMAIL"
                    ),
                    array(
                        "value" => "hi|xin chào|chào bạn|rất vui được nói chuyện với bạn",
                        "text" => "HI|XIN CHÀO|CHÀO BẠN|RẤT VUI ĐƯỢC NÓI CHUYỆN VỚI BẠN"
                    )
                ),
            );
        }
        echo json_encode($data);
    }

    function insertIntoQuickChat() {
        header('Content-Type: application/json');
        $maucau = $this->input->post("maucau");
        $data = $this->mongo_db4x->insert("quickChat",array('maucau' => $maucau));
        echo json_encode($data);
    }

    function updateQuickChat() {
        header('Content-Type: application/json');
        $id= $this->input->post("_id");
        $maucau= $this->input->post("maucau");
        $data = $this->mongo_db4x->where(array('_id' => new mongoId($id)))->set(array('maucau' => $maucau))->update("quickChat");
        echo json_encode($data);
    }

    function deleteQuickChat() {
        header('Content-Type: application/json');
        $id= $this->input->post('_id');
        $data = $this->mongo_db4x->where(array('_id' => new mongoId($id)))->delete("quickChat");
        echo json_encode($data);
    }
}
