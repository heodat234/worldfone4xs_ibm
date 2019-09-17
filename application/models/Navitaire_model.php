<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Navitaire_model extends CI_Model {
	// Where config
	private $where = array();
	private $config_collection = "ConfigType";
	private $external_path = "navitaire/";

	function __construct() {
        parent::__construct();
        $this->load->library("mongo_private");
		$this->load->library("session");
		$type = $this->session->userdata("type");
		if($type) $this->where = array("type" => $type);
    }

    function getBooking($pnr) 
    {
    	$data = array(
    		"pnr"		=> $pnr,
    		"func"		=> "GetBooking"
    	);
    	return $this->sendGET("index.php", $data);
    }

    function findBooking($findBy) {
        $data = array(
            "func"	=> "FindBooking",
        );
        $data = array_merge($data, $findBy);
        return $this->sendGET("index.php", $data);
    }

	function sendGET($file, $data = array())
    {
    	try {
	    	$this->load->library("mongo_private");
			$secret = 'a357e8e5fbce92dd44269146416b0b4d';
			$queryArr = array_merge(array("secret" => $secret), $data);
			$query = http_build_query($queryArr); 
			$url = 'http://127.0.0.1:8081/navitaire/index.php?' . $query;
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 300,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			if($err) throw new Exception("Curl error: " . PHP_EOL . print_r($err, true));
			if(!$response) throw new Exception("Response empty");
			$responseArr = json_decode($response, true);
			return $responseArr;
		} catch(Exception $e) {
			// Write log error api pbx
			$error = array(
				"url"		=> $url,
				"file"		=> $file,
				"data"		=> $data,
				"error"		=> $e->getMessage(),
				"response"	=> isset($response) ? $response : "",
				"time"		=> (new DateTime())->format('Y-m-d H:i:s')
			);
			$this->mongo_private->insert("PbxError", $error);
		}
    }
}