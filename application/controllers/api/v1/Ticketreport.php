<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Ticketreport extends WFF_Controller {

	private $collection = "Ticket";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->collection = set_sub_collection($this->collection);
	}

	function readTicketSource() {
	    $this->load->library('mongo_db');
        $request = json_decode($this->input->get("q"), TRUE);
	    try {
	        $data = array();
	        $pipeline = array(
	            array(
                    '$group'                => array(
	                    '_id'               => array(
	                        'source'        => '$source'
                        ),
                        'total'             => array(
                            '$sum'          => 1
                        ),
                        'open'              => array(
                            '$sum'          => array(
                                '$cond'     => array(array(
                                    '$eq'   => array('$status', 'Open')),
                                    1,
                                    0)
                            )
                        ),
                        'pending'           => array(
                            '$sum'          => array(
                                '$cond'     => array(array(
                                    '$eq'   => array('$status', 'Pending')),
                                    1,
                                    0)
                            )
                        ),
                        'close'             => array(
                            '$sum'          => array(
                                '$cond'     => array(array(
                                    '$eq'   => array('$status', 'Closed')),
                                    1,
                                    0)
                            )
                        ),
                    )
                ),
            );
	        $pipeline_count = $pipeline;
	        $pipeline_count[] = array(
	            '$count' => 'total'
            );
	        $total = $this->mongo_db->aggregate_pipeline($this->collection, $pipeline_count);
            if(!empty($total)) {
                $total = $total[0]['total'];
            }
            else {
                $total = 0;
            }
//            $pipeline = array_merge($pipeline, array(array('$skip' => $request['skip']), array('$limit' => $request['take'])));
	        $data = $this->mongo_db->aggregate_pipeline($this->collection, $pipeline);
            echo json_encode(array("status" => 10, "message" => "", "data" => $data, "total" => $total));
        }
	    catch(Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}