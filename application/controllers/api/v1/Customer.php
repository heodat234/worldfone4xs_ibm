<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Customer extends WFF_Controller {

	private $collection = "Customer";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("mongo_db");
		$this->load->model("Navitaire_model");
		$this->collection = set_sub_collection($this->collection);
	}

	function getPassengerIDByCustomerNumber() {
	    try {
	        $result = array('BookingID' => array(), 'PassengerID' => array());
            $request = json_decode($this->input->get("q"), TRUE);
            $bookingPassengerListByCusNu = $this->mongo_db->where(array('CustomerNumber' => (string)$request['value']['CustomerNumber']))->order_by(array('CreatedDate' => 'desc'))->select(array('PassengerID', 'BookingID'))->get('BookingPassenger');
            if(!empty($bookingPassengerListByCusNu)) {
                $listBookingID = array_column($bookingPassengerListByCusNu, 'BookingID');
                $result['BookingID'] = $listBookingID;
                $listPassengerID = array_column($bookingPassengerListByCusNu, 'PassengerID');
                $result['PassengerID'] = $listPassengerID;
            }
            echo json_encode($result);
	    }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

	function getPNRHistory() {
	    try {
            $request = json_decode($this->input->get("q"), TRUE);
            $pnrHistory = array();
            $total = 0;
	        if(!empty($request['customer_id'])) {
                $recordLocator = $this->mongo_db->where(array('customer_id' => $request['customer_id']))->select(array('RecordLocator'))->getOne('BookingCache');
                if(!empty($recordLocator)) {
                    foreach ($recordLocator['RecordLocator'] as $key => $value) {
                        $bookingInfo = $this->mongo_db->where(array('RecordLocator' => $value))->select(array('BookingID', 'PassengerID'))->getOne('Booking');
                        $passengerInfo = $this->mongo_db->where(array('BookingID' => $bookingInfo['BookingID']))->select(array('PassengerID'))->get('BookingPassenger');
                        $numberOfPax = count($passengerInfo);
                        $flightDetail = $this->mongo_db->where(array('PassengerID' => $passengerInfo[0]['PassengerID']))->order_by(array('DepartureDate' => 'asc'))->select(array('SegmentID', 'CarrierCode', 'FlightNumber', 'DepartureDate', 'DepartureStation', 'ArrivalStation', 'JourneyNumber', 'PassengerID', 'LegNumber'))->get('PassengerJourneyLeg');
//                    print_r($flightDetail);
                        array_push($pnrHistory, array('RecordLocator' => $value, 'numberOfPax' => $numberOfPax, 'segment' => $flightDetail));
                    }
                    if(!empty($pnrHistory)) {
                        $total = count($pnrHistory);
                    }
                }
	        }
            echo json_encode(array("status" => 1, "message" => "", "data" => $pnrHistory, "total" => $total));
        } catch(Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
	}

    function getSSRsHistory() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $ssrsHistory = array();
            $total = 0;
            if(!empty($request['customer_id'])) {
                $bookingCacheInfo = $this->mongo_db->where(array('customer_id' => $request['customer_id']))->select(array('PassengerID'))->getOne('BookingCache');
                if(!empty($bookingCacheInfo)) {
                    $ssrsPipeline = array(
                        array(
                            '$match'            => array(
                                'PassengerID'   => array(
                                    '$in'       => $bookingCacheInfo['PassengerID']
                                )
                            )
                        ),
                        array(
                            '$group'            => array(
                                '_id'           => '$SSRCode',
                                'PassengerID'   => array(
                                    '$push'     => '$PassengerID'
                                )
                            )
                        )
                    );
                    $ssrsList = $this->mongo_db->aggregate_pipeline('PassengerJourneySSR', $ssrsPipeline);

                    if(!empty($ssrsList)) {
                        foreach ($ssrsList as $key => $value) {
                            $listBookingInfoByPassemgerId = $this->mongo_db->where(array('PassengerID' => array('$in' => $value['PassengerID'])))->select(array('BookingID'))->get('BookingPassenger');
                            $listBookingId = array_column($listBookingInfoByPassemgerId, 'BookingID');
                            $listPNRInfoByBookingId = $this->mongo_db->where(array('BookingID' => array('$in' => $listBookingId)))->select(array('RecordLocator'))->get('Booking');
                            array_push($ssrsHistory, array('ssrsCode' => $value['_id'], 'numOfBuy' => count($value['PassengerID']), 'listPNR' => $listPNRInfoByBookingId));
                        }
                        if(!empty($ssrsHistory)) {
                            $total = count($ssrsHistory);
                        }
                    }
                }
            }
            echo json_encode(array("status" => 1, "message" => "", "data" => $ssrsHistory, "total" => $total));
        } catch(Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getRelatedACC() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $accRelated = array('relatedByPhone' => array(), 'relatedByEmail' => array());
            if(!empty($request['id'])) {
                $customerInfo = $this->mongo_db->where_id($request['id'])->select(array('CustomerPhone', 'CustomerEmail', 'email', 'phone'))->getOne($this->collection);
                $relatedByPhone = array();
                $relatedByEmail = array();
                if(!empty($customerInfo['CustomerPhone'])) {
                    foreach ($customerInfo['CustomerPhone'] as $key => $value) {
                        $relatedByPhoneTemp = $this->mongo_db->where(array('CustomerPhone.Number' => $value['Number'], '_id' => array('$ne' => new MongoDB\BSON\ObjectId($request['id']))))->select(array('CustomerNumber', 'name'))->get($this->collection);
                        if(!empty($relatedByPhoneTemp)) {
                            $relatedByPhone = array_merge($relatedByPhone, $relatedByPhoneTemp);
                        }
                    }
                    $accRelated['relatedByPhone'] = $relatedByPhone;
                }
                elseif(!empty($customerInfo['phone'])) {
                    $relatedByPhoneTemp = $this->mongo_db->where(array('phone' => $customerInfo['phone'], '_id' => array('$ne' => new MongoDB\BSON\ObjectId($request['id']))))->select(array('CustomerNumber', 'name'))->get($this->collection);
                    if(!empty($relatedByPhoneTemp)) {
                        $accRelated['relatedByPhone'] = array_merge($relatedByPhone, $relatedByPhoneTemp);
                    }
                }
                if(!empty($customerInfo['CustomerEmail'])) {
                    foreach ($customerInfo['CustomerEmail'] as $key => $value) {
                        $relatedByEmailTemp = $this->mongo_db->where(array('CustomerPhone.EMailAddress' => $value['EMailAddress'], '_id' => array('$ne' => new MongoDB\BSON\ObjectId($request['id']))))->select(array('CustomerNumber', 'name'))->get($this->collection);
                        if(!empty($relatedByEmailTemp)) {
                            $relatedByEmail = array_merge($relatedByEmail, $relatedByEmailTemp);
                        }
                    }
                    $accRelated['relatedByEmail'] = $relatedByEmail;
                }
                else {
                    if(!empty($customerInfo['email'])) {
                        $relatedByEmailTemp = $this->mongo_db->where(array('email' => $customerInfo['email'], '_id' => array('$ne' => new MongoDB\BSON\ObjectId($request['id']))))->select(array('CustomerNumber', 'name'))->get($this->collection);
                        if(!empty($relatedByEmailTemp)) {
                            $accRelated['relatedByEmail'] = $relatedByEmailTemp;
                        }
                    }
                }
            }
            echo json_encode(array("status" => 1, "message" => "", "data" => $accRelated));
        } catch(Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getCustomerByCustomerNumber($CustomerNumber) {
	    try {
            $result = $this->mongo_db->where(array('CustomerNumber' => (int)$CustomerNumber))->getOne($this->collection);
            echo json_encode(array("status" => 1, "message" => "", "data" => $result));
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}