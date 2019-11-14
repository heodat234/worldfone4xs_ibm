<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Popup extends WFF_Controller {
	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$this->sub = set_sub_collection("");
	}

	function get_customer_by_phone()
	{
		$phone = $this->input->get("phone");
		try {
			if(!$phone) throw new Exception("Error Processing Request", 1);
			
			$customers = $this->mongo_db->where(array('$or' => array(array("phone" => $phone), array("other_phones" => $phone))))->get("{$this->sub}Customer");
			echo json_encode(array("status" => 1, "data" => $customers, "total" => count($customers)));
		} catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
	}

	function TS_get_customer_by_phone()
	{
		$phone = $this->input->get("phone");
		try {
			if(!$phone) throw new Exception("Error Processing Request", 1);
			
			$customers = $this->mongo_db->where(array("phone" => $phone))->get("{$this->sub}Telesalelist");
			echo json_encode(array("status" => 1, "data" => $customers, "total" => count($customers)));
		} catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
	}

	function complete($calluuid = "")
	{
		$id = $this->input->get("id");
		$this->load->model("call_model");
		$call = $this->call_model->get_call_by_id($calluuid);
		// Inc show popup
		$this->call_model->inc_show_popup($calluuid);
		//
		echo json_encode(array("status" => 1, "doc" => $call));
	}

	function get_customer_by_cif_or_phone()
	{
		$cif = $this->input->get("cif");
		$phone = $this->input->get("phone");
		$where = array();
		if($cif) $where["cif"] = $cif;
		if($phone) $where["phone"] = $phone;
		$customer = $where ? $this->mongo_db->where_or($where)->getOne("{$this->sub}Customer") : null;
		echo json_encode($customer ? $customer : null);
	}
}