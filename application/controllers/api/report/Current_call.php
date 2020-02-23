<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Current_call extends WFF_Controller {

    /**
     * API restful [worldfonepbxmanager] collection.
     * READ from base_url + api/restful/cdr
     * DETAIL from base_url + api/restful/cdr/$id
     */

    private $collection = "Report_current_call";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
    }

    function every_hour()
    {
        try {
            $this->load->library("crud");
	        $request = json_decode($this->input->get("q"), TRUE);

	        $match = [];
	        $date = $this->input->get("date");
	        if($date) {
	        	$dateObject = new DateTime();
	        	$dateObject->setTimestamp(strtotime(preg_replace('/\([^)]*\)/', '', $date)));
	        	$timestamp = $dateObject->setTime(0,0)->getTimestamp();
	        	$match = ["createdAt" => ['$gte' => $this->mongo_db->date($timestamp), '$lt' => $this->mongo_db->date($timestamp + 86400 - 1)]];
	        } else {
	        	$match = ["createdAt" => ['$gte' => $this->mongo_db->date(strtotime("today midnight")), '$lt' => $this->mongo_db->date(strtotime("tomorrow midnight"))]];
	        }
	        $department = $this->input->get("department");
	        if($department && $department != "ALL") {
	        	$match["type"] = $department;
	        }
	        // Kendo to aggregate
	        $this->load->library("kendo_aggregate", []);
	        $this->kendo_aggregate->set_kendo_query($request)->selecting();
	        $this->kendo_aggregate->filtering()->matching($match);

	        $group = array('$group' => array(
                "_id" => array( 
                    "y" => array( '$year' => '$createdAt' ),
                    "m" => array( '$month' => '$createdAt' ),
                    "d" => array( '$dayOfMonth' => '$createdAt' ),
                    "h" => array( '$hour' => '$createdAt' ),
                ),
                "first"	=> array('$first' => '$createdAt'),
                "total" => array( '$avg' => '$total' ),
                "inbound" => array( '$avg' => '$inbound' ),
                "outbound" => array( '$avg' => '$outbound' )
	        ));

            $this->kendo_aggregate->adding($group);

	        // Get data
	        $sort = array('$sort' => ["first" => 1]);
	        $this->kendo_aggregate->adding($sort);
	        $data_aggregate = $this->kendo_aggregate->get_data_aggregate();
        	$data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);

        	$timezone = date_default_timezone_get();
        	$timezoneOffset = (new \DateTimeZone($timezone))->getOffset(new \DateTime);
            $response = [];
        	foreach ($data as $doc) {
        		$y = $doc["_id"]["y"];
        		$m = $doc["_id"]["m"];
        		$d = $doc["_id"]["d"];
        		$h = $doc["_id"]["h"] + ($timezoneOffset / 3600);
        		if($h >= 24) $h -= 24;
        		$doc["time"] = "{$h}h - " . ($h+1) . "h";
                if($h >= 5 && $h < 22) {
                    $response[] = $doc;
                }
        	}
	        // Result
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}