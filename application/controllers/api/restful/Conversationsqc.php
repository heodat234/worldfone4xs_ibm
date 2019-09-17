<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Conversationsqc extends WFF_Controller {

    /**
     * API restful [worldfonepbxmanager] collection.
     * READ from base_url + api/restful/cdr 
     * DETAIL from base_url + api/restful/cdr/$id 
     */

    // private $collection = "Chat";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        // $this->collection = set_sub_collection($this->collection);
    }

    function read()
    {
        try {

            
            $_db = $this->config->item("_mongo_db");
            $this->mongo_db->switch_db($_db);
            $ConfigType = $this->mongo_db->where(array("type" => '2'))->select(["call_init_point", "conversation_init_point"])->getOne("ConfigType");
            // var_dump();
            // var_dump($_db);
            $this->mongo_db->switch_db('worldfone4xs');
            $request = json_decode($this->input->get("q"), TRUE);
            // var_dump($request);
            $response = $this->crud->read('chatGroups', $request);
            foreach ($response['data'] as $key => $value) {
                if ($value['source'] == 'messenger') {
                    $response['data'][$key]['source'] = 'Facebook';
                }else{
                    $response['data'][$key]['source'] = ucfirst($value['source']);
                }
                $response['data'][$key]['trigger'] = ucfirst($value['trigger']);
                $user_info = $this->mongo_db->where(array('people_id' => $value['to']->user_id ))->getOne('people');
                
                $page_info = $this->mongo_db->where(array('id'  => $value['page_id']))->getOne('pageapps');
                if ($page_info) {
                    $response['data'][$key]['page_name'] = $page_info['name'];
                }else{
                    $response['data'][$key]['page_name'] = '';
                }

                // var_dump($value);
                $response['data'][$key]['qr_status'] = isset($value['qcdata']) && !empty($value['qcdata']) ? "Đã đánh giá" : "Chưa đánh giá";
                $response['data'][$key]['updatedAt'] = isset($value['updatedAt']) ? date("d/m/Y H:i:s", $value['updatedAt']) : '';
                // $response['data'][$key]['updatedBy'] = isset($value['updatedBy']) ? $value['updatedBy'] : '';
                // $response['data'][$key]['qcnote'] = isset($value['qcnote']) ? $value['qcnote'] : '';
                $point = 0;
                if (isset($value['qcdata'])) {
                    foreach ($value['qcdata'] as $qcdata) {
                        $point += $qcdata->point;
                    }
                }
                
                $response['data'][$key]['endPoint'] =$ConfigType['conversation_init_point']+$point;//$ConfigType['conversation_init_point'].' '.$point;// /*$ConfigType['conversation_init_point']-*/$point;//$ConfigType['conversation_init_point'];

                if (isset($user_info['customer_4x_id']) && !empty($user_info['customer_4x_id'])) {
                    $customer_info = $this->mongo_db->getOne(set_sub_collection('Customer'));
                    if (!empty($customer_info)) {
                        // var_dump($customer_info);
                        $response['data'][$key]['group_name'] = $customer_info['CUSTOMER_NAME'];
                        $response['data'][$key]['BRANCH_CODE'] = $customer_info['BRANCH_CODE'];
                        $response['data'][$key]['MOBILE_NO'] = $customer_info['MOBILE_NO'];
                    }
                }
            }
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function detail($id)
    {
        try {
            $response = $this->crud->where_id($id)->getOne('chatGroups');

            // $response['qcdata'] = array(); 
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function update($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["updatedBy"]  =   $this->session->userdata("extension");
            $result = $this->crud->where_id($id)->update('chatGroups', array('$set' => $data));
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}