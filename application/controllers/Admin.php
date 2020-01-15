<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Admin extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    	$this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
    }

    public function user()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/user.js";
		$this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('admin/user_view');
	}

	public function role()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/table.js";
		$this->output->data["js"][] = STEL_PATH . "js/admin/role.js";
		$this->load->view('admin/role_view');
	}

	public function language()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/language.js";
		$this->load->view('admin/language_view');
	}

	public function notification()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/table.js";
		$this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/notification.js";
		$this->load->view('admin/notification_view');
	}

	public function navigator()
	{
		$this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/navigator.js";
		$this->load->view('admin/navigator_view');
	}

	public function module()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/module.js";
		$this->load->view('admin/module_view');
	}

	public function widget()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/widget.js";
		$this->load->view('admin/widget_view');
	}

	public function model()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/model.js";
		$this->load->view('admin/model_view');
	}

	public function config()
	{
		$this->load->view("admin/config_view");
	}

	public function changelog()
	{
		$this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/changelog.js";
		$this->load->view('admin/changelog_view');
	}

	public function type()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/type.js";
		$this->load->view('admin/type_view');
	}

	public function server()
	{
		$total_mem_arr 		= preg_split('/ +/', @exec('grep MemTotal /proc/meminfo'));
		$data["total_mem"] 	= $total_mem_arr[1];
		$data["numcores"] 	= trim(shell_exec("grep -P '^physical id' /proc/cpuinfo|wc -l"));

		$this->load->helper("server");
		$check_disks = array();
    	$check_disks[] = array("name" => "local" , "path" => getcwd());
    	$data["disks"] = get_disk_free_status($check_disks);

		$this->load->view('admin/server_view', $data);
	}

	public function ipphone()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/admin/ipphone.js";
		$this->load->view('admin/ipphone_view');
	}

	public function database()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["css"][] = STEL_PATH . "js/jsoneditor/jsoneditor.min.css";
		$this->output->data["js"][] = STEL_PATH . "js/jsoneditor/jsoneditor.js";
		$this->load->view('admin/database_view');
	}

	public function activity()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->load->view('admin/activity_view');
	}

	public function virtualcall()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->load->view('admin/virtualcall_view');
	}

	public function tail()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->load->view('admin/tail_view');
	}

	public function memcached()
	{
		$this->output->data["css"][] = STEL_PATH . "js/jsoneditor/jsoneditor.min.css";
		$this->output->data["js"][] = STEL_PATH . "js/jsoneditor/jsoneditor.js";
		$this->load->view('admin/memcached_view');
	}

	public function troubleshoot()
	{
		$this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
		$this->load->view('admin/troubleshoot_view');
	}

	public function websocket()
	{
		$this->load->view('admin/websocket_view');
	}

	public function import()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
		$this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('admin/import_view');
	}

	public function current_call()
	{
		$types = ["ALL"];
		$this->load->library("mongo_private");
		$data["types"] = array_merge($types, $this->mongo_private->distinct("ConfigType", "type"));
		$this->output->data["js"][] = KENDOUI_PATH . "js/kendo.timezones.min.js";
		$this->load->view('admin/current_call_view', $data);
	}
}