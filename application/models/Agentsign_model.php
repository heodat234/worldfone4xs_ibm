<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Agentsign_model extends CI_Model {

    private $collection = "Agent_sign";
    private $unique_login = TRUE;
    private $login_logout_ipphone = TRUE;
    private $record_activity = TRUE;

    function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->unique_login = $this->config->item("wff_unique_login");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;

        $this->login_logout_ipphone = $this->config->item("login_logout_ipphone");
        $this->record_activity = $this->config->item("record_activity");
    }

    function getOne($select = array(), $unselect = array()) {
        $my_session_id = $this->session->userdata("my_session_id");
        $data = $this->mongo_db->where(array("my_session_id" => $my_session_id, "signouttime" => 0))
            ->select($select, $unselect)
            ->order_by(array('signintime' => -1))
            ->getOne($this->collection);
        return $data;
    }

    function start($data = array()) {
        $this->update_previous();
        $time = time();
        $default_data = array(
            "signintime"            =>  $time,
            "signouttime"           =>  0,
            "lastpingtime"          =>  $time,
            "ipaddress"             =>  $this->input->ip_address()
        );
        $insert_data = array_merge($default_data, $data);

        // IPPHONE
        if($this->login_logout_ipphone) {
            $this->load->model("ipphone_model");
            $result = $this->ipphone_model->login($data["extension"]);
            $insert_data["login_ipphone"] = $result;
        }
        // RECORD ACTIVITY
        if($this->record_activity) {
            $this->recording_activity();
        }
        
        $this->mongo_db->insert($this->collection, $insert_data);        
    }

    function end($data = array()) {
        $time = time();
        $my_session_id  =   $this->session->userdata("my_session_id");

        $default_data = array(
            "signouttime" => $time,
            "endnote"        => "User signout"
        );
        $update_data = array_merge($default_data, $data);

        // IPPHONE
        if($this->login_logout_ipphone) {
            $data = $this->mongo_db->where(array('my_session_id' => $my_session_id))->getOne($this->collection);
            $this->load->model("ipphone_model");
            $result = $this->ipphone_model->logout($data["extension"]);
            $update_data["logout_ipphone"] = $result;
        }
        // RECORD ACTIVITY
        if($this->record_activity) {
            $this->recording_activity();
        }

        $this->mongo_db->where(array('my_session_id' => $my_session_id))
                ->set($update_data)
                ->update($this->collection);
    }

    private function recording_activity() {
        $this->benchmark->mark('mark_end');
        $time = microtime(TRUE); 
        $uri_string = $this->uri->uri_string();
        $directory = rtrim($this->router->fetch_directory(), "/");
        $class = $this->router->fetch_class();
        $function = $this->router->fetch_method();
        $get = $this->input->get() ? $this->input->get() : null;
        $post = $this->input->post() ? $this->input->post() : null;
        $input = file_get_contents('php://input') ? file_get_contents('php://input') : null;
        $method = $this->input->method();

        $params = null;
        $my_session_id = $this->session->userdata("my_session_id");
        $extension = $this->session->userdata("extension");
        $elapsed_time = (double) $this->benchmark->elapsed_time("total_execution_time_start", "mark_end");
        $memory_usage = ($usage = memory_get_usage()) != '' ? $usage : 0;

        $data = array(
            "my_session_id" => $my_session_id,
            "extension"     => $extension,
            "agentname"     => $this->session->userdata("agentname"),
            "directory"     => $directory,
            "class"         => $class,
            "function"      => $function,
            "uri"           => $uri_string,
            "method"        => $method,
            "params"        => $params,
            "get_data"      => $get,
            "post_data"     => $post,
            "input"         => $input,
            "elapsed_time"  => $elapsed_time,
            "memory_usage"  => $memory_usage,
            "createdAt"     => $time,
        );
        $this->mongo_db->insert("Activity", $data);
    }

    function update($data = array()) {
        $time = time();

        $my_session_id = $this->session->userdata("my_session_id");
        $current_session_id = $this->session->session_id;
        
        $default_data = array(
            "lastpingtime" => $time
        );
        $update_data = array_merge($default_data, $data);
        $this->mongo_db->where(array('my_session_id' => $my_session_id, "signouttime" => 0))
                ->set($update_data)->addtoset("session_ids", $current_session_id)
                ->update($this->collection);
        // Update User Collection
        $this->load->library("mongo_private");
        $this->mongo_private->where(array("current_my_session_id" => $my_session_id))->update($this->sub . "User", array('$set' => $default_data));
    }

    // Check user khong logout, tat trinh duyet het han session
    private function update_previous()
    {
        $this->load->config("_mongo");
        $sess_expiration = $this->config->item("sess_expiration");
        $time = time();
        $data = $this->mongo_db->where(array("signouttime" => 0))
        ->select(["_id", "session_ids", "lastpingtime"])
        ->get($this->collection);
        foreach ($data as $doc) {
            if( $time > $doc["lastpingtime"] + $sess_expiration) 
            {
                // Khi session het han
                $this->mongo_db->where(array("_id" => new MongoDB\BSON\ObjectId($doc["id"])))
                ->set(array("signouttime" => $doc["lastpingtime"], "endnote" => "Session end"))
                ->update($this->collection); 
            }
            // Chuc nang kick user - chi mot user dang nhap mot luc
            if(isset($doc["session_ids"]) && $this->unique_login) {
                $this->load->library("mongo_private");
                foreach ($doc["session_ids"] as $session_id) {
                    $this->mongo_private->where(array("_id"=>$session_id))->update( $this->config->item("session_mongo_collection") , array('$unset' => array("data" => 1)));
                }
            }
        }
    }

    function count_current_by_extension($extension)
    {
        return $this->mongo_db
        ->where(array("extension" => $extension, "signouttime" => 0, "lastpingtime" => array('$gt' => time() - 10)))
        ->count($this->collection);
    }
}