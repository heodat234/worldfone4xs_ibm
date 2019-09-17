<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Ticket_solve extends WFF_Controller {

    private $collection = "Ticket";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
    }

    function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $extension = $this->session->userdata("extension");
            $filterStatus = array(
                "logic" 	=> "or",
                "filters" 	=> array(
                    array("field" => "status", "operator" => "eq", "value" => "Open"),
                    array("field" => "status", "operator" => "eq", "value" => "Urgent"),
                    array("field" => "status", "operator" => "eq", "value" => "Assist"),
                    array("field" => "status", "operator" => "eq", "value" => "Pending"),
                )
            );
            if(isset($request["filter"])) {
                $request["filter"]["logic"] = "and";
                $request["filter"]["filters"][] = array("field" => "assign", "operator" => "eq", "value" => $extension);
                $request["filter"]["filters"][] = $filterStatus;
            } else {
                $request["filter"] = array();
                $request["filter"]["logic"] = "and";
                $request["filter"]["filters"] = array();
                $request["filter"]["filters"][] = array("field" => "assign", "operator" => "eq", "value" => $extension);
                $request["filter"]["filters"][] = $filterStatus;
            }
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function detail($id)
    {
        try {
            $response = $this->crud->where_id($id)->getOne($this->collection);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function create()
    {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        try {
            $this->load->library("crud");
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["createdBy"]	=	$this->session->userdata("extension");
            $assignAgent = (!empty($data['assign'])) ? $data['assign'] : '';
            $index_collecion = "Index";
            $fromPage = $data['fromPage'];
            $this->mongo_db->where(array("collection" => $this->collection, 'type' => $fromPage))->update($index_collecion, array('$inc' => array("index" => 1)), array("upsert" => true));
            $indexDoc = $this->mongo_db->where(array("collection" => $this->collection, 'type' => $fromPage))->order_by(array("index" => 1))->getOne($index_collecion);
            $sourceTicket = (!empty($data['source'])) ? $data['source'] : '';
            $data["ticket_id"] = "#TK" . '_' . $fromPage . '_' . (isset($indexDoc["index"]) ? $indexDoc["index"] : 1);
            $data["reply"] = 0;
            unset($data['PNRListDetail']);
            unset($data['isAgentAssign']);
            if(!empty($data['assignGroup'])) {
                $groupInfo = $this->crud->where_id($data['assignGroup'])->getOne(set_sub_collection('Group'));
                if(!empty($groupInfo)) {
                    $data['assign'] = $groupInfo['members'];
                    $data['assignGroupName'] = $groupInfo['name'];
                    $data['assignView'] = $groupInfo['name'];
                }
            }
            elseif (empty($data['assignGroup']) && !empty($data['assign'])) {
                $data['assignView'] = $assignAgent;
                $data['assign'] = array($assignAgent);
            }
            if(!empty($data['service'])) {
                $serviceList = explode(' / ', $data['service']);
                $data['service_level'] = (!empty($serviceList[0])) ? $serviceList[0] : '';
                $data['service_leve2'] = (!empty($serviceList[1])) ? $serviceList[1] : '';
                $data['service_leve3'] = (!empty($serviceList[2])) ? $serviceList[2] : '';
            }
            $result = $this->crud->create($this->collection, $data);
            if(!empty($result)) {
                $log_data = array(
                    'ticket_type'       => 'create',
                    'action_by'         => $this->session->userdata("extension"),
                    'action_time'       => time(),
                    'ticket_id'         => $result["id"],
                    'ticket_info'       => $data,
                    'assign'            => $data['assign']
                );
                $log_ticket_collection = set_sub_collection('Ticket_Logs');
                $this->crud->create($log_ticket_collection, $log_data);
                // Show notifiction after create
                $ticketInfoContent = '1 ticket mới đã được phân công cho ';
                $assignTo = array();
                if(!empty($data['assignGroup']) && $data['assignGroup'] === $data['assign']) {
                    $groupInfoById = $this->crud->where(array('id' => $data['assignGroup']))->getOne(set_sub_collection('Group'));
                    $ticketInfoContent = $ticketInfoContent . 'nhóm ' . $groupInfoById['name'];
                    $assignTo = $groupInfoById['members'];
                    $groupAssign = $data['assignGroup'];
                }
                else {
                    $ticketInfoContent = $ticketInfoContent . 'bạn';
                    $assignTo = $data['assign'];
                }
                $notiInfo = array(
                    'title'             => 'Ticket mới',
                    'content'           => $ticketInfoContent,
                    'active'            => true,
                    'dirty'             => false,
                    'createdBy'         => $this->session->userdata("extension"),
                    'createdAt'         => time(),
                    'to'                => $assignTo,
                    'link'              => "manage/ticket?ticketId=" . $result['id'],
                    'isTicketAssign'    => true,
                    'ticketId'          => $result["id"]
                );
                $this->crud->create(set_sub_collection('Notification'), $notiInfo);
                // Show notifiction after create
            }
            echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function update($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["updatedBy"]	=	$this->session->userdata("extension");
            $assignAgent = (!empty($data['assign'])) ? $data['assign'] : '';
            $isChangeAppTicketStatus = false;
            $ticket_info_old = $this->crud->where_id($id)->getOne($this->collection);
            if(!empty($data['service'])) {
                $serviceList = explode(' / ', $data['service']);
                $data['service_level'] = (!empty($serviceList[0])) ? $serviceList[0] : '';
                $data['service_leve2'] = (!empty($serviceList[1])) ? $serviceList[1] : '';
                $data['service_leve3'] = (!empty($serviceList[2])) ? $serviceList[2] : '';
            }
            if(!empty($data['assignGroup']) && empty($data['assign'])) {
                $groupInfo = $this->crud->where_id($data['assignGroup'])->getOne(set_sub_collection('Group'));
                if(!empty($groupInfo)) {
                    $data['assign'] = $groupInfo['members'];
                    $data['assignGroupName'] = $groupInfo['name'];
                    $data['assignView'] = $groupInfo['name'];
                }
            }
            elseif (empty($data['assignGroup']) && !empty($data['assign'])) {
                $data['assignGroupName'] = '';
                $data['assignView'] = $assignAgent;
                $data['assign'] = array($assignAgent);
            }
            elseif (!empty($data['assignGroup']) && !empty($data['assign'])) {
                $groupInfo = $this->crud->where_id($data['assignGroup'])->getOne(set_sub_collection('Group'));
                $data['assignView'] = $groupInfo['name'] . ' - ' . $assignAgent;
                $data['assign'] = array($assignAgent);
                $data['assignGroupName'] = $groupInfo['name'];
            }
            $result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
            if(!empty($result)) {
                $new_data = $data;
                $old_data = array();
                $update_field = array();
                $ticket_info = $this->crud->where_id($id)->getOne($this->collection);
                foreach ($data as $key => $value) {
                    $old_data[$key] = (!empty($ticket_info_old[$key])) ? $ticket_info_old[$key] : null;
                    array_push($update_field, $key);
                }
                $log_data = array(
                    'ticket_type'       => 'update',
                    'action_by'         => $this->session->userdata("extension"),
                    'action_time'       => time(),
                    'ticket_id'         => $id,
                    'ticket_info'       => $ticket_info,
                    'assign'            => (!empty($data['assign'])) ? $data['assign'] : $ticket_info['assign'],
                    'update_field'      => $update_field,
                    'old_data'          => $old_data,
                    'new_data'          => $new_data
                );
                $log_ticket_collection = set_sub_collection('Ticket_Logs');
                $this->crud->create($log_ticket_collection, $log_data);
                if(!empty($data['assign'])) {
                    $ticketInfoContent = '1 ticket mới đã được phân công cho ';
                    if(count($data['assign']) > 1) {
                        $ticketInfoContent = $ticketInfoContent . 'nhóm ' . $data['assignGroupName'];
                    }
                    else {
                        $ticketInfoContent = $ticketInfoContent . 'bạn';
                    }
                    $notiInfo = array(
                        'title'             => $ticket_info_old['ticket_id'],
                        'content'           => $ticketInfoContent,
                        'active'            => true,
                        'dirty'             => false,
                        'createdBy'         => $this->session->userdata("extension"),
                        'createdAt'         => time(),
                        'to'                => $data['assign'],
                        'link'              => "manage/ticket/solve?ticketId=" . $ticket_info_old['id'],
                        'isTicketAssign'    => true,
                        'ticketId'          => $ticket_info_old["id"],
                        'icon'              => 'gi gi-git_compare',
                        'color'             => 'text-warning',
                    );
                    $this->crud->create(set_sub_collection('Notification'), $notiInfo);
                }
                if(!empty($data['status']) && $ticket_info_old['source'] === 'App Ticket') {
                    $isChangeAppTicketStatus = true;
                }
            }
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function uploadToServer() {
        try {
            if(isset($_FILES['files'])){
                $errors= array();
                $file_name = $_FILES['files']['name'];
                $file_size =$_FILES['files']['size'];
                $file_tmp =$_FILES['files']['tmp_name'];
                $file_type=$_FILES['files']['type'];

                /* $extensions= array("jpeg","jpg","png");

                if(in_array($file_ext,$extensions)=== false){
                    $errors[]="extension not allowed, please choose a JPEG or PNG file.";
                }

                if($file_size > 2097152){
                    $errors[]='File size must be excately 2 MB';
                } */

                if(empty($errors)==true){
                    $file_path = "upload/web/" . time() . '_' . vn_to_str($file_name);
                    move_uploaded_file($file_tmp, $file_path);
                    echo json_encode($file_path);
                } else {
                    print_r($errors);
                }
            }
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function deleteFromServer() {
        try {
            if(isset($_GET['fileNames'])){
                $file_name = $_GET['fileNames'];
                if(unlink($file_name)) {
                    echo json_encode(array("status" => 1, "message" => "", "data" => array()));
                }
            }
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getGroupInfoForAssign() {
        try {
            $this->load->library('mongo_db');
            $request = json_decode($this->input->get("q"), TRUE);
            if(empty($request['isGroup'])) {
                if(!empty($request['group_id'])) {
                    if(!empty($this->session->userdata("isadmin"))) {
                        $listGroup = $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectId($request['group_id'])))->select(array('members'))->get(set_sub_collection('Group'));
                    }
                    else {
                        $listGroup = $this->mongo_db->where(array('members' => $this->session->userdata("extension")))->select(array('members'))->get(set_sub_collection('Group'));
                    }
                }
                else {
                    if(!empty($this->session->userdata("isadmin"))) {
                        $listGroup = $this->mongo_db->select(array('members'))->get(set_sub_collection('Group'));
                    }
                    elseif(empty($this->session->userdata("isadmin")) && !empty($this->session->userdata("issupervisor"))) {
                        $listGroup = $this->mongo_db->where(array('members' => $this->session->userdata("extension")))->select(array('members'))->get(set_sub_collection('Group'));
                    }
                    elseif (empty($this->session->userdata("isadmin")) && empty($this->session->userdata("issupervisor"))) {
                        $listGroup = $this->mongo_db->where(array('members' => $this->session->userdata("extension")))->select(array('members'))->get(set_sub_collection('Group'));
                    }
                }
                $listAgentRaw = array();
                foreach ($listGroup as $group) {
                    if(!empty($group['members'])) {
                        $listAgentRaw = array_merge($listAgentRaw, $group['members']);
                    }
                }
                $listAgentRaw = array_values(array_unique($listAgentRaw));
                $this->load->library('crud');
                $_db = $this->config->item("_mongo_db");
                $this->crud->select_db($_db);
                $listAgent = $this->crud->where(array('extension' => array('$in' => $listAgentRaw)))->get(set_sub_collection('User'));
                echo json_encode($listAgent);
            }
            else {
                $return = $this->mongo_db->select(array('name'))->get(set_sub_collection('Group'));
                echo json_encode($return);
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getGroupBy()
    {
        try {
            $this->load->library("crud");
            $request = json_decode($this->input->get("q"), TRUE);
            $model = $this->crud->build_model($this->collection);
            // Kendo to aggregate
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_kendo_query($request)->selecting();
            $this->kendo_aggregate->filtering();
            // Get total
            $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
            $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
            $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;

            if(!empty($request["group"])) {
                $requestGroup = $request["group"];
                $groupArr = array();
                $concatArr = array();
                if(count($requestGroup) == 1) {
                    $field = $requestGroup[0]["field"];
                    $groupArr = '$' . $field;
                    $concatArr = ['$' . $field];
                    $project = array('$project' => array('idFields' => '$_id', 'count' => 1));
                } else {
                    foreach ($requestGroup as $index => $doc) {
                        $groupArr[$doc["field"]] = '$' . $doc["field"];
                        $concatArr[] = '$_id.' . $doc["field"];
                        if($index + 1 < count($requestGroup)) {
                            $concatArr[] = " - ";
                        }
                    }
                    $project = array('$project' => array('idFields' => array('$concat' => $concatArr), 'count' => 1));
                }
                $group = array('$group' => array(
                    '_id' => $groupArr,
                    'count' => array('$sum' => 1)
                )
                );
                $this->kendo_aggregate->adding($group, $project);
            }
            // Get data
            $this->kendo_aggregate->sorting();
            $data_aggregate = $this->kendo_aggregate->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
            // Result
            $response = array("data" => $data, "total" => $total);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function getPNR($pnr_code) {
        try {
            $result = array();
            $this->load->model('ticket_model');
            $this->load->model('Navitaire_model');
            $checkPNRCode = $this->ticket_model->getOneFromCollection(array('RecordLocator' => $pnr_code), 'Booking', false);
            if(!empty($checkPNRCode)) {
                $passengerInfo = $this->ticket_model->getFromCollectionByCondition(array('BookingID' => $checkPNRCode['BookingID']), 'BookingPassenger', false);
                if(!empty($passengerInfo)) {
                    $listPassenger = array_column($passengerInfo, 'PassengerID');
                    $listTotalPriceByPassengerID = array_column($passengerInfo, 'TotalCost', 'PassengerID');
                    $journeyLegInfo = $this->ticket_model->getFromCollectionByConditionSort(array('PassengerID' => array('$in' => $listPassenger)), array('DepartureDate' => 'asc'), 'PassengerJourneyLeg', false);
                    $totalPriceByPassengerID = 0;
                    $result = array('pnr_code' => $pnr_code, 'pnr_state' => 'success');
                    $flightInfo = '';
                    foreach ($journeyLegInfo as $key => $value) {
                        $flightInfo = $flightInfo . ($key + 1) . '. ' . $value['CarrierCode'] . '-' . $value['FlightNumber'] . '  ' . timestampToFormattedString($value['DepartureDate'], 'D, dMy') . ' ' . $value['DepartureStation'] . '-' . $value['ArrivalStation'] . ' ' . timestampToFormattedString($value['LegSTD'], 'H:i') . ' - ' . timestampToFormattedString($value['LegSTA'], 'H:i') . "<br />";
                    }
                    $result['pnr_info'] = $flightInfo;
                }
                echo json_encode(array("status" => 1, "message" => "", "data" => $result));
            }
            else {
                $checkPNRCode = $this->Navitaire_model->getBooking($pnr_code);
                if(!empty($checkPNRCode) && !empty($checkPNRCode['Booking'])) {
                    $result = array('pnr_code' => $pnr_code, 'pnr_state' => 'success');
                    $pnr_info = '';
                    if(is_object($checkPNRCode['Booking']['Journeys']['Journey']['Segments']['Segment'])) {
                        $segment = $checkPNRCode['Booking']['Journeys']['Journey']['Segments']['Segment'];
                        $pnr_info = '1. ' . $segment['FlightDesignator']['CarrierCode'] . '-' . $segment['FlightDesignator']['FlightNumber'] . '  ' . stringDateToFormattedString($segment['STA'], 'D, dMy') . ' ' . $segment['DepartureStation'] . '-' . $segment['ArrivalStation'] . ' ' . stringDateToFormattedString($segment['STD'], 'H:i') . '-' . stringDateToFormattedString($segment['STA'], 'H:i');
                    }
                    else {
                        $segmentList = $checkPNRCode['Booking']['Journeys']['Journey']['Segments'];
                        foreach ($segmentList as $key => $value) {
                            $pnr_info = $pnr_info . ($key + 1) . '. ' . $value['FlightDesignator']['CarrierCode'] . '-' . $value['FlightDesignator']['FlightNumber'] . '  ' . stringDateToFormattedString($value['STA'], 'D, dMy') . ' ' . $value['DepartureStation'] . '-' . $value['ArrivalStation'] . ' ' . stringDateToFormattedString($value['STD'], 'H:i') . '-' . stringDateToFormattedString($value['STA'], 'H:i') . '<br />';
                        }
                    }
                    $result['pnr_info'] = $pnr_info;
                    echo json_encode(array("status" => 1, "message" => "", "data" => $result));
                }
                else {
                    echo json_encode(array("status" => 0, "message" => "No PNR Info", "data" => array()));
                }
            }
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}