<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Post extends WFF_Controller {

    private $collection = "Post";
    private $noti_collection = "Notification";
    private $sub = "";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_private");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
        $this->noti_collection = $this->sub . $this->noti_collection;
    }

    function add()
    {
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $extension  =   $this->session->userdata("extension");
        $merge_arr = ["scope" => $data["scope"]];
        switch ($data["scope"]) {
            case 'global': default:
                $to = $this->mongo_private->distinct($this->sub . "User", "extension");
                break;

            case 'group':
                $group = $this->mongo_db->where_id($data["group_id"])->getOne($this->sub . "Group");
                $to = $group["members"];
                $merge_arr["group_id"] = $data["group_id"];
                break;

            case 'private':
                $to = [$extension];
                break;
            
            case 'custom':
                $to = $data["to"];
                if(!in_array($extension, $to)) {
                    $to[] = $extension;
                }
                break;
        }
        $result = 0;
        if(!empty($data["isPost"]))
        {
            $result = $this->createPost($data["title"], $data["content"], $to, $merge_arr);
        }
        if(!empty($data["isNotification"]))
        {
            $post_id = !empty($result) ? $result["id"] : "";
            $result = $this->createNotification($data["title"], $data["content"], $to, $post_id);
        }
        
        echo json_encode(array("status" => $result ? 1 : 0));
    }

    private function createPost($title, $content, $to, $merge_arr)
    {
        $data = array(
            "title"     => $title,
            "active"    => true,
            "content"   => $content,
            "to"        => $to,
            "createdBy" => $this->session->userdata("extension")
        );
        $data = array_merge($data, $merge_arr);
        $result = $this->crud->create($this->collection, $data);
        return $result;
    }

    private function createNotification($title, $content, $to, $post_id = "")
    {
        $data = array(
            "title"     => $title,
            "active"    => true,
            "icon"      => "fa fa-file-text",
            "color"     => "text-info",
            "link"      => "tool/post",
            "content"   => $content,
            "to"        => $to,
            "createdBy" => $this->session->userdata("extension"),
            "notifyDate"=> $this->mongo_db->date(),
            "post_id"   => $post_id
        );
        $result = $this->crud->create($this->noti_collection, $data);
        return $result;
    }

    function read()
    {
        $request = json_decode($this->input->get("q"), TRUE);
        $extension = $this->session->userdata("extension");
        $where = ['$or' => [["to" => $extension], ["scope" => "global"]]];

        $response = $this->crud->read($this->collection, $request, [], $where);

        foreach ($response["data"] as &$doc) {
            $user = $this->mongo_private->where(["extension" => $doc["createdBy"]])->getOne($this->sub . "User");
            $doc["avatar"] = isset($user["avatar"]) ? $user["avatar"] : "";
            $doc["agentname"] = isset($user["agentname"]) ? $user["agentname"] : "";
            $doc["commentsCount"] = $this->mongo_db->where("post_id", $doc["id"])->count($this->sub . "Comment");
            if($doc["scope"] == "group" && !empty($doc["group_id"])) {
                $group = $this->mongo_db->where_id($doc["group_id"])->getOne($this->sub . "Group");
                $doc["group_name"] = isset($group["name"]) ? $group["name"] : "";
            }
        }
        echo json_encode($response);
    }

    function readNotification()
    {
        $request = json_decode($this->input->get("q"), TRUE);
        $extension = $this->session->userdata("extension");
        $where = ["to" => $extension];

        if( !empty($request["unread"]) ) {
            $where["read.extension"] = ['$ne' => $extension];
        }

        $response = $this->crud->read($this->noti_collection, $request, [], $where);
        foreach ($response["data"] as &$doc) {
            $doc["unread"] = TRUE;
            if(!empty($doc["read"])) {
                foreach ($doc["read"] as $e) {
                    if($e["extension"] == $extension) {
                        $doc["unread"] = FALSE;
                    }
                }
            }
        }
        echo json_encode($response);
    }

    function addComment()
    {
        $data = json_decode(file_get_contents('php://input'), TRUE);
        $data["createdBy"]  =   $this->session->userdata("extension");
        $result = $this->crud->create($this->sub . "Comment", $data);
        echo json_encode(["status" => $result ? 1 : 0]);
    }

    function like($post_id = "")
    {
        $extension  =   $this->session->userdata("extension");
        $post = $this->mongo_db->where_id($post_id)->getOne($this->collection);
        if(!empty($post["likes"]) && in_array($extension, $post["likes"])) {
            $likes = $post["likes"];
            $likes = array_values(array_diff($likes, [$extension]));
            $result = $this->mongo_db->where_id($post_id)->set("likes", $likes)->inc("likesCount", -1)->update($this->collection);
        } else {
            $result = $this->mongo_db->where_id($post_id)->push("likes", $extension)->inc("likesCount", 1)->update($this->collection);
        }
        echo json_encode(["status" => $result ? 1 : 0, "data" => $result]);
    }

    function readComments($post_id = "")
    {
        $request = json_decode($this->input->get("q"), TRUE);
        $extension = $this->session->userdata("extension");
        $where = ["post_id" => $post_id];
        $response = $this->crud->read($this->sub . "Comment", $request, [], $where);

        foreach ($response["data"] as &$doc) {
            if(!empty($doc["createdBy"])) {
                $user = $this->mongo_private->where(["extension"=>$extension])->getOne($this->sub . "User");
                $doc["agentname"] = isset($user["agentname"]) ? $user["agentname"] : "";
                $doc["avatar"] = isset($user["avatar"]) ? $user["avatar"] : "";
            }
        }
        echo json_encode($response);
    }
}