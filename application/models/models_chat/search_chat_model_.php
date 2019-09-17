<?php

class search_chat_model extends CI_Model {
    private $collection = "Customer";
    function __construct() {
        parent::__construct();
        $this->load->library('mongo_db4x');
        $this->load->library('mongodatasourceresult');
        $this->collection = set_sub_collection($this->collection);
    }

    function getCustomerField() {
//        $data = array();
//        try {
//            $data = $this->mongo_db4x->where(array('$or' => array(array('gridDisplay' => true), array('gridDisplay' => 1))))->order_by(array('position' => 1))->get(getCT()."customerFields");
//        } catch (Exception $e) {
//            $this->mongo_db4x->insert('logError', array('extension_action' => $extension, 'time_action' => time(), 'function_type' => 'model', 'url' => 'models/models_chat/search_chat_model', 'function_name' => 'getCustomerField', 'error_mess' => $e->getMessage(), 'lines' => $e->getLine()));
//        }
        $data = $this->mongo_db4x->where(array('$or' => array(array('gridDisplay' => true), array('gridDisplay' => 1))))->order_by(array('position' => 1))->get(set_sub_collection("customerFields"));
        return $data;
    }

    function searchCus($chatGroupId, $listField, $request) {
        $this->load->library('crud');

        $chatGroupInfo = $this->getChatGroupInfo($chatGroupId);
        $people_id = ($chatGroupInfo['from']['type'] === 'customer') ? $chatGroupInfo['from']['user_id'] : $chatGroupInfo['to']['user_id'];
        $peopleInfo = $this->search_model->getPeopleInfo($people_id);
        $mappingInfo = array(
            'type'      => $chatGroupInfo['type'],
            'people_id'        => $people_id,
            'name'      => (!empty($peopleInfo)) ? $peopleInfo['name'] : '',
            'source'    => $chatGroupInfo['source']
        );

        $data = $this->crud->read($this->collection, $request);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['mapping'] = 0;
            if (isset($value['socials'])) {
                foreach ($value['socials'] as $social) {
                    $people_id = isset($social->people_id) ? $social->people_id : '';
                    if ($mappingInfo['people_id'] == $people_id) {
                        $data['data'][$key]['mapping'] = 1;
                    }else{
                        $data['data'][$key]['mapping'] = 0;
                    }
                }
            }
        }
        return $data;
    }
    function getCustomer4xInfo($_id) {
        return $this->mongo_db4x->where(array('_id' => new mongoId($_id)))->getOne(set_sub_collection("Customer"));
    }

    function getChatGroupInfo($_id) {
        return $this->mongo_db4x->where(array('_id' => new mongoId($_id)))->getOne('chatGroups');
    }

    function getPeopleInfo($_id) {
        return $this->mongo_db4x->where(array('people_id' => $_id))->getOne('people');
   }

    function mappingProfileChatTo4x($_cusId4x, $mappingInfo) {

        return $this->mongo_db4x->where(array('_id' => new mongoId($_cusId4x)))->push('socials', $mappingInfo)->update(set_sub_collection('Customer'));
    }

    function mappingProfile4xToChat($people_id, $page_id, $customer_4x_id) {
        return $this->mongo_db4x->where(array('people_id' => $people_id, 'page_id' => $page_id))->set(array('customer_4x_id' => $customer_4x_id))->update('people');
    }
}
