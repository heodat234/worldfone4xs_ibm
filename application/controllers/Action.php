<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Action extends CI_Controller {

    private $signInPage = "page/signin";

	function __construct()
    { 
    	parent::__construct();
    }

	public function login()
	{
		$redirect = urldecode($this->input->post("redirect"));
		$username = $this->input->post("username");
		$password = $this->input->post("password");
		$this->load->library("authentication");
		$result = $this->authentication->authenticate_pbx($username, $password);
		if($result) {
            $this->load->model("afterlogin_model");
            $this->afterlogin_model->run();
			if($redirect && strpos($redirect, base_url()) !== FALSE)
				redirect($redirect);
			else redirect(base_url());
		} else {
			redirect(base_url($this->signInPage));
		}
	}

	public function glogin()
	{
        try {
            if (!isset($_GET['code'])) throw new Exception("Unauthorization");
            $this->load->library('googleplus');

            $this->googleplus->getAuthenticate();

            $user_profile = $this->googleplus->getUserInfo();

            $this->load->library('mongo_private');

            $email = $user_profile['email'];

            $this->load->library("authentication");

            $result = $this->authentication->authenticate_pbx_email($email);

            if(!$result) throw new Exception("Email not access");
            // Update user
            $this->update_user();
            $this->update_group();
            // Update google info
            $extension = $this->session->userdata("extension");
            $this->mongo_private->where(array("extension" => $extension))
            ->update("User", array('$set' => array("google_info" => $user_profile)));
            //
            // Access token
            $acc = $this->googleplus->getAccessToken();
            $access = json_decode($acc, true);
            $this->session->set_userdata('google_access', $access);
            //
            redirect(base_url());
        } catch (Exception $e) {
            $this->load->library("session");
            $this->session->set_flashdata("error", $e->getMessage());
            redirect(base_url($this->signInPage));
        }
	}

    function mode($type = "")
    {
        if($type == "test") {
            $this->load->library("session");
            $this->session->set_userdata("test_mode", 1);
            $base_url = base_url();
            echo "<h1>Đã bật test mode</h1><a href='{$base_url}'>Trở về trang chủ</a>";
        }
    }

    function backup()
    {
        $this->load->library("session");
        $time = time();
        if($this->session->userdata("isadmin")) {
            foreach (["_worldfone4xs", "worldfone4xs"] as $db) {
                shell_exec("mongodump --out ". APPPATH."/logs/backupdb_$time" ." --db {$db}");
            }
            echo "DB backup done";
        } else echo "Unauthorized";
    }
}