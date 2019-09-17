<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Chatstatus_model extends CI_Model {

	private $WFF;
	private $sub;
	private $collection = "Chat_status";
    private $collection_reference = "Chat_status_code";
	private $time_cache = 60;

	private $user_collection = "User";

	function __construct() {
		parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->time_cache = $this->config->item("wff_time_cache");

        $this->sub = set_sub_collection("");
        $this->collection = $this->sub . $this->collection;
    }

 	function getOne($select = array(), $unselect = array()) {
 		$time = time();
 		$extension = $this->session->userdata("extension");
 		$data = $this->mongo_db->where(array("extension" => $extension, "endtime" => 0))
 			->where_gt("lastupdate", $time - $this->config->item("sess_time_to_update"))
 			->select($select, $unselect)
 			->order_by(array('starttime' => -1))
 			->getOne($this->collection);
 		return $data;
 	}

    function start($data = array()) 
    {
    	$time = time();
    	$extension = $this->session->userdata("extension");
        $my_session_id = $this->session->userdata("my_session_id");

        $this->update_previous($extension);
		
        //start insert agentstatuslogs
        $default_data = array(
        	"extension" 		=> $extension,
            "statuscode" 		=> 1,
            "substatus" 		=> "",
            "starttime" 		=> $time,
            "endtime" 			=> 0,
            "lastupdate" 		=> $time,
            "my_session_ids" 	=> [$my_session_id],
        );
        // Get status before
        $lastStatus = $this->mongo_db->order_by(array("starttime" => -1, "extension" => $extension))->getOne($this->collection);
        if($lastStatus) {
            $default_data["statuscode"] = isset($lastStatus["statuscode"]) ? $lastStatus["statuscode"] : 1;
            $default_data["substatus"] = isset($lastStatus["substatus"]) ? $lastStatus["substatus"] : "";
        }

        $insert_data = array_merge($default_data, $data);
        $result = $this->mongo_db->insert($this->collection, $insert_data);
        if($result) {
        	$user_collection = $this->sub . $this->user_collection;
    		$this->load->library("mongo_private");
        	$this->mongo_private->where(array("extension" => $extension))
        	->update($user_collection, array(
        		'$set' => array(
        			"chat_statuscode"	=> $insert_data["statuscode"],
        			"chat_substatus"	=> $insert_data["substatus"]
        		)
        	));
        }
    	return $result;
    }

    function end($data = array())
    {
    	$time = time();
    	$update_data = array_merge($data, array('endtime'=> $time));
    	$extension = $this->session->userdata("extension");
    	$this->mongo_db->where(array('extension' => $extension, "endtime" => 0))
                ->set($update_data)
                ->update_all($this->collection);
    }

    function update($data = array()) 
    {
    	$extension = $this->session->userdata("extension");
        $time = time();
        
	    // END Agent state

		$default_data = array(
			"lastupdate" => $time
		);
		$check_data = $this->getOne(["statuscode","substatus"]);
		if(!$check_data) $this->start(array("note" => "Start after login"));

        $update_data = array_merge($default_data, $data);
        if($currentStatus = $this->getOne()) {
        	$my_session_id = $this->session->userdata("my_session_id");
			$this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectId($currentStatus["id"])))
		                ->set($update_data)
		                ->addtoset("my_session_ids", $my_session_id)
		                ->update($this->collection);
	    }
    }

    private function update_previous($extension)
    {
    	// TH: User tat trinh duyet, khong dang xuat
    	$time = time();
    	$data = $this->mongo_db
    	->where(array("endtime" => 0, "extension" => $extension))
    	->select(["_id", "lastupdate"])
    	->get($this->collection);
    	if($data) {
	    	foreach ($data as $doc) {
	    		if( $time > $doc["lastupdate"] + $this->config->item("sess_time_to_update")) 
	            {
	                // Qua thoi han session update
		    		$this->mongo_db->where(array("_id" => new MongoDB\BSON\ObjectId($doc["id"])))
		    		->set(array("endtime" => $doc["lastupdate"], "endnote" => "No connect too long"))
		    		->update($this->collection);
		    	}
	    	}
    	}
    }

    function change($data = array())
    {
		if(!isset($data["statuscode"])) {
			throw new Exception("Need state");
		}
		$status = (int) $data["statuscode"];
		
		$extension = $this->session->userdata("extension");

		//change state process
        $note 		= "User change status";

        //log operation
		$substatus = "";
		if(!empty($data["substatus"])) {
			$substatus = $data["substatus"];
		}

		// End current log
    	$this->end(array("endnote" => $note));
    	// Start new log
    	return $this->start(array(
    		"statuscode"	=> $status,
    		"substatus" 	=> $substatus,
    		"note" 			=> $note
    	));
    }

    function getOneByExtension($extension) {
 		$time = time();
 		$data = $this->mongo_db->where(array("extension" => $extension, "endtime" => 0))
 			->where_gt("lastupdate", $time - 10)
 			->order_by(array('starttime' => -1))
 			->getOne($this->collection);
 		return $data;
 	}

    function get_today_by_extension($extension)
    {
        $aggregate = array(
            array('$match' => array(
                    "extension" => $extension,
                    "starttime" => array('$gte' => strtotime('today midnight')),
                    "lastupdate"=> array('$gt'  => strtotime('today midnight'))
                )
            ),
            array('$sort' => array("starttime" => 1, "lastupdate" => 1)),
            array('$group' => array(
                    "_id"           => array(
                        "extension"     => '$extension',
                        "statuscode"    => '$statuscode'
                    ),
                    "last_substatus"=> array('$last' => '$substatus'),
                    "last_starttime"=> array('$last' => '$starttime'),
                    "last_endtime"  => array('$last' => '$endtime'),
                    "last_update"   => array('$last' => '$lastupdate'),
                    "total_time"    => array('$sum' => array('$subtract' => ['$lastupdate', '$starttime']))
                )
            ),
            array('$project' => array(
                    "statuscode"        => '$_id.statuscode',
                    "_id"               => 0,
                    "last_substatus"    => 1,
                    "last_starttime"    => 1,
                    "last_endtime"      => 1,
                    "last_update"       => 1,
                    "total_time"        => 1
                )
            ),
            array('$sort' => array("statuscode" => 1)),
            array('$lookup' => array(
                    "from"          => $this->sub . $this->collection_reference,
                    "localField"    => "statuscode",
                    "foreignField"  => "value",
                    "as"            => "status"
                )
            ),
            array('$unwind' => array(
                    'path'                          => '$status',
                    'preserveNullAndEmptyArrays'    => TRUE
                )
            ),
            array('$project' => array(
                    "statuscode"                    => 1,
                    "last_substatus"                => 1,
                    "last_starttime"                => 1,
                    "last_endtime"                  => 1,
                    "last_update"                   => 1,
                    "total_time"                    => 1,
                    "statustext"                    => '$status.text'
                )
            )
        );
        $data = $this->mongo_db->aggregate_pipeline($this->collection, $aggregate);
        return $data;
    }

    function start_from_other($data = array(), $extension) 
    {
        $time = time();
        $user_collection = $this->sub . $this->user_collection;
        $this->load->library("mongo_private");
        $user = $this->mongo_private->where(array("extension" => $extension))->getOne($user_collection);
        if(!isset($user["current_my_session_id"])) throw new Exception("No current my session id exists");
        $my_session_id = $user["current_my_session_id"];
        
        //start insert agentstatuslogs
        $default_data = array(
            "extension"         => $extension,
            "statuscode"        => 1,
            "substatus"         => "",
            "starttime"         => $time,
            "endtime"           => 0,
            "lastupdate"        => $time,
            "my_session_ids"    => [$my_session_id],
        );

        $insert_data = array_merge($default_data, $data);
        $result = $this->mongo_db->insert($this->collection, $insert_data);
        if($result) {
            $this->load->library("mongo_private");
            $this->mongo_private->where(array("extension" => $extension))
            ->update($user_collection, array(
                '$set' => array(
                    "chat_statuscode"   => $insert_data["statuscode"],
                    "chat_substatus"    => $insert_data["substatus"]
                )
            ));
        }
        return $result;
    }

    function end_from_other($data = array(), $extension)
    {
        $time = time();
        $update_data = array_merge($data, array('endtime'=> $time));
        $this->mongo_db->where(array('extension' => $extension, "endtime" => 0))
                ->set($update_data)
                ->update_all($this->collection);
    }

    function change_from_other($extension, $data = array())
    {
        if(!isset($data["statuscode"])) {
            throw new Exception("Need status");
        }
        $status = (int) $data["statuscode"];

        $change_extension = $this->session->userdata("extension");

        //change state process
        $note       = "User change status";

        //log operation
        $substatus = "";
        if(!empty($data["substatus"])) {
            $substatus = $data["substatus"];
        }

        // End current log
        $this->end_from_other(array("endnote" => "{$change_extension} change"), $extension);
        // Start new log
        return $this->start_from_other(array(
            "statuscode"    => $status,
            "substatus"     => $substatus,
            "note"          => $note
        ), $extension);
    }
}