<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * PHP Codeigniter Template
 * Default folder template is constant TEMPLATE ROOT
 * OUTPUT MODE "normal" to set load view as normal
 * OUTPUT MODE "template" to set load view as template
 * Default is normal if set template change to mode Template
 * USE $this->output->set_template(template_file)
 */
class WFF_Output extends CI_Output {
	const OUTPUT_MODE_NORMAL = "normal";
	const OUTPUT_MODE_TEMPLATE = "template";
	const TEMPLATE_ROOT = "themes/";

	private $_template = null;
	private $_mode = self::OUTPUT_MODE_NORMAL;
	public $data = array();

	private $translate = TRUE;

	/**
	 * Set the  template that should be contain the output <br /><em><b>Note:</b> This method set the output mode to MY_Output::OUTPUT_MODE_TEMPLATE</em>
	 *
	 * @uses MY_Output::set_mode()
	 * @param string $template_view
	 * @return void
	 */
	function set_template($template_view){
		$this->set_mode(self::OUTPUT_MODE_TEMPLATE);
		$template_view = str_replace(".php", "", $template_view);
		$this->_template = self::TEMPLATE_ROOT . $template_view;
	}

	/**
	 * If don't want to use template set before
	 * Use $this->output->unset_template();
	 */
	function unset_template()
	{
		$this->_template = null;
		$this->set_mode(self::OUTPUT_MODE_NORMAL);
	}

	/**
	 * Sets the way that the final output should be handled.<p>Accepts two possible values 	MY_Output::OUTPUT_MODE_NORMAL for direct output
	 * or MY_Output::OUTPUT_MODE_TEMPLATE for displaying the output contained in the specified template.</p>
	 *
	 * @throws Exception when the given mode hasn't defined.
	 * @param integer $mode one of the constants MY_Output::OUTPUT_MODE_NORMAL or MY_Output::OUTPUT_MODE_TEMPLATE
	 * @return void
	 */
	function set_mode($mode){

		switch($mode){
			case self::OUTPUT_MODE_NORMAL:
			case self::OUTPUT_MODE_TEMPLATE:
				$this->_mode = $mode;
				break;
			default:
				throw new Exception("Unknown output mode.");
		}

		return;
	}

	/**
	 * Overide _display function CI_Output
	 * @see system/libraries/CI_Output#_display($output)
	 */
	function _display($output=''){

		if($output=='')
			$output = $this->get_output();

		switch($this->_mode){
			case self::OUTPUT_MODE_TEMPLATE:
				$output = $this->get_template_output($output);
				break;
			case self::OUTPUT_MODE_NORMAL:
			default:
				$output = $output;
				break;
		}

		parent::_display($output);
	}

	/**
	 * Render main page
	 * Part of template change every page
	 */
	private function get_template_output($output){

		if(function_exists("get_instance") && class_exists("CI_Controller")){

			$ci = get_instance();

			$this->data["output"] = $output;

			$data = $this->data;

			$output = $ci->load->view($this->_template, $data, true);

			// Multi language
			if($this->translate) {
				
				$ci->load->model("language_model");

        		$output = $ci->language_model->translate($output, "CONTENT");
        	}
		}

		return $output;
	}

	/**
	 * Test output function
	 * Print string to modal to test
	 */

	public function test($value = "", $name = "") {

		if(function_exists("get_instance") && class_exists("CI_Controller")) {

			$ci = get_instance();

			$ci->load->library("session");

			if($ci->session->userdata("test_mode")) {

				$data["output_test"] = array("name" => $name, "value" => $value);

				$this->data["modal_output_test"] = $ci->load->view(self::TEMPLATE_ROOT . "modal_output_test", $data, true);
			}

			return TRUE;

		} else return FALSE;
	}
}