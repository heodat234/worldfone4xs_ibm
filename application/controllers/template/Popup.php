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
            case "TS":
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
        $this->load->library('mongo_db');
        $data["callData"]   = $callData = json_decode($this->input->get("q"), TRUE);
        $string             = 'templates/popup/loan/';

        if(isset($callData["dialid"])) {
            $diallist_detail    = $this->mongo_db->where_id($callData['dialid'])->getOne('LO_Diallist_detail');
            $diallist  = $data["diallist"] = $this->mongo_db->where_id($diallist_detail["diallist_id"])->getOne('LO_Diallist');
            if($callData["dialtype"] == 'manual'){
                if(isset($diallist['team'])){
                    $view   = (strtolower($diallist['team']) == 'wo') ? $this->load->view($string . "wo", $data, TRUE) : $this->load->view($string . "manual", $data, TRUE);
                }else{
                    $view   = $this->load->view($string . "manual", $data, TRUE);
                }
            }else{
                $view = $this->load->view($string . $callData["dialtype"], $data, TRUE);
            }
        } else {
            $view = $this->load->view($string . "default", $data, TRUE);
        }
        return $view;
    }
}