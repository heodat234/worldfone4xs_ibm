<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
$url_libraries = __DIR__ . "/../../libraries";
require_once $url_libraries . "/omnisales-sdk/autoload.php";

use Omnisales\Omnisales;
use Omnisales\OmnisalesApp;

class Chat_group_manager extends WFF_Controller {

    private $access_token;
    private $Omnisales;

    public function __construct() {
        parent::__construct();
        
        $this->load->model("models_chat/chat_model");
        $this->load->model('pbx_model');
        // $this->load->model('models_chat/facebook_model');
        $this->username = $this->session->userdata('extension');
//        $this->load->model('models_chat/user_model');
        $this->name = $this->session->userdata('name');
        $this->parent_user = $this->session->userdata('parent_user');
        $this->parent_id = $this->session->userdata('parent_id');

        $this->userextension    = $this->session->userdata('extension');
        $this->agentname        = $this->session->userdata('agentname');


//        $user_info = $this->user_model->getProfileByUserName($this->username);
//        $this->avatar = $user_info['profile_pic'];

        $data_config = array(
            "app_id" => $this->config->item('omnisale_app_id'),
            "app_secret" => $this->config->item('omnisale_app_secret'),
        );
        // var_dump($data_config);
        //http://192.168.16.45:8021/apis/webhook/chat

        $this->Omnisales = new Omnisales($data_config);

        $app = new OmnisalesApp($data_config['app_id'], $data_config['app_secret']);
        $this->access_token = $app->getAccessToken();
    }

    public function index($version = 'v1') {

        if ($version === 'v1') {
            $data['title'] = 'Fanpage';
            $data['template'] = $this->config->item('template');
            $data['template']['header'] = 'navbar-fixed-top';
            $data['template']['main_style'] = 'style-alt';
            $data['template']['footer'] = 'footer-fixed';
            $data['username'] = $this->username;
            $data['name'] = $this->name;
            $userdata = $this->session->userdata;
            
            // $data['supervisors'] = $this->pbx_model->getAgent(0, 1, 0);
            // var_dump($data['supervisors']);
            // var_dump($this->wfpbx_model->getAgent(0, 1, 0));
            // var_dump($data['supervisors']);
            // $data['agents'] = $this->pbx_model->getAgent(0, 0, 0);

            $this->load->model("user_model");
            $data['agents'] = $this->user_model->all();
            $this->_build_template();
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/httpVueLoader.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-router.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-i18n.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/select2/select2.min.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/socket/socket.io.js";
            $this->output->data["css"][] = CHAT_PATH . "assets/js/select2/select2.min.css";

            // $this->load->view('templates/worldui/template_start', $data);
            // $this->load->view('templates/worldui/page_head', $data);
            $this->load->view('chat/chat_group_manager_view', $data);
            // $this->load->view('templates/worldui/page_footer');
            // $this->load->view('templates/worldui/template_end');
        }
    }

    public function addGroup($version = 'v1') {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $agents = $this->input->post('agents') ? $this->input->post('agents') : array();
            $supervisor = $this->input->post('supervisor');
            $data_insert = array(
                'name'            => $name,
                'description'     => $description,
                'supervisor'      => $supervisor,
                'agents'           => $agents,
                'created_by'      => $this->userextension,
                'date_added'      => time(),
            );
            // var_dump($data_insert);exit();
            $this->mongo_db4x->insert('chatGroup_Manager', $data_insert);
            $json['success'] = 'Thêm Nhóm thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function editGroup($version = 'v1') {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $agents = $this->input->post('agents') ? $this->input->post('agents') : array();
            $supervisor = $this->input->post('supervisor');
            $data_insert = array(
                'name'            => $name,
                'description'     => $description,
                'supervisor'      => $supervisor,
                'agents'           => $agents,
                'created_by'      => $this->userextension,
                'date_added'      => time(),
            );
            $this->mongo_db4x->where(array( '_id' => new mongoId($id) ))->set($data_insert)->update('chatGroup_Manager');
            $json['success'] = 'Edit Nhóm thành công';
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function deleteGroups() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $this->mongo_db4x->where(array( '_id' => new mongoId($id) ))->delete('chatGroup_Manager');
            $json['success'] = 'Delete Nhóm thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function getGroups() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $json = $this->mongo_db4x/*->where(array('created_by' => $this->userextension))*/->order_by(array('date_added' => -1))->get('chatGroup_Manager');            
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    public function getGroup() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $id = $this->input->get('id');
            $json = $this->mongo_db4x->where(array('_id' => new mongoId($id)))->getOne('chatGroup_Manager');
            $users = array();
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

}
