<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class WFF_Controller extends CI_Controller
{   
    public $data = array();

    function __construct()
    {   
        parent::__construct();
        // Load Session
        $this->load->library("session");
        // Authentication
        $this->load->library("authentication");
        $this->authentication->check_login();

        $this->data["permission"] = $this->authentication->check_permissions();

        $this->check_page_module();
    }

    /*
     * Custom function for reroute many type
     * @example: Call to test, Type = "CS" -> call to CS_test if exists 
     */

    function _remap($method, $params = array())
    { 
        $type = $this->session->userdata("type");
        $type_method = $type . "_" . $method;
        // Check type method exists
        $check_method_exists = "";
        if(method_exists($this, $type_method)) {
            $check_method_exists = "type";
        } elseif(method_exists($this, $method)) {
            $check_method_exists = "default";
        }
        if(!$check_method_exists) {
            // Not any method exists
            redirect(base_url("page/error/404"));
        }
        return call_user_func_array(array($this, $check_method_exists == "type" ? $type_method : $method), $params);
    }

    public function _build_template($only_main_content = NULL) {
        $data = $this->data;

        if($only_main_content === NULL) {
            $only_main_content = (bool) $this->input->get("omc");
        }
        $this->config->load('proui');
        $data['template'] = $this->config->item('template');
        $data['template']["version"] = $this->config->item("wff_version");
        // Set preference
        $data['template']["theme"]          = $this->session->userdata("theme");
        $data['template']["page_preloader"] = $this->session->userdata("page_preloader");
        $data['template']["language"]       = $this->session->userdata("language");

        $data["currentUri"] = $this->uri->uri_string();

        $data["only_main_content"] = $only_main_content;

        // Load page_head, page footer to a variable
        $data['page_head'] = $this->load->view('themes/proui/page_head', $data, TRUE);
        $data['page_footer'] = $this->load->view('themes/proui/page_footer', $data, TRUE);

        $data['js'] = $data['css'] = array();
        // JQUERY
        $data['js_nodefer'][] = KENDOUI_PATH . "js/jquery.min.js";
        /*
         * PROUI CSS -- from template_start
         * bootstrap.min.css -- Bootstrap is included in its original form, unaltered
         * plugin.css -- Related styles of various icon packs and plugins
         * main.css -- The main stylesheet of this template. All Bootstrap overwrites are defined in here
         * themes.css -- The themes stylesheet of this template (for using specific theme color in individual elements - must included last
         */
        $proui_css = array('bootstrap.min.css','plugins.css','main.css','themes.css');
        foreach($proui_css as $value) {
            $data['css'][] = PROUI_PATH . "css/{$value}";
        }
        /*
         * PROUI JS -- from template_scripts
         * modernizr-respond.min.js -- Modernizr (browser feature detection library) & Respond.js (enables responsive CSS code on browsers that don't support it, eg IE8)
         */
        // 'vendor/modernizr-respond.min.js'
        $proui_js = array('vendor/bootstrap.min.js','plugins.js','app.js');
        foreach($proui_js as $value) {
            $data['js_nodefer'][] = PROUI_PATH . "js/{$value}";
        }
        
        /* 
         * KENDOUI CSS
         */
        $kendoui_css = array('kendo.common.min.css','kendo.default.min.css');
        foreach($kendoui_css as $value) {
            $data['css'][] = KENDOUI_PATH."styles/{$value}";
        }
        /* 
         * KENDOUI JS
         */
        $kendoui_js = array('kendo.all.min.js');
        foreach($kendoui_js as $value) {
            $data['js'][] = KENDOUI_PATH."js/{$value}";
        }
        /*
         * SWEETALERT JS using bootstrap css
         */
        $data['js'][] = base_url('/public/sweetalert.min.js');
        /* 
         * SOUTH TELECOM COMMON CSS
         */
        $stel_css = array('common.css','kendo.customize.css','popup.css','phone-ring.css');
        foreach($stel_css as $value) {
            $data['css'][] = STEL_PATH . "css/{$value}";
        }

        /* 
         * SOUTH TELECOM COMMON JS
         */
        $stel_js = array("function.js","auth.js","common.js", "record.js", "popup.js");

        if(!$only_main_content) $stel_js = array_merge($stel_js, ["ping.js"]);
        $data['js'][]= "js/func";
        foreach ($stel_js as $value) {
            $data['js'][] = STEL_PATH."js/{$value}";
        }

        /* 
         * CHAT
         */
        /*$chat_js = array('assets/js/socket/socket.io.js', 'assets/js/socket_client.js');
        foreach($chat_js as $value) {
            $data['js_nodefer'][] = CHAT_PATH . "{$value}";
        }*/

        /*
         * Selection js
         */
        if($this->session->userdata("text_tool")) {
            $data["js"][] = base_url() . "public/selection/custom.js";
        }
        
        /*
         * Use Template to render default
         */
        $this->output->set_template('proui');

        $this->output->data = $data;
    }

    protected function check_page_module() {

        $this->data["page_module"] = "";

        if(ENVIRONMENT != "production") {
            $this->data["page_module"] .= $this->load->view("themes/module/taco_assistant", [], TRUE);
        }

        // Benchmark
        $this->output->enable_profiler($this->input->get("cmd_profiler") == "show");

        if($this->input->get("cmd_php_logs")) {
            $php_logs_value = $this->input->get("cmd_php_logs");
            $this->load->helper('file');
            $date = $php_logs_value == "today" ? time() : strtotime($php_logs_value . " day");
            $file_name = "log-". date("Y-m-d", $date) . ".php";
            $content = read_file(APPPATH . '/logs/' . $file_name);
            $this->data["page_module"] .= $this->load->view("themes/module/php_logs", ["content" => $content, "file_name" => $file_name], TRUE);
        }

        if($this->input->get("cmd_js_logs")) {
            $value = $this->input->get("cmd_js_logs");
            $js_log_timestamp = $value == "today" ? strtotime("today midnight") : strtotime($value . " day midnight");
            $this->data["page_module"] .= $this->load->view("themes/module/js_logs", ["js_log_timestamp" => $js_log_timestamp], TRUE);
        }
    }
}