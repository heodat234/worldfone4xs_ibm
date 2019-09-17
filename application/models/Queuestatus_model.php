<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Queuestatus_model extends CI_Model {

    private $WFF;
    private $collection = "Queue_status";

    function __construct() {
        parent::__construct();
        $this->WFF =& get_instance();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->collection = set_sub_collection($this->collection);
    }

    function run() {
        $status_queues      = $this->status_queues();
        foreach ($status_queues as $status_queue) {
            $queuename = $status_queue["name"];
            $last_status_queue = $this->getOne($queuename);
            $check_flag = $last_status_queue ? FALSE : TRUE;
            if(!$check_flag) {
                foreach (["type", "paused"] as $field) {
                    if($status_queue[$field] != $last_status_queue[$field]) {
                        $check_flag = TRUE;
                        break;
                    }
                }
            }
            if($check_flag) {
                if(isset($field)) $this->end($queuename, array("endnote" => "{$field} change"));
                $this->update_previous($queuename);
                $this->start($queuename, $status_queue);
            } else $this->update($queuename);
        }
        
    }

    function getOne($queuename) {
        $where = array(
            "name"              =>  $queuename,
            "lastpingtime"      =>  array('$gt' => time() - $this->config->item("sess_time_to_update")),
            "endtime"           =>  0
        );
        $data = $this->mongo_db->where($where)
            ->order_by(array('lastpingtime' => -1))
            ->getOne($this->collection);
        return $data;
    }

    function start($queuename, $data = array()) {
        $extension     = $this->session->userdata("extension");
        $my_session_id = $this->session->userdata("my_session_id");
        $time = time();
        $default_data = array(
            "starttime"                 =>  $time,
            "endtime"                   =>  0,
            "lastpingtime"              =>  $time,
            "extensions"                =>  [ $extension ],
            "my_session_ids"            =>  [ $my_session_id ]
        );
        $insert_data = array_merge($default_data, $data);
        
        $this->mongo_db->insert($this->collection, $insert_data);
    }

    function end($queuename, $data = array()) {
        $time = time();
        $my_session_id = $this->session->userdata("my_session_id");
        $where = array("name" => $queuename, "my_session_ids" => $my_session_id, 'endtime'=> 0);
        $update_data = array_merge(array('endtime'=> $time), $data);
        $this->mongo_db->where($where)
                ->set($update_data)
                ->update_all($this->collection);
    }

    function update($queuename, $data = array()) {
        $time           = time();
        $extension      = $this->session->userdata("extension");
        $my_session_id  = $this->session->userdata("my_session_id");

        $default_data = array(
            "lastpingtime" => $time
        );
        $update_data = array_merge($default_data, $data);
        $where = array("name" => $queuename, "endtime" => 0);
        $this->mongo_db->where($where)
                ->set($update_data)
                ->addtoset("my_session_ids", $my_session_id)
                ->addtoset("extensions", $extension)
                ->update($this->collection);
    }

    private function update_previous($queuename)
    {
        $this->load->config("_mongo");
        $time = time();
        // Truong hop user khong logout, tat trinh duyet
        $data = $this->mongo_db->where(array("name" => $queuename, "endtime" => 0))
        ->select(["lastpingtime"])
        ->get($this->collection);
        foreach ($data as $doc) {
            $this->mongo_db->where(array("_id" => new MongoDB\BSON\ObjectId($doc["id"])))
            ->set(array("endtime" => $doc["lastpingtime"]))
            ->update($this->collection);
        }
    }

    function status_queues() {
        $this->WFF->load->model("agentstatus_model");
        $result = $this->WFF->agentstatus_model->getOne(["agentstate"]);
        $status_queues = array();
        if(!empty($result["agentstate"]))
        {
            $agent_state = json_decode(json_encode($result["agentstate"]), TRUE);
            if($agent_state["queues"]["queue"]) {
                $queues = $agent_state["queues"]["queue"];
                foreach ($queues as $queue) {
                    $status_queue = array(
                        "name"              => $queue["queuename"],
                        "type"              => $queue["queuemembership"],
                        "paused"            => (bool) (int) $queue["queuememberpaused"]
                    );
                    $status_queues[] = $status_queue;
                }
            }
        }
        return $status_queues;
    }
}