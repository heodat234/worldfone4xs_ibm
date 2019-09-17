<?php 
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Copyright © 2014 South Telecom
 */


class livechat_config extends WFF_Controller {
    private $extension = null;
    private $agentname = null;
    private $group = null;
    private $isAdmin;
    function __construct()
    {
        parent::__construct();
        $this->load->config('worldui');
        $this->load->model('xcrm/local_xmodel', 'xmodel');
        $this->load->model('wfpbx_model', 'wfpbx_model');
        /*$this->extension = $this->session->userdata("extension");
        $this->agentname = $this->session->userdata("agentname");
        $this->isAdmin = $this->session->userdata("isadmin");
        $this->group = $this->xmodel->getCollectionByCondition("groups", array("agent" => $this->extension));*/
        $this->username = $this->session->userdata("username");
        $this->name = $this->session->userdata('name');
    }

    public function index($version = 'v1'){
        if( $version == 'v1' ) {
            $data['title'] = 'Diallist';
            $data['template'] = $this->config->item('template');
            $data['template']['header'] = 'navbar-fixed-top';
            $data['template']['footer'] = 'footer-fixed';
            if (!empty($this->input->get('id'))) {
                $data['livechat_id'] = $this->input->get('id');
            }else{
                return;
            }
            
            $this->load->view('templates/worldui/template_start', $data);
            $this->load->view('templates/worldui/page_head', $data);
            $this->load->view('chat/livechat_config_view');
            $this->load->view('templates/worldui/page_footer');
            $this->load->view('templates/worldui/template_end');
        }
    }

}