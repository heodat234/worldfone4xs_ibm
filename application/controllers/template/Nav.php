<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Nav extends CI_Controller {
	function index()
	{
		$this->load->library("authentication");
		$nav = $this->authentication->get_nav();
		$data["template"] = array("active_page" => $this->input->get("currentUri"));
		$data["primary_nav"] = $nav;
		$this->load->view("templates/sidebar/nav", $data);
	}

	function from_role_id($id = "")
    {
        $this->load->library("session");
        $this->config->load("env");
        $env = $this->config->item('v1');
        $query = http_build_query(array(
            "type" => $this->session->userdata("type"),
            "lang" => $this->session->userdata("language")
        ));
        $ch = curl_init("{$env['vApi']}permission/nav_from_role_id/{$id}?{$query}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode == 200)
            $nav = json_decode($result, TRUE);
        else $nav = array();
        $data["template"] = array("active_page" => "");
        $data["primary_nav"] = $nav;
        $view = $this->load->view("templates/sidebar/nav", $data, TRUE);
        echo str_replace("href", "data-href", $view);
    }

    function from_privileges()
    {
        $privilegesArr = json_decode($this->input->get("q"), TRUE);
        $modules = array();
        if($privilegesArr) {
            foreach ($privilegesArr as $privilege) {
                if($privilege["view"]) {
                    $modules[] = $privilege["module_id"];
                }
            }
        }
        $this->load->library("session");
        $this->config->load("env");
        $env = $this->config->item('v1');
        $query = http_build_query(array(
            "type"          => $this->session->userdata("type"),
            "lang"          => $this->session->userdata("language"),
            "modules"       => $modules
        ));
        //pre("{$env['vApi']}permission/nav_from_modules?{$query}");
        $ch = curl_init("{$env['vApi']}permission/nav_from_modules?{$query}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode == 200)
            $nav = json_decode($result, TRUE);
        else $nav = array();
        $data["template"] = array("active_page" => "");
        $data["primary_nav"] = $nav;
        $view = $this->load->view("templates/sidebar/nav", $data, TRUE);
        echo str_replace("href", "data-href", $view);
    }
}