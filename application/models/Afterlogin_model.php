<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Afterlogin_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    function run() 
    {
        $this->update_user();
        $this->update_group();
    }

    function update_user()
    {
        $collection = "User";
        $collection = set_sub_collection($collection);
        $this->mongo_db->switch_db($this->config->item("_mongo_db"));
        $this->load->model("pbx_model");
        $result = $this->pbx_model->list_agent(0, 0, 0);
        if(!empty($result["data"])) {
            $users = $result["data"];
            $extensions = array();
            foreach ($users as $user) {
                if(!isset($user["extension"])) continue;

                $extension = $user["extension"];
                $extensions[] = $extension;
                foreach (["issupervisor", "isadmin"] as $bool_field) {
                    $user[$bool_field] = (bool) (int) $user[$bool_field];
                }
                $user["active"] = TRUE;

                $this->mongo_db->where(array("extension" => $extension))->update($collection, array('$set' => $user), array("upsert" => TRUE));
            }
            $this->mongo_db->where(array("extension" => array('$nin' => $extensions), "active" => TRUE))
            ->update_all($collection, array('$set' => array("active" => FALSE)), []);
        }
        // Update 01/11/2019
        $extension = $this->session->userdata("extension");
        $user = $this->mongo_db->where(array("extension"=>$extension))
            ->select(["role_name"])->getOne($collection);
        $this->session->set_userdata("role_name", isset($user["role_name"]) ? $user["role_name"] : "");
        //
        $this->mongo_db->switch_db();
    }

    function update_group()
    {
        $collection = "Group";
        $collection = set_sub_collection($collection);
        $this->load->model("pbx_model");
        $result = $this->pbx_model->list_queues();
        if(!empty($result["data"])) {
            $queues = $result["data"];
            foreach ($queues as $queue) {
                $queuename = $queue["queuename"];
                if($this->mongo_db->where(array("queuename" => $queuename))->getOne($collection)) {
                    // Update
                    $item = array(
                        "queuename" => $queuename,
                        "type"      => "queue",
                        "queues"    => [$queuename],
                        "members"   => array_map(function($element){
                            return $element["extension"];
                        }, $queue["members"])
                    );
                    $this->mongo_db->where(array("queuename" => $queuename))->update($collection, array('$set' => $item));

                } else {
                    // Insert
                    $item = array(
                        "name"      => "Queue ".$queuename,
                        "queuename" => $queuename,
                        "type"      => "queue",
                        "queues"    => [$queuename],
                        "active"    => FALSE,
                        "members"   => array_map(function($element){
                            return $element["extension"];
                        }, $queue["members"])
                    );
                    $this->mongo_db->where(array("queuename" => $queuename))->insert($collection, $item);
                }
            }
        }
    }
}