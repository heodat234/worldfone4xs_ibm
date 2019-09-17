<?php 
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */
$url_libraries = __DIR__ . "/../../libraries";
require_once $url_libraries . "/omnisales-sdk/autoload.php";

use Omnisales\Omnisales;
use Omnisales\OmnisalesApp;

class livechats extends WFF_Controller {
    private $extension = null;
    private $agentname = null;
    private $group = null;
    private $isAdmin;
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('xcrm/local_xmodel', 'xmodel');
        $this->load->model('wfpbx_model', 'wfpbx_model');
        $this->userextension = $this->session->userdata('extension');
        /*$this->extension = $this->session->userdata("extension");
        $this->agentname = $this->session->userdata("agentname");
        $this->isAdmin = $this->session->userdata("isadmin");
        $this->group = $this->xmodel->getCollectionByCondition("groups", array("agent" => $this->extension));*/
        $this->username = $this->session->userdata("username");
        $this->name = $this->session->userdata('name');

        $data_config = array(
            "app_id" => $this->config->item('omnisale_app_id'),
            "app_secret" => $this->config->item('omnisale_app_secret'),
        );

        $this->Omnisales = new Omnisales($data_config);

        $app = new OmnisalesApp($data_config['app_id'], $data_config['app_secret']);
        $this->access_token = $app->getAccessToken();
    }

    public function index($version = 'v1'){
            $this->_build_template();
            $data['title'] = 'Diallist';
            $data['template'] = $this->config->item('template');
            $data['template']['header'] = 'navbar-fixed-top';
            $data['template']['footer'] = 'footer-fixed';

            $this->load->view('templates/worldui/template_start', $data);
            $this->load->view('templates/worldui/page_head', $data);
            $this->load->view('chat/livechats_view');
            $this->load->view('templates/worldui/page_footer');
            $this->load->view('templates/worldui/template_end');
    }
    
    public function getlivechats() {        
        $data = $this->mongo_db->where(array( /*'created_by'  =>  $this->userextension, */'source' => 'livechat_remote' ))->get('livechat_remote_pageapps');
        foreach ($data as $key => $value) {
                /*if (isset($value['group_id']) && !empty($value['group_id'])) {
                    $group_info = $this->mongo_db->where(array('_id' => new mongoId($value['group_id'])))->getOne('groups');
                    if ($group_info) {
                        $data[$key]['group_name'] = $group_info['name'];
                    }
                }*/
            }
        header('Content-Type: application/json');
        echo json_encode($data);
        
    }

    public function getlivechat() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $id = $this->input->get('id');
            $json = $this->mongo_db->where(array(/*'created_by' => $this->userextension,*/ '_id' => new mongoId($id) ))->getOne('livechat_remote_pageapps');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function addlivechat($version = 'v1') {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // var_dump($this->input->post());exit();
            $name        = $this->input->post('name');
            $description = $this->input->post('description');
            $website_url = $this->input->post('website_url');
            $group_id    = $this->input->post('group_id');

            // $this->mongo_db->insert('livechats',array('name' => $name, 'website_url' => $website_url, 'description' => $description, 'group_id' => $group_id, 'created_by' => $this->session->userdata("username"), 'date_added' => time() ));
            $page_array = array(
                'source'  => 'livechat_remote',
                'created_by'    => $this->userextension,
                'page_id'   => '',
                'page_info'   => array(
                    'name'         => $name,
                    'description'  => $description,
                    'website_url'  => $website_url,
                    'page_id'      => '',
                ),
                'status'    => 1,
                'group_id' => $group_id,
                'date_added'  => time(),
            );
            $result = $this->mongo_db->insert('livechat_remote_pageapps',$page_array);
            $this->mongo_db->where(array('_id'  => new mongoId($result->{'$id'}) ))->set(array('page_id' => $result->{'$id'}, 'page_info.page_id'    => $result->{'$id'} ))->update('livechat_remote_pageapps');

            // Ping to omnisale để omni cập nhật
            $this->ping_event_update_chat('add', $result->{'$id'});
            $json['success'] = 'Thêm live chat thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function editlivechat() {
        header('Content-Type: application/json');
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $website_url = $this->input->post('website_url');
            $group_id = $this->input->post('group_id');
            $this->mongo_db->where(array( '_id' => new mongoId($id) ))->set(array('page_info.name' => $name, 'page_info.website_url' => $website_url, 'page_info.description' => $description, 'group_id' => $group_id ))->update('livechat_remote_pageapps');
            $json['success'] = 'Edit live chat thành công';

            // Ping to omnisale để omni cập nhật
            $this->ping_event_update_chat('edit', $id);
        }
        
        echo json_encode($json);
    }

    public function deletelivechat() {
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $id = $this->input->post('id');
            $this->mongo_db->where(array( '_id' => new mongoId($id) ))->delete('livechat_remote_pageapps');

            // Ping to omnisale để omni cập nhật
            $this->ping_event_update_chat('delete', $id);
            $json['success'] = 'Delete live chat thành công';

        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
        
    /*public function delete() {        
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->mongo_db->where(array( '_id' => new mongoId( $this->input->post('id') ) ))->delete('livechats');
        }
        header('Content-Type: application/json');
        echo json_encode($json);                
    }

    public function add() {     
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            if (empty($this->input->post('name'))) {
                $json['error'] = 'Error name!';
            }

            if (empty($this->input->post('website_url'))) {
                $json['error'] = 'Error website_url!';
            }
            if (empty($json['error'])) {
                $data_insert = array(
                    'name'              => $this->input->post('name'),
                    'website_url'       => $this->input->post('website_url'),
                    'fanpagemanager_id' => $this->input->post('fanpagemanager_id'),
                );
                $this->mongo_db->insert('livechats', $data_insert);
            }
        }
        header('Content-Type: application/json');
        echo json_encode($json);                
    }
        
    public function edit() {                
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            if (empty($this->input->post('name'))) {
                $json['error'] = 'Error name!';
            }

            if (empty($this->input->post('website_url'))) {
                $json['error'] = 'Error website_url!';
            }

            if (empty($json['error'])) {
                $data_insert = array(
                    'name'              => $this->input->post('name'),
                    'website_url'       => $this->input->post('website_url'),
                    'fanpagemanager_id' => $this->input->post('fanpagemanager_id'),
                );
                $this->mongo_db->set($data_insert)->update('livechats');
            }
        }
        header('Content-Type: application/json');
        echo json_encode($json);        
    }*/

    public function codeView() {                
        $json = array();
        $id = $this->input->get('id');
        ob_start();?>       
            <textarea name="" class="form-control" rows="5" style="padding: 14px 20px 0px 20px;margin: 10px 0;"><script type='text/javascript'>window._stzq||function(e){e._stzq=[];var t=e._stzq;t.push(["_setAccount",'<?php echo $id; ?>']);var n=e.location.protocol=="https:"?"https:":"http:";var r=document.createElement("script");r.type="text/javascript";r.async=true;r.src=n+"//kim.worldfone.vn/js/chat/loader.js";var i=document.getElementsByTagName("script")[0];i.parentNode.insertBefore(r,i)}(window);</script></textarea>
       <?php
       $list_post = ob_get_contents();
        ob_end_clean();
        echo $list_post;
        // header('Content-Type: application/json');
        // echo json_encode($json);     
    }


    /*SURVEY*/
    public function getChatSurvey(){
        if ($this->input->server('REQUEST_METHOD') === 'GET') {
            $id = $this->input->get('id');
            $data['id'] = $id;
            $chat_web_surver_info = $this->mongo_db->where(array( 'livechat_id' => $id ))->getOne('livechats_remote_survey');

            if (empty($chat_web_surver_info)) {
                $chat_web_surver_info['livechat_id'] = $id;
                $chat_web_surver_info['onoff_surver'] = 1;
                $chat_web_surver_info['data_field'][0] = array(
                    'id'    => 'name',
                    'check'    => '1',
                    'require'    => '1',
                    'field_name'    => 'Họ tên',
                );
                $chat_web_surver_info['data_field'][1] = array(
                    'id'    => 'email',
                    'check'    => '1',
                    'require'    => '1',
                    'field_name'    => 'Email',
                );
                $chat_web_surver_info['data_field'][2] = array(
                    'id'    => 'phone',
                    'check'    => '1',
                    'require'    => '1',
                    'field_name'    => 'Số điện thoại',
                );
                $chat_web_surver_info['color'] = "#1688c5";
                $chat_web_surver_info['title_survey_heading'] = "Bác sỹ tư vấn trực tuyến ";
                $chat_web_surver_info['title_begin_chat'] = "Bắt đầu trò chuyện";
                $chat_web_surver_info['title_instruction_text'] = "Xin chào! Hãy để lại thông tin cần hỗ trợ. Chúng tôi sẵn lòng giải đáp giúp bạn.<br>Hoặc liên hệ Hotline: 1900 6899";
                $chat_web_surver_info['title_ready_text'] = "Chat với chúng tôi!";
                $chat_web_surver_info['title_agentname_text'] = "Tư vấn trực tuyến";
                $chat_web_surver_info['title_ready_ask_us_text'] = "Sẵn sàng hỗ trợ";
                $chat_web_surver_info['title_ready_enter_text'] = "Nhập nội dung và ấn \"Enter\" để chat";

                $chat_web_surver_info['title_ready_welcome_text'] = "Bạn có thắc mắc? Hãy chat với chúng tôi!";
                $chat_web_surver_info['title_ready_busy_text'] = "Hiện tại nhân viên hỗ trợ của chúng tôi đang bận. Xin bạn vui lòng đợi trong giây lát. Cảm ơn vì thời gian của bạn.";

            }
            // return $data['chat_web_surver_info'];
            // var_dump($data['chat_web_surver_info']);
            header('Content-Type: application/json');
        echo json_encode($chat_web_surver_info);
        }
    }

     public function add_field() {     
        $json = array();
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // var_dump($this->input->post()); exit();
            /*if (empty($this->input->post('name'))) {
                $json['error'] = 'Error name!';
            }

            if (empty($this->input->post('website_url'))) {
                $json['error'] = 'Error website_url!';
            }*/
            $data_field = array();
            foreach ($this->input->post('surver_field') as $key => $value) {
                if (empty($value['field_name'])) {
                    $json['error'] = "Error field name";
                    break;
                }

                $data_field[] = array(
                    'id'      => $value['id'],
                    'check'   => isset($value['check']) ? $value['check'] : 0,
                    'require' => $value['require'],
                    'field_name'  => $value['field_name']
                );
            }
            if (empty($this->input->post('onoff_surver'))) {
                $onoff_surver = 0;
            }else{
                $onoff_surver = 1;
            }
            // var_dump($data_field);
            // exit();
            // var_dump($this->input->post('color'));
            if (empty($json['error'])) {
                $data_insert = array(
                    'livechat_id'      => $this->input->post('livechat_id'),
                    'onoff_surver'             => $onoff_surver,
                    'data_field'               => $data_field,
                    'color'                    => $this->input->post('color'),
                    'title_survey_heading'     => $this->input->post('title_survey_heading'),
                    'title_begin_chat'         => $this->input->post('title_begin_chat'),
                    'title_instruction_text'         => $this->input->post('title_instruction_text'),
                    'title_ready_text'         => $this->input->post('title_ready_text'),
                    'title_agentname_text'     => $this->input->post('title_agentname_text'),
                    'title_ready_ask_us_text'  => $this->input->post('title_ready_ask_us_text'),
                    // 'title_ready_ask_us_text'  => $this->input->post('title_ready_ask_us_text'),
                    'title_ready_enter_text'   => $this->input->post('title_ready_enter_text'),
                    'title_ready_welcome_text' => $this->input->post('title_ready_welcome_text'),
                    'title_ready_busy_text'    => $this->input->post('title_ready_busy_text'),
                );
                 // var_dump($data_insert);
                $chat_web_surver_info = $this->mongo_db->where(array( 'livechat_id' => $this->input->post('livechat_id') ))->getOne('livechats_remote_survey');
                if (!empty($chat_web_surver_info)) {
                    $this->mongo_db->where(array( 'livechat_id' => $this->input->post('livechat_id') ))->update('livechats_remote_survey', $data_insert);
                }else{
                    $this->mongo_db->insert('livechats_remote_survey', $data_insert);
                }
                
            }
        }
        header('Content-Type: application/json');
        echo json_encode($json);     
    }

    public function ping_event_update_chat($trigger, $page_id=''){
        $data_sending = array(
            'trigger'   => $trigger,
            'page_id'        => $page_id,
        );        

        try {
            $response = $this->Omnisales->get('livechat_remote/event_update_livechat_remote', $data_sending, $this->access_token);
            $httpcode = $response->gethttpStatusCode();
            $response = $response->getDecodedBody();
            // var_dump($response);
            return true;
        } catch (Exception $e) {
            var_dump($e);
        }

    }

}