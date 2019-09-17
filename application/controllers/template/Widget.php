<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Widget extends CI_Controller {
	
	function headerbar()
	{
		// Load Parser
        $this->load->library('parser');
        $this->load->model("language_model");
		// Left widget
        $data["LEFT_HEADER_WIDGETS"] = $this->language_model->translate($this->_widgets("LEFT_HEADER_WIDGET"), "HEADERBAR");
        // Right widget
        $data["RIGHT_HEADER_WIDGETS"] = $this->language_model->translate($this->_widgets("RIGHT_HEADER_WIDGET"), "HEADERBAR");

        $this->parser->parse('templates/headerbar/left', $data);
        $this->parser->parse('templates/headerbar/right', $data);
	}

    function sidebar()
    {
        $this->load->library('parser');
        $this->load->model("language_model");
        $data["SIDEBAR_WIDGETS"] = $this->language_model->translate($this->_widgets("SIDEBAR_WIDGET"), "SIDEBAR", "", "", "@", FALSE);
        $this->parser->parse('templates/sidebar/widget', $data);
    }

	private function _widgets($type)
    {
        $this->load->library("session");
        $sub            = set_sub_collection();
        $time_cache     = $this->config->item("wff_time_cache");
        // Cache file
        $this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'));
        if (!$data = $this->cache->get($sub . $type . "_widgets")) {
            $this->load->library("mongo_private");
            $data = $this->mongo_private
            ->where(array("type" => $type, "active" => TRUE))
            ->order_by(array("index" => 1))->get($sub . "Widget");
            $this->cache->save($sub . $type . "_widgets", $data, $time_cache);
        }

        $parser_data = array();
        foreach ($data as $doc) {
            if(isset($doc["name"]))
            {
                $file = $doc["name"];
                $parser_data[] = array($type => $this->parser->parse("widgets/{$file}", array(), TRUE));
            }
        }
        
        return $parser_data;
    }
}