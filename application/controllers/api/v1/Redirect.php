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
}