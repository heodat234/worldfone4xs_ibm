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
					array("field" => "status", "operator" => "eq", "value" => "Assist")
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

            $index_collecion = "Index";
            $fromPage = $data['fromPage'];
            $this->mongo_db->where(array("collection" => $this->collection, 'type' => $fromPage))->update($index_collecion, array('$inc' => array("index" => 1)), array("upsert" => true));
            $indexDoc = $this->mongo_db->where(array("collection" => $this->collection, 'type' => $fromPage))->order_by(array("index" => 1))->getOne($index_collecion);
            $sourceTicket = (!empty($data['source'])) ? $data['source'] : '';
            /* switch($sourceTicket) {
                case ''
            } */
            $data["ticket_id"] = "#TK" . '_' . $fromPage . '_' . (isset($indexDoc["index"]) ? $indexDoc["index"] : 1);
            $data["reply"] = 0;
            unset($data['PNRListDetail']);
            if(!empty($data['assignGroup'])) {
                $data['assign'] = $data['assignGroup'];
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
                $ticketInfoContent = '@A new ticket has assiged for@ ';
                $assignTo = array();
                if(!empty($data['assignGroup']) && $data['assignGroup'] === $data['assign']) {
                    $groupInfoById = $this->crud->where(array('id' => $data['assignGroup']))->getOne(set_sub_collection('Group'));
                    $ticketInfoContent = $ticketInfoContent . '@group@ ' . $groupInfoById['name'];
                    $assignTo = $groupInfoById['members'];
                    $groupAssign = $data['assignGroup'];
                }
                else {
                    $ticketInfoContent = $ticketInfoContent . '@you@';
                    $assignTo = array($data['assign']);
                }
                $notiInfo = array(
                    'title'             => '@New ticket@',
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
            $this->load->library("crud");
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["updatedBy"]	=	$this->session->userdata("extension");
            $ticket_info_old = $this->crud->where_id($id)->getOne($this->collection);
            $result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
            if(!empty($result)) {
                $new_data = $data;
                $old_data = array();
                $update_field = array();
                $ticket_info = $this->crud->where_id($id)->getOne($this->collection);
                if(!empty($data['assignGroup'])) {
                    $data['assign'] = $data['assignGroup'];
                    $new_data['assign'] = $data['assignGroup'];
                }
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
                    $groupInfoById = $this->crud->where_id($data['assignGroup'])->getOne(set_sub_collection('Group'));
                    $ticketInfoContent = '@A new ticket has assiged for@ ';
                    $assignTo = array();
                    if(!empty($data['assignGroup']) && $data['assignGroup'] === $data['assign']) {
                        $groupInfoById = $this->crud->where(array('id' => $data['assignGroup']))->getOne(set_sub_collection('Group'));
                        $ticketInfoContent = $ticketInfoContent . '@group@ ' . $groupInfoById['name'];
                        $assignTo = $groupInfoById['members'];
                        $groupAssign = $data['assignGroup'];
                    }
                    else {
                        $ticketInfoContent = $ticketInfoContent . '@you@';
                        $assignTo = array($data['assign']);
                    }
                    $notiInfo = array(
                        'title'             => '@New ticket@',
                        'content'           => $ticketInfoContent,
                        'active'            => true,
                        'dirty'             => false,
                        'createdBy'         => $this->session->userdata("extension"),
                        'createdAt'         => time(),
                        'to'                => $assignTo,
                        'link'              => "manage/ticket?ticketId=" . $ticket_info_old['id'],
                        'isTicketAssign'    => true,
                        'ticketId'          => $ticket_info_old["id"]
                    );
                    $this->crud->create(set_sub_collection('Notification'), $notiInfo);
                }
            }
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}