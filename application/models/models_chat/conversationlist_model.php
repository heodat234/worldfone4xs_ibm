<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of people model
 *
 * @author Le Thi Ngoc Oanh
 */

class conversationlist_model extends WFF_Model{
    public function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
        $this->collection = "chatGroups";
    }

    function getReceiverPhoneById($toId) {
        $data = $this->mongo_db->where(array('_id' => new mongoId($toId)))->select(array('phone'))->getOne('people');
        return $data;
    }

    function getPeopleByPhone($phone) {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        $data = $this->mongo_db->where(array('phone' => new MongoRegex("/" . $phone . "/i")))->select(array('phone', 'people_id'))->get('people');
        return $data;
    }

    function getGoupChatByToUserid($userId) {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        $data = $this->mongo_db->where(array('to.user_id' => $userId))->select(array('trigger','source','from.name','phone', 'to.user_id', 'to.name','date_added'))->order_by(array('date_added' => 'desc'))->get('chatGroups');
        return $data;
    }
}

