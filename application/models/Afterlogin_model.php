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
        $this->set_group_session(); // Added 07/12/2019
    }

    function update_user()
    {
        $collection = "User";
        $collection = getCT($collection);
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
        // Update 01/11/2019, Set role name
        $extension = $this->session->userdata("extension");
        $user = $this->mongo_db->where(array("extension"=>$extension))
            ->select(["role_name"])->getOne($collection);
        $this->session->set_userdata("role_name", isset($user["role_name"]) ? $user["role_name"] : "");
        //
        // Update 16/12/2019, Set default role
        if( !$this->session->userdata("role_name") ) {
            $default_role = $this->mongo_db->where("default", TRUE)->getOne(getCT("Role"));
            if($default_role) {
                $role_name = isset($default_role["name"]) ? $default_role["name"] : "";
                $this->mongo_db->where("extension", $extension)
                ->set("role_id",new MongoDB\BSON\ObjectId($default_role["id"]))
                ->set("role_name",$role_name)->update($collection);
                $this->session->set_userdata("role_name", $role_name);
            }
        }
        //
        $this->mongo_db->switch_db();
    }

    function update_group()
    {
        $collection = "Group";
        $collection = getCT($collection);
        $this->load->model("pbx_model");
        $result = $this->pbx_model->list_queues();
        if(!empty($result["data"])) {
            $queues = $result["data"];
            $queueArr = [];
            foreach ($queues as $queue) {
                $queuename = $queue["queuename"];
                $queueArr[] = $queuename;
                if($this->mongo_db->where(array("queuename" => $queuename))->getOne($collection)) {
                    // Update
                    $item = array(
                        "queuename" => $queuename,
                        "type"      => "queue",
                        "queues"    => [$queuename]
                    );
                    if( is_array($queue["members"]) ) {
                        $item["members"] = array_map(function($element){
                            return $element["extension"];
                        }, $queue["members"]);
                    }
                    $this->mongo_db->where(array("queuename" => $queuename))->update($collection, array('$set' => $item));
                } else {
                    // Insert
                    $item = array(
                        "name"      => "Queue ".$queuename,
                        "queuename" => $queuename,
                        "type"      => "queue",
                        "queues"    => [$queuename],
                        "active"    => FALSE
                    );
                    if( is_array($queue["members"]) ) {
                        $item["members"] = array_map(function($element){
                            return $element["extension"];
                        }, $queue["members"]);
                    }
                    $this->mongo_db->insert($collection, $item);
                }
            }
            // Update 19/12/2019. Change queue not exists to custom group
            $this->mongo_db->where("queuename", ['$nin' => $queueArr])->set("type","custom")->update_all($collection);
        }
    }

    function set_group_session()
    {
        if( !$this->session->userdata("group_name") ) {
            $this->mongo_db->switch_db();
            $extension = $this->session->userdata("extension");
            $group = $this->mongo_db->where(['$or' => [
                ["lead" => $extension],
                ["members" => $extension]
            ], "type" => "custom"])->getOne( getCT("Group") );

            if( $group && isset($group["name"]) ) {
                $this->load->library("mongo_private");
                $this->mongo_private->where(["extension"=>$extension])
                ->update(getCT("User"), ['$set'=>["group_id"=>$group["id"],"group_name"=>$group["name"]]]);
                $this->session->set_userdata("group_id", $group["id"]);
                $this->session->set_userdata("group_name", $group["name"]);
            }
        }
    }
}