<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Popup extends CI_Controller {
	function index($id = "")
    {
        $this->load->model("language_model");
        $data["callData"] = $callData = json_decode($this->input->get("q"), TRUE);
		$type = isset($callData["dialid"]) ? "diallist" : "default";
        switch ($type) {
        	case 'diallist':
                switch ($callData["dialtype"]) {
                    case 'dialmode_1':
                        $view = $this->load->view("templates/popup/diallist", $data, TRUE);
                        break;

                    case 'cif':
                        $view = $this->load->view("templates/popup/defaultcif", $data, TRUE);
                        break;

                    default:
                        $view = $this->load->view("templates/popup/default", $data, TRUE);
                        break;
                }
        		break;
        	
        	default:
        		$view = $this->load->view("templates/popup/default", $data, TRUE);
        		break;
        }
        echo $this->language_model->translate($view, "CONTENT");
    }
}