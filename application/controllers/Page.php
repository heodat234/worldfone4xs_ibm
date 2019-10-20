<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends WFF_Controller {

    private $default_language = "VIE";

	function __construct()
	{
		parent::__construct();
        $this->load->library("session");
		$this->_build_template();
	}

	function signin()
	{
        // Set language
        $language = $this->input->get("lang");
        $this->session->set_userdata("language", $language ? $language : $this->default_language);

        $this->output->data["css"][] = STEL_PATH . "css/signin.css";
		$this->output->data["js"][] = PROUI_PATH . "js/pages/login.js";
        $this->output->data["js"][] = STEL_PATH . "js/signin.js";
        $data = $this->output->data;
        $data["default_language"] = $this->default_language;
        $data['redirect'] = $this->input->get("redirect");
        $data['error'] = $this->session->flashdata("error");
        if($this->config->item("login_background_img"))
            $data["login_background_img_url"] = $this->config->item("login_background_img_url");
        else $data['login_background_color'] = $this->config->item("login_background_color");
        $data["login_brand_img"] = $this->config->item("login_brand_img");
        // Glogin
        /*$config = array('option'=>'googleplus');
        $this->load->library('googleplus',$config);
        $data['glogin_url'] = $this->googleplus->loginURL();*/
		$this->load->view('page/signin', $data);
	}

    function signin_view()
    {
        // For view signin page
        $language = $this->input->get("lang");
        $this->session->set_userdata("language", $language ? $language : $this->default_language);
        $this->output->data["css"][] = STEL_PATH . "css/signin.css";
        $this->output->data["js"][] = PROUI_PATH . "js/pages/login.js";
        $this->output->data["js"][] = STEL_PATH . "js/signin.js";
        $data = $this->output->data;
        $data["default_language"] = $this->default_language;

        $background_color = $this->input->get("bg_color");
        if($background_color) {
            $data['login_background_color'] = "#" . $background_color;
        } else {
            $data['login_background_img_url'] = $this->input->get("bg_image");
        }
        $data["login_brand_img"] = $this->input->get("brand_img");

        $this->load->view('page/signin_view', $data);
    }

    function signout()
    {
        // Authentication
        $this->load->library("authentication");
        $this->authentication->log_out();
    }

    function error($number = "404")
    {
        $data = $this->output->data;
        $this->load->view("page/{$number}", $data);
    }

    public function _build_template($only_main_content = FALSE)
    {
        $this->config->load('proui');
        $data['template'] = $this->config->item('template');
        $data['template']["version"] = $this->config->item("wff_version");

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
        $proui_js = array('vendor/modernizr-respond.min.js','vendor/bootstrap.min.js','plugins.js','app.js');
        foreach($proui_js as $value) {
            $data['js'][] = PROUI_PATH . "js/{$value}";
        }

        /*
         * Use Template to render default
         */
        $this->output->set_template('proui');
        $this->output->data = $data;
    }
}