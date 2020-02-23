<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pbx_model extends CI_Model {
	// Where config
	private $where = array();
	private $config_collection = "ConfigType";
	private $external_path = "externalcrm/";

	function __construct() {
        parent::__construct();
        $this->load->library("mongo_private");
		$this->load->library("session");
		$type = $this->session->userdata("type");
		if($type) $this->where = array("type" => $type);
    }

    function make_call_2($extension, $phone, $dialid = "", $type = "") {
    	if(!$extension || !$phone) throw new Exception("Lack of input");
    	$data = array(
			"callernum" => $extension,
			"destnum"	=> $phone
		);
		if($dialid) {
			$data["dialid"] = base64_encode(json_encode(array("dialid" => $dialid, "dialtype" => $type)));
		} 
		return $this->send("makecall2.php", $data);
    }

    function make_call_3($dest, $phone, $dialid = "", $type = "", $callback_type = "queue") {
    	// $callback_type is dest || queue
    	if(!$dest || !$phone) throw new Exception("Lack of input");
    	$data = array(
			"callback" 		=> $dest,
			"callto"		=> $phone,
			"callback_type" => $callback_type
		);
		$data["dialid"] = base64_encode(json_encode(
			array("dialid" => $dialid, "dialtype" => $type)
		));
		return $this->send("makecall3.php", $data);
    }

	function pause_queue_member($queuename = "", $extension, $all = FALSE) {
		if(!$extension) throw new Exception("Lack of input"); 
		$data = array(
			"queuename" => $queuename,
			"extension"	=> $extension
		);
		if($all) $data["all"] = 1;
		return $this->send("pauseQueueMember2.php", $data);
	}

	function unpause_queue_member($queuename = "", $extension, $all = FALSE) {
		if(!$extension) throw new Exception("Lack of input"); 
		$data = array(
			"queuename" => $queuename,
			"extension"	=> $extension
		);
		if($all) $data["all"] = 1;
		return $this->send("unpauseQueueMember2.php", $data);
	}

	function add_queue_member($queuename, $extension) {
		if(!$queuename || !$extension) throw new Exception("Lack of input");
		$data = array(
			"queuename"	=> $queuename,
			"extension"	=> $extension
		);
		return $this->send("addQueueMember2.php", $data);
	}

	function remove_queue_member($queuename, $extension) {
		if(!$queuename || !$extension) throw new Exception("Lack of input");
		$data = array(
			"queuename"	=> $queuename,
			"extension"	=> $extension
		);
		return $this->send("removeQueueMember2.php", $data);
	}

	function list_agent_state($extension = "") {
		$data = $extension ? array("extension" => $extension) : [];
		return $this->send("listAgentStates2.php", $data);
	}

	function list_queues($queuename = "") {
		$data = $queuename ? array("queuename" => $queuename) : [];
		return $this->send("listQueues2.php", $data);
	}

	function list_agent($extension = 0, $issupervisor = 0, $isadmin = 0) { 
		$data = [];
		if($extension) $data["extension"] = $extension;
		if($issupervisor) $data["issupervisor"] = $issupervisor;
		if($isadmin) $data["isadmin"] = $isadmin;
		return $this->send("listAgent2.php", $data);
    }

    function hangup($calluuid = "") {
    	if(!$calluuid) throw new Exception("Lack of input");
    	$data = array("calluuid" => $calluuid);
		return $this->send("hangupcall2.php", $data);
    }

    function getAgent($userextension=0, $issupervisor=0, $isadmin=0) {        
		$data = array(
			"version"		=> 4,
			"extension" 	=> $userextension,
			"issupervisor"	=> $issupervisor,
			"isadmin"		=> $isadmin
		);
		return $this->send("listAgent2.php", $data);
    }

    function transfer($calluuid, $extension, $type="attended") {
    	$data = array(
    		"calluuid" 			=> $calluuid,
    		"targetextension" 	=> $extension,
    		"type"				=> $type
    	);
    	$result = $this->send("transfercall2.php", $data);
    	/*if(((int) $result) ==  200) {
    		$this->mongo_db->where(array('calluuid'=>$calluuid))
    		->set(array('transfer_type'=> $type,'transfernumber'=>$extension))->update(set_sub_collection('worldfonepbxmanager'));
    	}*/
    	return $result;
    }

    function blacklist($action = "get", $phone = "", $status = 1) {
    	$data = array(
    		"action"	=> $action,
    		"number"	=> $phone,
    		"status"	=> (int) $status,
    		"direction"	=> "in",
    		"version" 	=> 3
    	);
    	$result = $this->send("BlackList2.php", $data);
    	return $result;
    }

    function spy($spying_extension, $spied_extension, $mode = "spy")
    {
    	$data = array(
    		"spying"	=> $spying_extension,
    		"spied"		=> $spied_extension,
    		"mode"		=> $mode
    	);
    	$result = $this->send("chanspycall2.php", $data);
    	return $result;
    }

	function send($file, $data = array())
    {
    	try {
	    	$this->load->library("mongo_private");
	    	$config = $this->mongo_private->where($this->where)->getOne($this->config_collection);          
			$secret = $config['secret_key'];
			$queryArr = array_merge($data, array("secret" => $secret, "secrect" => $secret));
			$query = http_build_query($queryArr); 
			$url = $config["pbx_url"] . $this->external_path . $file . "?" . $query;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			if($err) throw new Exception("Curl error: " . PHP_EOL . print_r($err, true));
			if(!$response) throw new Exception("Response empty");
			$responseArr = json_decode($response, true);
			return $responseArr;
		} catch(Exception $e) {
			// Write log error api pbx
			$error = array(
				"url"		=> $url,
				"file"		=> $file,
				"data"		=> $data,
				"error"		=> $e->getMessage(),
				"response"	=> isset($response) ? $response : "",
				"time"		=> (new DateTime())->format('Y-m-d H:i:s'),
				"createdAt" => time()
			);
			$this->mongo_private->insert("PbxError", $error);
		}
    }

    /*
     * Request need auth
     */

    function change_password($old_password, $new_password)
    {
    	if(!$old_password || !$new_password) throw new Exception("Lack of input");
    	return $this->send_with_auth("agentchangepass2.php", $old_password, array("newpass" => $new_password));
    }

    function send_with_auth($file, $password, $data = array())
    {
    	try {
	    	$this->load->library("mongo_private");
	    	$username = $this->session->userdata("user");
	    	$config = $this->mongo_private->where($this->where)->getOne($this->config_collection);          
			$secret = $config['secret_key'];
			$queryArr = array_merge($data, array("secret" => $secret, "secrect" => $secret));
			$query = http_build_query($queryArr); 
			$url = $config["pbx_url"] . $this->external_path . $file . "?" . $query;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ".base64_encode("$username:$password"),
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			if($err) throw new Exception("Curl error");
			if(!$response) throw new Exception("Response empty");
			$responseArr = json_decode($response, true);
			return $responseArr;
		} catch(Exception $e) {
			// Write log error api pbx
			$error = array(
				"url"		=> $url,
				"file"		=> $file,
				"data"		=> $data,
				"error"		=> $e->getMessage(),
				"response"	=> isset($response) ? $response : "",
				"time"		=> (new DateTime())->format('Y-m-d H:i:s')
			);
			$this->mongo_private->insert("PbxError", $error);
		}
    }
}