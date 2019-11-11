<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include "./application/third_party/beanstalk_console/public/index.php";

Class Test extends WFF_Controller {


	function __construct()
	{
		parent::__construct();
	}

	function index($id = "")
	{
		/*$this->load->model("ftp_model");
		$connectId = $this->ftp_model->connectToFTP();
		pre($this->ftp_model->listFileInFTP($connectId));*/
		//shell_exec("/usr/bin/php ".FCPATH."cronjob/LOAN/importAccounts.php");
		shell_exec("/usr/bin/php ".FCPATH."cronjob/LOAN/importSIBS.php");
		/*$result = exec("kill -9 4910");
		pre($result);*/
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