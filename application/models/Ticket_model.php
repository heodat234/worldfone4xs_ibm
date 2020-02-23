<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Ticket_model extends CI_Model {

	private $sub = "";
	private $collection = 'Ticket';

	function __construct()
	{
		parent::__construct();
		$this->load->library("mongo_db");
	}

    function getTicketInfoById($id) {
	    return $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectId($id)))->getOne(set_sub_collection($this->collection));
    }

    function assignToExtension($id, $updateData) {
	    try {
            $result = $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectId($id)))->set($updateData)->update(set_sub_collection('Ticket'));
            if(!empty($result)) {
                return array('status' => 1, 'message' => 'Successed assign');
            }
        } catch (Exception $e) {
            return array('status' => 0, 'message' => $e->getMessage());
        }

    }

    function addLogs($logInfo) {
	    try {
            return $this->mongo_db->insert(set_sub_collection('Ticket_Logs'), $logInfo);
        } catch(Exception $e) {

        }
    }

    function getOneFromCollection($where, $collection, $isSubCollection = true) {
	    if($isSubCollection) {
            return $this->mongo_db->where($where)->getOne(set_sub_collection($collection));
        }
        else {
            return $this->mongo_db->where($where)->getOne($collection);
        }
    }

    function getFromCollectionByCondition($where, $collection, $isSubCollection = true) {
	    if($isSubCollection) {
            return $this->mongo_db->where($where)->get(set_sub_collection($collection));
        }
        else {
            return $this->mongo_db->where($where)->get($collection);
        }
    }

    function getFromCollectionByConditionSort($where, $sort, $collection, $isSubCollection = true) {
	    if($isSubCollection) {
            return $this->mongo_db->where($where)->order_by($sort)->get(set_sub_collection($collection));
        }
        else {
            return $this->mongo_db->where($where)->get($collection);
        }
    }
}