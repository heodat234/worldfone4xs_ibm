<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
$url_libraries = __DIR__ . "/../../libraries";
require_once $url_libraries . "/omnisales-sdk/autoload.php";

use Omnisales\Omnisales;
use Omnisales\OmnisalesApp;

//start code Tram 
class search extends WFF_Controller {

    public function __construct() {
        parent::__construct();
        $this->username = $this->session->userdata('extension');
        $this->name = $this->session->userdata('extension');
        $this->userextension = $this->session->userdata('extension');
        $this->agentname = $this->session->userdata('agentname');
        $this->parent_user = $this->session->userdata('parent_user');
        $this->parent_id = $this->session->userdata('parent_id');
        $this->load->model('models_chat/search_chat_model', 'search_model');
        $this->load->library('mongo_db');

        $data_config = array(
            "app_id" => $this->config->item('omnisale_app_id'),
            "app_secret" => $this->config->item('omnisale_app_secret'),
        );
        // exit('12323');
    }

    function getCustomerField() {
        header('Content-Type: application/json');
        $listField = $this->search_model->getCustomerField($this->userextension);
        echo json_encode($listField);
    }

    function searchCusInfo() {
        header('Content-Type: application/json');
        $request = json_decode(file_get_contents('php://input'), TRUE);
        $chatGroupId = $request['chatGroupId'];
        //$listField = json_decode(json_encode($request->listFiel), true);
        //$listField = array_column($listField, 'key');
        $listCus = $this->search_model->searchCus($chatGroupId,array("phone","name","email","address", "socials")/* $listField*/, $request);
        foreach ($listCus['data'] as $key => $value) {
            $listCus['data'][$key]['DOB'] = isset($value['DOB']) ? date("d/m/Y", $value['DOB']) : "";
        }
        echo json_encode($listCus);
    }

    function mappingProfileChatTo4x() {
        $chatGroupInfo = $this->search_model->getChatGroupInfo($this->input->post('chatGroupId'));
        if(!empty($chatGroupInfo)) {
            $people_id = ($chatGroupInfo['from']['type'] === 'customer') ? $chatGroupInfo['from']['user_id'] : $chatGroupInfo['to']['user_id'];
            $peopleInfo = $this->search_model->getPeopleInfo($people_id);
            $mappingInfo = array(
                'type'      => $chatGroupInfo['type'],
                'people_id'        => $people_id,
                'name'      => (!empty($peopleInfo)) ? $peopleInfo['name'] : '',
                'source'    => $chatGroupInfo['source']
            );
            $result = $this->search_model->mappingProfileChatTo4x($this->input->post('CustomersId4x'), $mappingInfo);
            $result1 = $this->search_model->mappingProfile4xToChat($people_id, $chatGroupInfo['page_id'], $this->input->post('CustomersId4x'));
            if(!empty($result) && !empty($result1)) {
                echo json_encode(array('status' => "1", "message" => "success", "data" => array()));
            }
            else {
                echo json_encode(array('status' => "0", "message" => "error", "data" => array()));
            }
        }
        else {
            echo json_encode(array('status' => "0", "message" => "error", "data" => array()));
        }
    }

    function UnMappingPeopleAndCustomer4x() {
        $chatGroupInfo = $this->search_model->getChatGroupInfo($this->input->post('chatGroupId'));
        if(!empty($chatGroupInfo)) {
            $people_id = ($chatGroupInfo['from']['type'] === 'customer') ? $chatGroupInfo['from']['user_id'] : $chatGroupInfo['to']['user_id'];
            $peopleInfo = $this->search_model->getPeopleInfo($people_id);
            $mappingInfo = array(
                'type'      => $chatGroupInfo['type'],
                'people_id'        => $people_id,
                'name'      => (!empty($peopleInfo)) ? $peopleInfo['name'] : '',
                'source'    => $chatGroupInfo['source']
            );
            // $result = $this->search_model->mappingProfileChatTo4x($this->input->post('CustomersId4x'), $mappingInfo);
            // $result1 = $this->search_model->mappingProfile4xToChat($people_id, $chatGroupInfo['page_id'], $this->input->post('CustomersId4x'));

            $customer_info = $this->mongo_db->where_id($this->input->post('CustomersId4x'))->getOne(set_sub_collection('Customer'));
            $people_id_filter = array();
            $socials_update = array();
            if (isset($customer_info['socials'])) {
                $socials_update = array();
                foreach ($customer_info['socials'] as $social) {
                    /*var_dump('______start_______');
                    var_dump($social);
                    var_dump($social->people_id);  
                    var_dump($people_id);
                    var_dump('_______end______');*/
                $social_people_id = $social->people_id;            
                    if ( $people_id != $social_people_id) {
                        $socials_update[] = $social;
                    }
                }
            }

            $result = $this->mongo_db->where_id($this->input->post('CustomersId4x'))->set('socials', $socials_update)->update(set_sub_collection('Customer'));
            $result1 = $this->mongo_db->where(array('people_id' => $people_id, 'page_id' => $chatGroupInfo['page_id']))->set(array('customer_4x_id' =>''))->update('people');
            if(!empty($result) && !empty($result1)) {
                echo json_encode(array('status' => "1", "message" => "success", "data" => array()));
            }
            else {
                echo json_encode(array('status' => "0", "message" => "error", "data" => array()));
            }
        }
        else {
            echo json_encode(array('status' => "0", "message" => "error", "data" => array()));
        }
    }

    function getCustomer4xInfo() {
        header('Content-Type: application/json');
        $data = array();
        $chatGroupInfo = $this->search_model->getChatGroupInfo($this->input->post('room_id'));
        // var_dump($chatGroupInfo);
        if(!empty($chatGroupInfo)) {
            $people_id = ($chatGroupInfo['from']['type'] === 'customer') ? $chatGroupInfo['from']['user_id'] : $chatGroupInfo['to']['user_id'];
            $peopleInfo = $this->search_model->getPeopleInfo($people_id);
             // var_dump($peopleInfo);
            if(!empty($peopleInfo) && !empty($peopleInfo['customer_4x_id'])) {
                // var_dump($peopleInfo);
                $data = $this->search_model->getCustomer4xInfo($peopleInfo['customer_4x_id']);
            }
        }
        echo json_encode($data);
    }
}
