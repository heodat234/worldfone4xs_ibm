<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set("log_errors", 1);
ini_set("error_log", APPPATH . "logs/acslog.txt");

class BroadcastingDebt extends CI_Controller {

    private static $_collection = "LO_Diallist_detail";	

    private static $_products = array(
       "101"    =>  "xemay",
       "102"    =>  "xemay",
       "103"    =>  "xemay",
       "201"    =>  "tienmat",
       "401"    =>  "xeoto",
       "501"    =>  "dienmay",
       "601"    =>  "noithat",
       "701"    =>  "xephankhoilon",
       "801"    =>  "vatlieuxaydung",
       "802"    =>  "vatlieuxaydung",
       "901"    =>  "thucanchannuoi"
    );
	
	private static $_type = array(
		"1"	=>	array("101","102","103","401","501","601","701","801","802","901"),
		"2"	=>	array("201")
	);

	private static $_collection_ivrs = "IVR_Voice";

    function __construct() {
		parent::__construct();		
        $this->load->library("mongo_db");
	}

    public function index(){
        if(0===strcasecmp("GET", $this->input->server("REQUEST_METHOD"))){
            $callernumber = (string)$this->input->get("callernumber");
            $calluuid = (string)$this->input->get("calluuid");
            $customerphone = (string)$this->input->get("customerphone");
			$dialid = (string)$this->input->get("dialid");
			$direction = (string)$this->input->get("direction");
        } else if(0===strcasecmp("POST", $this->input->server("REQUEST_METHOD"))){
            $request = json_decode(file_get_contents("php://input"), false);
            if(!empty($request)){
				$callernumber = (string)$request->callernumber;
				$calluuid = (string)$request->calluuid;
				$customerphone = (string)$request->customerphone;
				$dialid = (string)$request->dialid;
				$direction = (string)$request->direction;
			}
        }

        try {
			//Xử lý thông tin và lưu vào db
			if(!empty($dialid)){
				$data_insert = array(
					"type"			=> "outbound",
					"callernumber" 	=> $callernumber,
					"calluuid"		=> $calluuid,
					"customerphone" => $customerphone,	
					"dialid" 		=> $dialid,
					"recording"		=> null
				);
			} else {
				$data_insert = array(
					"type"			=> "inbound",
					"callernumber" 	=> $callernumber,
					"calluuid"		=> $calluuid,
					"customerphone" => $customerphone,
					"recording"		=> null
				);
			}
			$this->mongo_db->insert(self::$_collection_ivrs, $data_insert);
			// Kết thúc tính toán xử lý thông tin
			//Tính toán và trả lại kết quả cho callback

			if(!empty($dialid)){
				$decode = base64_decode($dialid);
				$dial = json_decode($decode, false);
				if ($dial === null || json_last_error() !== JSON_ERROR_NONE) {
					throw new Exception("Mising dialid!");
				}			
				$dialid = $dial->dialid;
				//print_r($dialid);die;			
				$result = $this->mongo_db->select(["due_date", "overdue_amount_this_month", "PRODGRP_ID"], ["_id"])->where_id($dialid)->getOne(self::$_collection);
			} else {
				$result = $this->mongo_db->select(["due_date", "overdue_amount_this_month", "PRODGRP_ID"], ["_id"])->where(["phone" => $customerphone, "created_at" => ['$gte' => strtotime('00:00:00'), '$lte' => strtotime('23:59:59')]])->getOne(self::$_collection);
			}	
			//print_r($result);die;
			if (empty($result["due_date"])) {
				throw new Exception("Mising Result due_date!");
			}
			if(is_numeric($result["due_date"])) $result["due_date"] = date('dmy', $result["due_date"]);
			if(!isset($result["overdue_amount_this_month"])||!is_numeric($result["overdue_amount_this_month"])) $result["overdue_amount_this_month"] = 0;
			//Get Product
			$product = self::$_products[$result["PRODGRP_ID"]];
			if ($product === null || $product === "" ) {
				throw new Exception("Mising Result product!");
			}
			//Get due_date
			$curent_date = date_create_from_format('dmy', date('dmy'));
			$due_date = date_create_from_format('dmy', $result["due_date"]);
			$interval = date_diff($curent_date, $due_date);
			$days_past_due = ( $interval->format('%y') * 365 ) + ( $interval->format('%m') * 30) + $interval->format('%d');
			//Get overdue_amount
			$overdue_amount = (string)(int)$result["overdue_amount_this_month"];
			$key = 0;
			foreach(self::$_type as $_ => $__){
				if(in_array($result["PRODGRP_ID"], $__)){
					$key = $_;
					break;
				}
			}
			header('Content-type: application/json');
			echo json_encode(array(
				"type"				=>	"script_".$key,
				"product" 			=>  "$product",
				"days_past_due" 	=>  "$days_past_due",
				"overdue_amount" 	=>  "$overdue_amount"
			));
        } catch(Exception $ex){
            error_log($ex);
            echo $ex->getMessage();
        }
    }

}