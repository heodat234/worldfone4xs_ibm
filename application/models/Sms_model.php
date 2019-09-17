<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_model extends CI_Model {

	private $collection = "Sms_pending";
	private $config_collection = "Config";
	private $log_collection = "Sms_log";

	function __construct()
	{
		parent::__construct();
		$this->load->library("mongo_db");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
		$this->config_collection = $this->sub . $this->config_collection;
		$this->log_collection = $this->sub . $this->log_collection;
	}

    function send($id)
	{
		$sms = $this->mongo_db->where("_id", new MongoDB\BSON\ObjectId($id))->getOne($this->collection);
		if(empty($sms) || empty($sms["phone"])) throw new Exception("No phone");

		$smsConfig 	= $this->mongo_db->where(array("active" => true))->getOne($this->config_collection);

		$brand_name = $smsConfig['sms_brandname'];
		$username 	= $smsConfig['sms_username']; 
		$password 	= $smsConfig['sms_password'];
		$sms_url  	= $smsConfig['sms_api'];

		$sub_sms = !empty($smsConfig["sms_sub"]) ? $smsConfig["sms_sub"] : "";
		$sms_send_content = $sub_sms . $sms["content"]; 
		
		$curl = curl_init();
		$payload = array(
			"from"		=> $brand_name,
			"to"		=> $sms["phone"],
			"text"		=> $sms_send_content
		);	
		curl_setopt_array($curl, array(
			CURLOPT_URL => $sms_url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($payload),
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic " .base64_encode($username . ":" . $password ),
				"cache-control: no-cache",
				"content-type: application/json"
			)
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if($err)  {
			throw new Exception("Error send API: {$err}");
		}
		$responseArr = json_decode($response, true);

		$sms['sendedBy']		=  $this->session->userdata('extension');
		$sms['sendedAt'] 		=  time();
		$sms['agentname']		=  $this->session->userdata('agentname');
		$sms['contentSended'] 	=  $sms_send_content;
		if($responseArr["status"]) {
			$sms['success']			=  TRUE;	
			$this->mongo_db->insert($this->log_collection, $sms);
			$this->mongo_db->where("_id", new MongoDB\BSON\ObjectId($id))->delete($this->collection);
			echo json_encode(array("status" => 1, "message" => "Send SMS to {$sms["phone"]} successfully"));
		} else {
			$sms['success']			=  FALSE;
			$sms['error']			=  $response;
			$this->mongo_db->insert($this->log_collection, $sms);
			throw new Exception("Send SMS error: $response");
		}
	}
}