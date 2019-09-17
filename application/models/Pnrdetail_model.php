<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Pnrdetail_model extends CI_Model {

	private $sub = "";
	private $collection = 'Ticket';

	function __construct()
	{
		parent::__construct();
		$this->load->library("mongo_db");
	}

	function getOne($where, $collection) {
	    return $this->mongo_db->where($where)->getOne($collection);
    }

	function getByCondition($where, $collection) {
	    return $this->mongo_db->where($where)->get($collection);
    }

	function getByConditionSort($where, $sort, $collection) {
	    return $this->mongo_db->where($where)->order_by($sort)->get($collection);
    }

	function getByConditionSelectColumn($where, $select, $collection) {
	    return $this->mongo_db->where($where)->select($select)->get($collection);
    }

    function getBookingInfoByPnr($pnr_code) {
        return $this->mongo_db->where(array('RecordLocator' => $pnr_code))->getOne('Booking');
    }

    function getPassengerInfoByBookingID($BookingID) {
        return $this->mongo_db->where(array('BookingID' => $BookingID))->get('BookingPassenger');
    }

    function insertPNRIntoDB($data) {
	    $this->mongo_db->insert('tempPNR', $data);
    }

    function getSSRsByPassengerID($PassengerID) {
	    return $this->mongo_db->where(array('PassengerID' => $PassengerID))->get('PassengerJourneySSR');
    }

    function getJourneysByPassengerID($PassengerID) {
	    return $this->mongo_db->where(array('PassengerID' => $PassengerID))->get('PassengerJourneyLeg');
    }

    function checkPNR($pnr_code) {
	    return $this->mongo_db->where(array('RecordLocator' => $pnr_code))->count('Booking');
    }
}