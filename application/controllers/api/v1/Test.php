<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Test extends WFF_Controller {


	function __construct()
	{
		parent::__construct();
		//var_dump($this->config->item("abc"));
	}

	function index($id = "")
	{
		/*$this->load->model("ftp_model");
		$connectId = $this->ftp_model->connectToFTP();
		pre($this->ftp_model->listFileInFTP($connectId));*/
		//shell_exec("/usr/bin/php ".FCPATH."cronjob/LOAN/importAccounts.php");
		//shell_exec("/usr/bin/php ".FCPATH."cronjob/LOAN/importSIBS.php");
		/*$result = exec("kill -9 4910");
		pre($result);*/
		/*$this->load->library("mongo_db");
		$this->mongo_db->switch_db("_worldfone4xs");
		$model = $this->mongo_db->where(array("collection" => "LO_LNJC05"))->like("sub_type", "import")->order_by(array("index" => 1))->get("Model");
		pre($model);*/
		/*$this->load->model("pbx_model");
		$result = $this->pbx_model->list_agent_state("911");
		pre($result);
		$result = $this->pbx_model->make_call_3("9999","0968495645","5dddfc021ef2b4638d24e11f","auto", "queue");
		var_dump($result);*/
		// $data = $this->mongo_db->command(["listDatabases" => 1, "nameOnly" => TRUE]);
		// pre($data);

		$CI =& get_instance();
		if(isset($CI->data, $CI->data["permission"]))
		pre($CI->data);
	}

	function import()
	{
		shell_exec("/usr/bin/php ".FCPATH."cronjob/LOAN/createPaymentHistory.php");
		echo "DONE";
	}

	function cdr()
    {
    	header('Content-type: application/json');
    	$this->collection = "worldfonepbxmanager";
        $this->sub = set_sub_collection("");
        $this->collection = $this->sub . $this->collection;
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $this->load->library("cruds");
            // PERMISSION
            $match = array();
            /*if(!in_array("viewall", $this->data["permission"]["actions"])) {
                $extension = $this->session->userdata("extension");
                $this->load->model("group_model");
                $members = $this->group_model->members_from_lead($extension);
                $match["userextension"] = ['$in' => $members];
            }*/
            $response = $this->cruds->read($this->collection, $request, [], $match);

            /*foreach ($response["data"] as &$doc) {
                if(isset($doc["dialid"]) && empty($doc["customer"])) {
                    $diallistDetail = $this->mongo_db->where_id($doc["dialid"])->getOne($this->sub . "Diallist_detail");
                    if($diallistDetail) {
                        if(isset($diallistDetail["cus_name"]) && empty($diallistDetail["name"])) 
                            $diallistDetail["name"] = $diallistDetail["cus_name"];
                        $this->mongo_db->where_id($doc["id"])->set(array("customer" => $diallistDetail))
                            ->update($this->collection);
                        $doc["customer"] = $diallistDetail;
                    }
                }
                if(!empty($doc["customernumber"]) && empty($doc["customer"])) {
                    $phone = $doc["customernumber"];
                    $customers = $this->mongo_db->where_or(array("phone" => $phone, "other_phones" => $phone))->get($this->sub . "Customer");
                    if($customers) {
                        if(count($customers) == 1) {
                            $this->mongo_db->where_id($doc["id"])->set(array("customer" => $customers[0]))
                            ->update($this->collection);
                            $doc["customer"] = $customers[0];
                        } else {
                            $doc["customer"] = $customers;
                        }
                    }
                }
            }*/
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

	function update()
	{
		$result = shell_exec("ps aux | grep php | head -20");
		$data = array();
		$lines = explode("\n", $result);
		$data = [];
		foreach ($lines as $key => $line) {
			if($line) {
				$data[] = array(
					"USER"		=> trim(substr($line, 0, 9)),
					"PID" 		=> trim(substr($line, 9, 5)),
					"CPU" 		=> trim(substr($line, 14, 5)),
					"MEM" 		=> trim(substr($line, 19, 5)),
					"VSZ" 		=> trim(substr($line, 24, 7)),
					"RSS" 		=> trim(substr($line, 31, 6)),
					"TTY"		=> trim(substr($line, 37, 2)),
					"STAT"		=> trim(substr($line, 39, 9)),
					"START"		=> trim(substr($line, 48, 9)),
					"TIME"		=> trim(substr($line, 57, 7)),
					"COMMAND"	=> (substr($line, 64))
				);
			}
		}
		pre($data);
		//pre($lines);
	}

	function abc()
	{
		exit("OK");
	}

	function LO_abc()
	{
		exit("OK LO");
	}
}