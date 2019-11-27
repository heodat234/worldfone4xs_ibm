<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Dailyreport extends WFF_Controller {
	function __construct()
    { 
    	parent::__construct();
    	$this->_build_template();
    	$this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
    }

	public function dailyPayment()
	{
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
		$this->load->view('report/loan/daily_payment_view');
	}

    public function dailyBalance()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/loan/daily_balance_view');
    }
    public function dailyProductOfEachUser()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/loan/daily_product_of_user_view');
    }
    public function dailyProductProductOfEachUser()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/loan/daily_product_product_of_user_view');
    }
    public function dailyProductOfEachUserGroup()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/loan/daily_product_of_user_group_view');
    }
    public function dailyAllUser()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/loan/daily_all_user_view');
    }
    public function dailyAssignment()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view('report/loan/daily_assignment_view');
    }
}