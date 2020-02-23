<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_model extends CI_Model {

	private $common_email = TRUE;
	private $collection = "Email_pending";
	private $config_collection = "Config";
	private $log_collection = "Email_logs";
	private $email_config_collection = "Email_config";

	function __construct()
	{
		parent::__construct();
		$this->load->library("mongo_db");
		$this->load->library("mongo_private");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
		$this->config_collection = $this->sub . $this->config_collection;
		$this->log_collection = $this->sub . $this->log_collection;
		$this->email_config_collection = $this->sub . $this->email_config_collection;
	}

	function send($mail)
	{
		if(empty($mail["subject"]) || empty($mail["content"]) || empty($mail["email"])) {
			throw new Exception("Lack of input");
		}
		$extension 		= $this->session->userdata("extension");
		$result 		= $this->_send($mail, $extension);
		return $result;
	}

    function send_from_pending($id)
	{
		$mail = $this->mongo_db->where_id($id)->getOne($this->collection);
		if(empty($mail) || empty($mail["email"])) throw new Exception("No email customer");
		$extension = $mail["createdBy"];
		$result = $this->_send($mail, $extension);
		if($result) $this->mongo_db->where_id($id)->delete($this->collection);
		return $result;
	}

	private function _send($mail, $extension = "")
	{
		$user 	= $this->mongo_db->where(array("extension" => $extension))->getOne($this->email_config_collection);
		$emailConfig 	= $this->mongo_db->where(array("active" => true))->getOne($this->config_collection);
		if(empty($emailConfig["email_address"]) || empty($emailConfig["email_password"]))
			throw new Exception("Error Processing Request", 1);
		if($this->common_email) {
			$email_name = isset($emailConfig["email_name"]) ? $emailConfig["email_name"] : "";
			$from_email = $emailConfig["email_address"];
			$password_email = $emailConfig["email_password"];
			$email_signature = !empty($emailConfig["email_signature"]) ? $emailConfig["email_signature"] : "";
			$email_signature_cid_attachments = !empty($emailConfig["email_signature_cid_attachments"]) ? $emailConfig["email_signature_cid_attachments"] : "";
		} else {
			$email_name = isset($user["email_name"]) ? $emailConfig["email_name"] : "";
			$from_email = !empty($user["email_address"]) ? $user["email_address"] : $emailConfig["email_address"];
			$password_email = !empty($user["email_password"]) ? $user["email_password"] : $emailConfig["email_password"];
			$email_signature = !empty($user["email_signature"]) ? $user["email_signature"] : "";
		}
		$config = array(
		    'protocol' 	=> 'smtp',
		    'smtp_host' => $emailConfig["email_host"],
		    'smtp_port' => 465,
		    'smtp_user' => $from_email,
		    'smtp_pass' => $password_email,
		    'mailtype'  => 'html', 
		    'charset'   => 'utf-8'
		);
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");

		$this->email->from($from_email, $email_name);
		$this->email->reply_to($emailConfig["email_address"], $emailConfig["email_name"]);
		$this->email->to($mail["email"]);
		if(!empty($mail["cc"])) {
			$this->email->cc($mail["cc"]);
		}
		if(!empty($mail["bcc"])) {
			$this->email->bcc($mail["bcc"]);
		}
		if(!empty($mail["attachments"])) {
			foreach($mail["attachments"] as $attact) {
				$attact = (array) $attact;
				$this->email->attach($attact["filepath"]);
			}
		}
		
		$this->email->subject($mail["subject"]);

		$message =  $mail["content"] . ($email_signature ? "<br>" . $email_signature : "");
		$email_log = $mail;
		$email_log["message"] 				=  $message;
		
		if(!empty($email_signature_cid_attachments)) {
			$img_paths = array();
			$cid_paths = array();
			foreach($email_signature_cid_attachments as $attact) {
				$attact = (array) $attact;
				$this->email->attach($attact["filepath"]);
				$cid = $this->email->attachment_cid($attact["filepath"]);
				$img_paths[] = $attact["filepath"];
				$cid_paths[] = 'cid:' . $cid;
			}
			$message = str_replace($img_paths, $cid_paths, $message);
		}

		if(!empty($mail["cid_attachments"])) {
			$img_paths = array();
			$cid_paths = array();
			foreach($mail["cid_attachments"] as $attact) {
				$attact = (array) $attact;
				$this->email->attach($attact["filepath"]);
				$cid = $this->email->attachment_cid($attact["filepath"]);
				$img_paths[] = $attact["filepath"];
				$cid_paths[] = 'cid:' . $cid;
			}
			$message = str_replace($img_paths, $cid_paths, $message);
		}

		$this->email->message($message); 

		$email_log["from"]					=  $from_email;
		$email_log["to"]					=  $mail["email"];
		$email_log['sendedBy']				=  $this->session->userdata('extension');
		$email_log['sendedAt'] 				=  time();
		$email_log['sendedByAgentname']		=  $this->session->userdata('agentname');
		if($this->email->send()) {
			$email_log['success']			=  TRUE;	
			$this->mongo_db->insert($this->log_collection, $email_log);
			return TRUE;
		} else {
			$email_log['success']			=  FALSE;
			$email_log['error']				=  "";
			$this->mongo_db->insert($this->log_collection, $email_log);
			throw new Exception("Send Email error");
		}
	}
}