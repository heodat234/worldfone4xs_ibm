<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
$url_libraries = __DIR__ . "/../../libraries";
require_once $url_libraries . "/omnisales-sdk/autoload.php";

use Omnisales\Omnisales;
use Omnisales\OmnisalesApp;

class fanpage extends WFF_Controller {

    private $access_token;
    private $Omnisales;

    public function __construct() {
        parent::__construct();
        
        $this->load->library('mongo_db4x');
        $this->load->model("models_chat/chat_model");
        $this->load->model('models_chat/wfpbx_model');
        $this->load->model('models_chat/facebook_model');
        $this->username = $this->session->userdata('extension');
        $this->load->model('models_chat/user_model');
        $this->name = $this->session->userdata('name');
        $this->parent_user = $this->session->userdata('parent_user');
        $this->parent_id = $this->session->userdata('parent_id');

        // $user_info = $this->user_model->getProfileByUserName($this->username);
        // $this->avatar = $user_info['profile_pic'];

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
            $data['title'] = 'Fanpage';
            $data['template'] = $this->config->item('template');
            $data['template']['header'] = 'navbar-fixed-top';
            $data['template']['main_style'] = 'style-alt';
            $data['template']['footer'] = 'footer-fixed';
            $data['username'] = $this->username;
            $data['name'] = $this->name;
            $userdata = $this->session->userdata;

            /*$this->load->view('templates/worldui/template_start', $data);
            $this->load->view('templates/worldui/page_head', $data);
            $this->load->view('chat/fanpage_view');
            $this->load->view('templates/worldui/page_footer');
            $this->load->view('templates/worldui/template_end');*/
            $this->_build_template();
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue.min.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/httpVueLoader.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-router.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/vue-i18n.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/select2/select2.min.js";
            $this->output->data["js_nodefer"][] = CHAT_PATH . "assets/js/socket/socket.io.js";
            $this->output->data["css"][] = CHAT_PATH . "assets/js/select2/select2.min.css";
            
            $this->load->view('chat/fanpage_view');
    }

    //start code Tram synFanpage 25122018
    public function syncFanpage() {
        $data_sending = array();
        $response = $this->Omnisales->get('app/getpages', $data_sending, $this->access_token);
        $httpcode = $response->gethttpStatusCode();
        $response = $response->getDecodedBody();
        $data_array = array();
        foreach ($response['data'] as $value) {
            $data_array = array(
                'id' => $value['id'],
                'source' => $value['source'],
                'name' => $value['name'],
                'picture' => ($value['picture'] != "") ? $value['picture'] : "https://static.xx.fbcdn.net/rsrc.php/v3/y6/r/_xS7LcbxKS4.gif",
                //'group_id' => isset($value['group_id']) ? $value['group_id'] : '',
                //'group_name' => isset($value['group_name']) ? $value['group_name'] : '',
                'date_added' => time(),
                'created_by' => $this->username
            );
            $result_read = $this->mongo_db4x->where(array('id' => $data_array['id']))->getOne("pageapps");
            if (empty($result_read)) {
                $this->mongo_db4x->insert('pageapps', $data_array);
            } else {

                $this->mongo_db4x->where(array("id" => $data_array['id']))->set($data_array)->update('pageapps');
            }
        }
        $json['success'] = 'Sync Done';

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    //endcode
    //start code Tram 26122018 getFanpage
    public function getFanpages() {
        $result = $this->mongo_db4x->order_by(array('group_name' => 'DESC'))->get('pageapps');
        echo json_encode($result);
    }

    public function getChatGroupManager() {
        $result = $this->mongo_db4x->get('chatGroup_Manager');
        echo json_encode($result);
    }

    public function getFanpage() {
        $id = $this->input->get('id');
        $result = $this->mongo_db4x->where(array('_id' => new MongoId($id)))->getOne('pageapps');
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function editFanpage() {
        $id = $this->input->post('id');
        
        $group_id = $this->input->post('group_mn');
        $group_manager = $this->mongo_db4x->where(array('_id' => new MongoId($group_id)))->getOne('chatGroup_Manager');

        $result = $this->mongo_db4x->where(array('_id' => new MongoId($id)))->set(array('group_id' => $group_id, 'group_name' => $group_manager['name']))->update('pageapps');
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    

}
