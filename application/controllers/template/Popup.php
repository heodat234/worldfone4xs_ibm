<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Popup extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library("session");
    }

	function index($id = "")
    {
        $this->load->model("language_model");
        $user_type = $this->session->userdata("type");
        switch ($user_type) {
            case "2":
                $view = $this->telesale();
                break;
            default:
                $view = $this->loan();
                break;
        }
        echo $this->language_model->translate($view, "CONTENT");
    }

    private function telesale()
    {
        $data["callData"] = $callData = json_decode($this->input->get("q"), TRUE);
        if(isset($callData["dialid"])) {
            $view = $this->load->view("templates/popup/telesale/" . $callData["dialtype"], $data, TRUE);
        } else {
            $view = $this->load->view("templates/popup/telesale/default", $data, TRUE);
        }
        return $view;
    }

    private function loan()
    {
        $data["callData"] = $callData = json_decode($this->input->get("q"), TRUE);
        if(isset($callData["dialid"])) {
            $view = $this->load->view("templates/popup/loan/" . $callData["dialtype"], $data, TRUE);
        } else {
            $view = $this->load->view("templates/popup/loan/default", $data, TRUE);
        }
        return $view;
    }
}