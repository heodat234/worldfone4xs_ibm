<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setting extends WFF_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->_build_template();
    }

    public function agent_status_code()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/setting/agent_status_code.js";
        $this->load->view("setting/agent_status_code_view");
    }

    public function config_type()
    {
        $this->load->view("setting/config_type_view");
    }

    public function preference()
    {
        $this->load->view("setting/preference_view");
    }

    public function config()
    {
        $this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
        $this->load->view("setting/config_view");
    }

    public function jsondata()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/admin/jsondata.js";
        $this->load->view('setting/jsondata_view');
    }

    public function group()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view("setting/group_view");
    }

    public function manageCreateCallingLists()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view("setting/manage_create_calling_list_view");
    }

    public function campaign_divide_rule()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view("setting/campaign_divide_rule_view");
    }

    public function diallistDetailField()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view("setting/diallist_detail_field_view");
    }

    public function servicelevel()
    {
        $this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
        $this->load->view('setting/servicelevel_view');
    }

    public function chat_status_code()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/setting/agent_status_code.js";
        $this->load->view("setting/chat_status_code_view");
    }

    public function sms_template()
    {
        $this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
        $this->load->view("setting/sms_template_view");
    }

    public function email_template()
    {
        $this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
        $this->load->view("setting/email_template_view");
    }

    public function email_blacklist()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->load->view("setting/email_blacklist_view");
    }

    public function phone_blacklist()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->load->view("setting/phone_blacklist_view");
    }

    public function trigger()
    {
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/admin/trigger.js";
        $this->load->view("setting/trigger_view");
    }

    public function organization()
    {
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view("setting/organization_view");
    }

    public function report_off_sys_date()
    {
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view("setting/report_off_sys_date_view");
    }

    public function report_due_date()
    {
        $this->output->data["js"][] = STEL_PATH . "js/admin/admin.js";
        $this->load->view("setting/report_due_date_view");
    }

    public function product_code()
    {
        $this->output->data["js"][] = STEL_PATH . "js/setting/product_code.js";
        $this->load->view("setting/product_code_view");
    }

    public function target()
    {
        $this->output->data["js"][] = STEL_PATH . "js/setting/target.js";
        $this->load->view("setting/target_view");
    }

    public function ts_rate()
    {
        $this->output->data["js"][] = STEL_PATH . "js/setting/ts_rate.js";
        $this->load->view("setting/ts_rate_view");
    }
}
