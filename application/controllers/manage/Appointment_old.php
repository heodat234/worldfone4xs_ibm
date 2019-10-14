<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AppointmentOld extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
        $this->load->model("Ticket_model");
    	$only_main_content = (bool) $this->input->get("omc");
        $this->_build_template($only_main_content);
    }

	public function index()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/manage/appointment.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('manage/appointment_view');
	}

	public function usersappointment()
	{
		$this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = STEL_PATH . "js/manage/ticket_solve.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
		$this->load->view('manage/usersappointment_view');
	}

	public function pnrDetail() {
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        $this->load->view('manage/pnrDetail_view_old');
    }

    function assignTicketToExtension($ticketId) {
        try {
            $ticketInfoById = $this->Ticket_model->getTicketInfoById($ticketId);
            if(!empty($ticketInfoById['assignGroup']) && $ticketInfoById['assignGroup'] === $ticketInfoById['assignGroup']) {
                if(count($ticketInfoById['assign']) > 1) {
                    $updateData = array(
                        'assign'        => array($this->session->userdata("extension")),
                        'assignView'    => $ticketInfoById['assignGroupName'] . ' - ' . $this->session->userdata("extension")
                    );
                }
                else {
                    $updateData = array(
                        'assign'        => array($this->session->userdata("extension")),
                        'assignView'    => $this->session->userdata("extension")
                    );
                }
                $result = $this->Ticket_model->assignToExtension($ticketId, $updateData);
                $log_data = array(
                    'ticket_type'       => 'create',
                    'action_by'         => $this->session->userdata("extension"),
                    'action_time'       => time(),
                    'ticket_id'         => $ticketId,
                    'ticket_info'       => $ticketInfoById,
                    'assign'            => $this->session->userdata("extension")
                );
                $this->Ticket_model->addLogs($log_data);
                return array("status" => 1, 'message' => 'Assign ticket thành công.');
            }
            else {
                return array("status" => 0, 'message' => 'Đã có người nhận ticket này.');
            }
        } catch(Exception $e) {
            return array("status" => 0, 'message' => 'Assign ticket bị lỗi.');
        }
    }

    function getJSONFile() {

    }
}