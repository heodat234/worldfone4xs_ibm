<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Redirect extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
	}

	function fromPhoneToCustomerDetail($phone = "")
	{
		try {
			if(!$phone) throw new Exception("No phone number");
			$customer = $this->mongo_db->where("phone", $phone)->getOne(getCT("Customer"));
			if($customer) {
				redirect(base_url("manage/customer/#/detail/" . $customer["id"]));
			} else redirect(base_url("page/error/404"));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function fromFieldToCustomerDetail($field = "phone", $value = "")
	{
		try {
			// Only use for LOAN
			if(!$value) throw new Exception("No $field");
			$customer = $this->mongo_db->where($field, $value)->getOne(getCT("Customer"));
			if($customer) {
				redirect(base_url("manage/customer/#/detail/" . $customer["id"]));
			} else {
				$diallistDetail = $this->mongo_db->where($field, $value)->order_by(["createdAt"=>-1])->getOne(getCT("Diallist_detail"));
				// Insert customer
				if($diallistDetail) {
					$diallistDetail["name"] = isset($diallistDetail["cus_name"]) ? $diallistDetail["cus_name"] : "";
					$customer = $this->mongo_db->insert(getCT("Customer"), $diallistDetail);
					redirect(base_url("manage/customer/#/detail/" . $customer["id"]));
				}
				redirect(base_url("page/error/404"));
			}
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}