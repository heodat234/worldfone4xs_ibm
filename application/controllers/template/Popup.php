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
        $data["callData"] = $callData = json_decode($this->input->get("q"), TRUE);
		$type = isset($callData["dialid"]) ? "diallist" : "default";
        $user_type = $this->session->userdata("type");
        switch ($type) {
        	case 'diallist':
                switch ($callData["dialtype"]) {
                    case 'dialmode_1':
                        switch ($user_type) {
                            case '2':
                                $view = $this->load->view("templates/popup/telesale/diallist", $data, TRUE);
                                break;
                            
                            default:
                                $view = $this->load->view("templates/popup/diallist", $data, TRUE);
                                break;
                        }
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