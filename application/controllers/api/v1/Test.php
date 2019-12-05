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
		$this->session->set_userdata("agentname", "tri_dung_huynh");
		// $data = $this->mongo_db->command(["listDatabases" => 1, "nameOnly" => TRUE]);
		// pre($data);
	}

	function import()
	{
		shell_exec("/usr/bin/php ".FCPATH."cronjob/LOAN/createPaymentHistory.php");
		echo "DONE";
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