<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Ping extends CI_Controller {

	private $sub = "";
	private $missCallIconClass = "fa fa-times";
	private $events = array(
		"agent_status"					=> 1,
		"chat_status"					=> 1,
		"call" 							=> 1,
		"chat_notifications"			=> 1,
		"wait_in_queue"					=> 1,
		"header_notifications"			=> 1,
		"misscall_menu_notifications"	=> 1,
		"follow_up_menu_notifications"	=> 1
	);

	function __construct()
	{
		parent::__construct();
		// Load Session
        $this->load->library("session");
        $my_session_id = $this->session->userdata("my_session_id");
        if(!$my_session_id) exit();
        $this->sub = set_sub_collection("");
	}

	function sse()
	{
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
        $time = time();

        echo "id: {$time}\nretry: 1000\n";
        // Event every second
        $this->echoEvent("ping", json_encode(array("time" => $time)));

        
        if($this->events["call"]) {
        	$call = $this->getCall();
        	$this->echoEvent("call", json_encode($call));
        }
        // Event interval second
        if($this->events["agent_status"]) {
        	$agent_status = $this->last_agent_status();
        	$this->echoEvent("agent_status", json_encode($agent_status));
        }
        if($this->events["chat_status"]) {
        	$chat_status = $this->get_chat_status();
        	$this->echoEvent("chat_status", json_encode($chat_status));
     	}  

        if($time % 2 == 0 && $this->events["chat_notifications"]) {
        	// Chat
        	$chat_notifications = $this->get_chat_notifications();
        	$this->echoEvent("chat_notifications", 
        		json_encode(array("data" => $chat_notifications, "total" => count($chat_notifications))));
        }
    	
    	if($time % 2 == 0 && $this->events["wait_in_queue"]) {
    		// Wait in queue
	    	$wait_in_queue = $this->get_wait_in_queue();
	    	$this->echoEvent("wait_in_queue", 
	    		json_encode(array("data" => $wait_in_queue, "total" => count($wait_in_queue), "time" => $time)));
    	}

        if($time % 10 == 0 && $this->events["header_notifications"]) {
        	// 4x notification
        	$header_notifications = $this->notification();
        	$this->echoEvent("header_notifications", json_encode($header_notifications));
        }

        $menu_notifications = [];
    	if($time % 4 == 0 && $this->events["misscall_menu_notifications"]) {
        	$misscall = $this->misscall();
        	if($misscall["count"]) {
        		$menu_notifications[] = $misscall;
        	}
    	}
    	if($time % 4 == 0 && $this->events["follow_up_menu_notifications"]) {
        	$follow_up = $this->getFollowUp();
        	if($follow_up["count"]) {
        		$menu_notifications[] = $follow_up;
        	}
    	}
    	$this->echoEvent("menu_notifications", json_encode($menu_notifications));
    	
  		flush();
	}

	private function echoEvent($type, $response = "")
	{
		echo "event: $type\ndata: {$response}\n\n";
	}

	function json() 
	{
		header('Content-type: application/json');
		echo json_encode(array("status" => 1));
	}

	private function getFollowUp()
	{
		$this->load->library("mongo_db");
		$count = $this->mongo_db->count(set_sub_collection("Follow_up"));
		return array("class" => "gi gi-pushpin", "count" => $count);
	}

	private function getCall() {
		$this->load->model("call_model");
		$extension = $this->session->userdata("extension");
		$currentCall = $this->call_model->get_current_call($extension);
		if($currentCall) $currentCall["currentTime"] = time();
		return $currentCall;
	}

	private function misscall() {
		$this->load->library("mongo_db");
		$extension = $this->session->userdata("extension");
		$count = $this->mongo_db->where("assign", $extension)->count("{$this->sub}misscall");
		return array("class" => $this->missCallIconClass, "count" => $count);
	}

	function notification()
    {
        $extension = $this->session->userdata("extension");
        $this->load->library("mongo_db");
        $total = $this->mongo_db->where(array(
        	"active" 			=> true, 
        	"to" 				=> $extension, 
        	"read.extension" 	=> array('$ne' => $extension)
        ))->count("{$this->sub}Notification");
        return array("total" => $total);
    }

	private function last_agent_status() { 
		$this->load->model("agentstatus_model");
		try {	
			$agentStatus = $this->agentstatus_model->getOne([], ["_id", "my_session_id","endtime"]); 		

			if(!$agentStatus) throw new Exception("No data");
			if(empty($agentStatus["status"])) throw new Exception("No status reference");
			$shortcut 					=  $agentStatus["status"]["code"];
			$agentStatus[$shortcut] 	= TRUE;
			return $agentStatus;
		} catch (Exception $e) {
			echo $e->getMessage()."\n";
		}
        
    } 

    public function update_ping() {
    	try {	
			// Set agent status logs real time
	        $this->agentstatus_log();
			// Set agent sign logs real time
			$this->agentsign_log();
        	// Set tab, page log real time
        	//$this->write_log();
        	// Set chat status
        	if($this->events["chat_status"]) $this->chatstatus_log();
    	} catch (Exception $e) {
			echo $e->getMessage()."\n";
		}
    }

    private function agentstatus_log() {
    	$this->load->model("agentstatus_model");
    	if( !$this->agentstatus_model->getOne(["_id"]) )
            $this->agentstatus_model->start();
		else $this->agentstatus_model->update([]);
    }

    private function agentsign_log() {
    	// Set agent sign logs real time
		$this->load->model("agentsign_model");
        $this->agentsign_model->update();
    }

	private function write_log()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(!isset($data["tabs"])) throw new Exception("Tabs data not exists");
		$tabs = $data["tabs"];
		$this->load->model("browsertab_model");
		$this->load->model("browserpage_model");
		foreach ($tabs as $doc) {
			$id = $doc["id"];
			// Log tab
			$this->browsertab_model->run($id);
			// Log page
			$currentUri = isset($doc["currentUri"]) ? $doc["currentUri"] : "Undefined";
			$this->browserpage_model->run($id, $currentUri);
		}
	}

	private function get_chat_notifications()
	{
		$this->load->model('models_chat/chat_model', 'chat_model');
		return $this->chat_model->getNewNotify();
	}

	private function get_chat_status()
	{
		try {
			$this->load->model("chatstatus_model");	
			$chatStatus = $this->chatstatus_model->getOne([], ["_id", "my_session_id","endtime"]); 		

			if(!$chatStatus) throw new Exception("No data");
			if(!isset($chatStatus["statuscode"])) throw new Exception("No status reference");
			$shortcut 					=  $chatStatus["statuscode"];
			$chatStatus[$shortcut] 		= TRUE;
			return $chatStatus;
		} catch (Exception $e) {
			return array("status" => 0, "message" => $e->getMessage());
		}
	}

	private function chatstatus_log() {
    	$this->load->model("chatstatus_model");
    	$this->chatstatus_model->update([]);
    }

    private function get_wait_in_queue() {
    	$this->load->model("call_model");
    	return $this->call_model->get_call_in_queue();
    }
}