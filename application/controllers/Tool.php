<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tool extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
        $this->_build_template();
    }

    public function search() {
        $this->load->view('tool/search_view', $_GET);
    }

    public function scheduler() {
		$this->output->data["js"][] = KENDOUI_PATH . "js/kendo.timezones.min.js";
        $this->load->view('tool/scheduler_view', $_GET);
    }

    public function library() {
        $this->output->data["css"][] = STEL_PATH . "css/users/roles/index.css";
        $this->load->view('tool/library_view');
    }

    public function chat() {
        $this->output->data["css"][] = KENDOUI_PATH . "styles/kendo.office365.min.css";
        $this->output->data["js"][] = PROUI_PATH . "js/pages/readyChat.js";
        $this->output->data["js"][] = STEL_PATH . "js/chat/socket.io.js";
        $this->output->data["js"][] = STEL_PATH . "js/chat/kendo.mychat.min.js";
        $this->output->data["js"][] = base_url() . "public/emojionearea/emojionearea.min.js";
        $this->output->data["css"][] = base_url() . "public/emojionearea/emojionearea.min.css";
        $this->load->view('tool/chat_view');
    }
}