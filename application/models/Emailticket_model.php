<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emailticket_model extends CI_Model {

	private $sub = "2_";
	private $collection = "Ticket";
	private $reply_collection = "Ticket_reply";
	private $config_collection = "Config";
	private $log_collection = "Email_inbound_logs";
	private $customer_collection = "Customer";
	private $notification_collection = "Notification";
	private $blacklist_collection = "Email_blacklist";

	function __construct()
	{
		parent::__construct();
		$this->load->library("mongo_db");
		$this->collection 				= $this->sub . $this->collection;
		$this->config_collection 		= $this->sub . $this->config_collection;
		$this->log_collection 			= $this->sub . $this->log_collection;
		$this->customer_collection 		= $this->sub . $this->customer_collection;
		$this->reply_collection 		= $this->sub . $this->reply_collection;
		$this->notification_collection 	= $this->sub . $this->notification_collection;
	}

	function runCheckEmail()
	{
		$emails = $this->getNewEmails();
		foreach ($emails as $mail) {
			$email_address = $mail["from"]["email"];
			// Check blacklist
			if(!$this->mongo_db->where(array("email" => $email_address))->getOne($this->blacklist_collection)) 
			{
				preg_match_all("/Re: \[(.*?)\]/", $mail["subject"], $matches); 
				$ticket_id = $matches[1] ? $matches[1][0] : "";
				if($ticket_id)
					$this->createTicketReply($mail, $ticket_id);
				else $this->createTicket($mail);
			}
		}
		return true;
	}

	function sendEmailReply($data)
	{
		if(empty($data["receiver_key"]) || empty($data["ticket_id"]) || empty($data["title"]) || empty($data["content"])) {
			throw new Exception("Lack of input");
		}
		$mail = array(
			"email"				=> $data["receiver_key"],
			"subject"			=> "[".$data["ticket_id"]."] " .$data["title"],
			"content"			=> $data["content"],
			"cid_attachments" 	=> isset($data["cid_attachments"]) ? $data["cid_attachments"] : [],
			"attachments" 		=> isset($data["attachments"]) ? $data["attachments"] : [],
		);
		
		$this->load->model("email_model");
		return $this->email_model->send($mail);
	}

	function createTicket($mail)
	{
		$content = isset($mail["body"]) ? (!empty($mail["body"]["html"]) ? $mail["body"]["html"] : $mail["body"]["plain"]) : "";
		$data = array(
			"ticket_id"	=> $this->getNewTicketId(),
			"title"		=> isset($mail["subject"]) ? $mail["subject"] : "",
			"content"	=> $content,
			"status"	=> "Open",
			"source"	=> "Email",
			"reply"		=> 0,
			"mail_uid"	=> $mail["uid"],
			"createdAt"	=> time(),
			"createdBy"	=> "System"
		);

		if(isset($mail["from"])) {
			$email = $mail["from"]["email"];
			$customers = $this->mongo_db->where(array("email" => $email))->get($this->customer_collection);
			if(count($customers) == 1) {
				$customer = $customers[0];
				$data["sender_id"] 		= $customer["id"];
				$data["sender_name"] 	= $customer["name"];
				$data["sender_key"] 	= $email;
			} else {
				$data["sender_name"]	= $email;
				$data["sender_key"] 	= $email;
			}
		}

		$result = $this->mongo_db->insert($this->collection, $data);
		if($result) {
			$this->insertLog($mail);
			$this->load->model("user_model");
			$this->user_model->set_sub($this->sub);
			$assigns = $this->user_model->extensions(1, 1);
			$this->createNotification($data["ticket_id"], "You have a new email ticket", $result["id"], $assigns);
		}
	}

	function createTicketReply($mail, $ticket_id)
	{
		$content = isset($mail["body"]) ? (!empty($mail["body"]["html"]) ? $mail["body"]["html"] : $mail["body"]["plain"]) : "";
		$to_emails = array();
		if(!empty($mail["to"])) {
			foreach ($mail["to"] as $doc) {
				$to_emails[] = $doc["email"];
			}
		}
		$data = array(
			"ticket_id"		=> $ticket_id,
			"title"			=> isset($mail["subject"]) ? $mail["subject"] : "",
			"content"		=> $content,
			"receiver_key"	=> implode(",", $to_emails),
			"sender_type"	=> "customer",
			"mail_uid"		=> $mail["uid"],
			"createdAt"		=> time(),
			"createdBy"		=> "System"
		);

		if(isset($mail["from"])) {
			$email = $mail["from"]["email"];
			$customers = $this->mongo_db->where(array("email" => $email))->get($this->customer_collection);
			if(count($customers) == 1) {
				$customer = $customers[0];
				$data["sender_id"] 		= $customer["id"];
				$data["sender_name"] 	= $customer["name"];
				$data["sender_key"] 	= $email;
			} else {
				$data["sender_name"]	= $email;
				$data["sender_key"] 	= $email;
			}
		}

		$result = $this->mongo_db->insert($this->reply_collection, $data);
		if($result) {
			$this->insertLog($mail);
			$ticket = $this->mongo_db->where(array("ticket_id" => $ticket_id))->getOne($this->collection);
			$assign = !empty($ticket["assign"]) ? $ticket["assign"] : "";
			$doc_id	= !empty($ticket["id"]) ? $ticket["id"] : "";
			$this->createNotification($ticket_id, "You have a new reply for email ticket", $doc_id, $assign, false);
		}
	}

	private function createNotification($title, $content, $doc_id = "", $assign = "", $to_manager = true)
	{
		$this->load->library("crud");
		$data = array(
			"title"		=> $title,
			"content"	=> $content,
			"to"		=> is_array($assign) ? $assign : explode(",", $assign),
			"active" 	=> true,
			"icon"		=> "gi gi-envelope",
			"color"		=> $doc_id ? "text-warning" : "text-danger",
			"createdBy"	=> "System",
			"link"		=> $to_manager ? ("manage/ticket/#/detail/" . $doc_id) : ("manage/ticket/solve/#/detail/" . $doc_id)
		);
		$this->crud->create($this->notification_collection, $data);
	}

	function getNewTicketId()
	{
		// Create ticket id
		$index_collecion = "Index";
		$from = "EML";
		$this->mongo_db->where(array("collection" => $this->collection, 'type' => $from))->update($index_collecion, array('$inc' => array("index" => 1)), array("upsert" => true));
		$indexDoc = $this->mongo_db->where(array("collection" => $this->collection, "type" => $from))->getOne($index_collecion);
		$ticket_id = "#TK_" . $from . "_" . $indexDoc["index"];
		return $ticket_id;
	}

    function getNewEmails($date = "")
	{
		if(!$date) 
			$date = date(DATE_RFC2822);

		$doc = $this->mongo_db->where(array("active" => true))->getOne($this->config_collection);
		$config = array();
		if(!empty($doc["email_address"]) && !empty($doc["email_password"])) {
			$config = array(
				"username" 	=> $doc["email_address"],
				"password"	=> $doc["email_password"]
			);
		}
		$this->load->library("imap", $config);
		$uids = $this->imap->search('SINCE "' . $date . '"');	
		$emails = array();
		foreach ($uids as $uid) {
			if(!$this->mongo_db->where(array("uid" => $uid))->getOne($this->log_collection)) 
			{
				$emails[] = $this->imap->get_message($uid);
			}
		}
		return $emails;
	}

	function insertLog($data)
	{
		$data["createdAt"] 	= time();
		$result 			= $this->mongo_db->insert($this->log_collection, $data);
		return $result;
	}
}