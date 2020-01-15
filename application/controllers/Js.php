<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Js extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		header('Content-type: text/javascript');
        $this->load->model("language_model");
	}

	function env()
	{
		$currentUri = $this->input->get("currentUri");

        $wff_env = $this->config->item("wff_env");
		// Load Session
        $this->load->library("session");
        // Load Parser
		$this->load->library('parser');
		// Load env
        $this->config->load('env');
        $env = $this->config->item('v1');

		/*
         * SET ENV const javascript
         */
        // System config
      
        $env["currentUri"]      = $currentUri;
        $env["softphone"]       = $this->config->item("softphone");
        $env["brandTitle"]      = $this->config->item("brand_title");
        $env["brandLogo"]       = $this->config->item("brand_logo");
        $env["use_worker"]      = $this->config->item("use_worker");
        $env["short_key_ipphone"] = $this->config->item("short_key_ipphone");

        // Preference
        $extension = $this->session->userdata("extension");
        $env["extension"]       = $extension;
        $env["ringtone"]        = $this->session->userdata("ringtone");
        $env["avatar"]          = $this->session->userdata("avatar");
        $env["language"]        = $this->session->userdata("language");
        $env["type"]            = $this->session->userdata("type");
        $env["typename"]        = $this->session->userdata("typename");
        $env["my_session_id"]   = $this->session->userdata("my_session_id");
        $env["agentname"]       = $this->session->userdata("agentname")
            . ($this->session->userdata("test_mode") ? " (TM)" : "");
        $env["sound_effect"]    = $this->session->userdata("sound_effect");
        $env["role_name"]       = $this->session->userdata("role_name");

        // Queues which extension belong to
        $this->load->model("group_model");
        $env["queues"] = $this->group_model->queues_of_extension($extension);

        $data['ENV'] = json_encode($env);

        $data['loader_layer'] = $this->config->item("loader_layer");

        // convertExtensionToAgentname
        $this->load->library("mongo_private");
        $users = $this->mongo_private->get(set_sub_collection("User"));
        $convertExtensionToAgentname = new stdClass();
        foreach ($users as $doc) {
            $convertExtensionToAgentname->$doc["extension"] = $doc["agentname"];
        }
        $data["convertExtensionToAgentname"] = json_encode($convertExtensionToAgentname);
        
		$view = $this->parser->parse('js/env', $data, TRUE);
        $view = $this->language_model->translate($view, "CONTENT");
        echo $this->language_model->translate($view, "NOTIFICATION");
	}

    function func()
    {
        $view = $this->load->view('js/func', array(), TRUE);
        $view = $this->language_model->translate($view, "CONTENT", "", "", "@", FALSE);
        echo $this->language_model->translate($view, "NOTIFICATION", "", "", "@", FALSE);
    }
}
