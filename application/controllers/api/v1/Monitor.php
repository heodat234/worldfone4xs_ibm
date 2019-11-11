<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Monitor extends CI_Controller {

    private $collection = "worldfonepbxmanager";
    private $sub = "";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("mongo_db");
        $this->sub = set_sub_collection("");
        $this->collection = $this->sub . $this->collection;
    }

    function users()
    {
        $request = json_decode($this->input->get("q"), TRUE);
        $this->load->library("crud");
        $this->load->model("agentstatus_model");
        $this->load->model("agentsign_model");
        $this->load->model("call_model");
        $this->crud->select_db($this->config->item("_mongo_db"));
        $result = $this->crud->read("{$this->sub}User", $request, ["extension", "agentname", "favorite", "avatar"],  array("active" => TRUE));
        $this->crud->select_db();
        foreach ($result["data"] as $index => &$doc) {
            $doc["status"] = $this->agentstatus_model->get_today_by_extension($doc["extension"]);
            $doc["totalCurrentUser"] = $this->agentsign_model->count_current_by_extension($doc["extension"]);
            $doc["totalCallIn"] = $this->call_model->get_total_today_by_extension($doc["extension"], array("direction" => "inbound"));
            $doc["totalCallOut"] = $this->call_model->get_total_today_by_extension($doc["extension"], array("direction" => "outbound"));
        }
        $result["time"] = time();
        echo json_encode($result);
    }

    function callin()
    {
        $pipeline = array(
            array(
                '$match' => array(
                    '$and' => array(
                            array('direction' => 'inbound'),
                            array('starttime' => array('$gte'=>strtotime("today"))),
                            array('dnis' => array('$exists'=>true))
                        )
                    )
            ),
            array(
                '$group' => array(
                    '_id' => '$dnis',
                    'waiting' => array('$sum' =>
                        array(
                            '$cond' => array(
                                array('$eq' => array('$workstatus', 'New')),
                                1,
                                0)
                        )
                    ),
                    'talking' => array('$sum' =>
                        array(
                            '$cond' => array(
                                array('$eq' => array('$workstatus', 'On-Call')),
                                1,
                                0)
                        )
                    ),
                    'totalofferedcall' => array('$sum' => 1),
                    'totalabandonedcall' => array('$sum' =>
                        array(
                            '$cond' => array(
                                array('$or' => array(
                                    array('$eq' => array('$disposition', 'NO ANSWER')),
                                    array('$eq' => array('$disposition', 'BUSY'))
                            )),
                            1,
                            0)
                        )
                    )
                )
            ),
            array(
                '$project' => array(
                    'did' => '$_id',
                    'waiting' => '$waiting',
                    'talking' => '$talking',
                    'totalofferedcall' => '$totalofferedcall',
                    'totalabandonedcall' => '$totalabandonedcall',
                    '_id' => 0
                )
            )
        );
        $result = $this->mongo_db->aggregate_pipeline($this->collection, $pipeline);
        echo json_encode(array("data" => $result));
    }

    function callout()
    {
        $pipeline = array(
            array(
                '$match' => array(
                    '$and' => array(
                            array('direction' => 'outbound'),
                            array('starttime' => array('$gte'=>strtotime("today"))),
                            array('dnis' => array('$exists'=>true))
                        )
                    )
            ),
            array(
                '$group' => array(
                    '_id' => '$dnis',
                    'waiting' => array('$sum' =>
                        array(
                            '$cond' => array(
                                array('$eq' => array('$workstatus', 'Ring')),
                                1,
                                0)
                        )
                    ),
                    'talking' => array('$sum' =>
                        array(
                            '$cond' => array(
                                array('$eq' => array('$workstatus', 'On-Call')),
                                1,
                                0)
                        )
                    ),
                    'totalofferedcall' => array('$sum' => 1),
                    'totalabandonedcall' => array('$sum' =>
                        array(
                            '$cond' => array(
                                array('$or' => array(
                                    array('$eq' => array('$disposition', 'NO ANSWER')),
                                    array('$eq' => array('$disposition', 'BUSY'))
                            )),
                            1,
                            0)
                        )
                    )
                )
            ),
            array(
                '$project' => array(
                    'did' => '$_id',
                    'waiting' => '$waiting',
                    'talking' => '$talking',
                    'totalofferedcall' => '$totalofferedcall',
                    'totalabandonedcall' => '$totalabandonedcall',
                    '_id' => 0
                )
            )
        );
        $result = $this->mongo_db->aggregate_pipeline($this->collection, $pipeline);
        echo json_encode(array("data" => $result));
    }

    function abandonedcall() {
        $request = json_decode($this->input->get("q"), TRUE);
        $match = array(
            'starttime' => array('$gte'=>strtotime("today")), 
            'dnis' => array('$exists'=>true),
            'disposition' => array('$in' => ["NO ANSWER", "BUSY"])
        );
        $this->load->library("crud");
        $response = $this->crud->read($this->collection, $request, array("customernumber", "userextension", "billduration", "totalduration", "glide_extension", "direction"), $match);
        echo json_encode($response);
    }

    public function change_status_extension() {
        $request = json_decode(file_get_contents('php://input'), TRUE);
        $this->load->model("language_model");
        try {
            if(empty($request["extension"])) throw new Exception("@Undefined extension@");
            $extension = $request["extension"];
            $this->load->model("agentstatus_model");
            $result = $this->agentstatus_model->change_from_other($extension, $request);
            if(!$result) throw new Exception("@Change not success@");
            $current_status = $this->agentstatus_model->getOne(["statuscode"]);
            $message = !empty($current_status["status"]) ? $this->language_model->translate("{$extension} @Change to status@ @".$current_status["status"]["text"]."@", "NOTIFICATION") : "";
            echo json_encode(array("status" => 1, "message" => $message));          
        } catch (Exception $e) {
            echo json_encode(array('status' => 0, "message" => $e->getMessage()));
        }
    }

    function users_with_chat()
    {
        $request = json_decode($this->input->get("q"), TRUE);
        $this->load->library("crud");
        $this->load->model("agentstatus_model");
        $this->load->model("agentsign_model");
        $this->load->model("call_model");
        $this->load->model("chatstatus_model");
        $this->crud->select_db($this->config->item("_mongo_db"));
        $result = $this->crud->read("{$this->sub}User", $request, ["extension", "agentname", "favorite", "avatar"],  array("active" => TRUE));
        $this->crud->select_db();
        foreach ($result["data"] as $index => &$doc) {
            $doc["status"] = $this->agentstatus_model->get_today_by_extension($doc["extension"]);
            $doc["totalCurrentUser"] = $this->agentsign_model->count_current_by_extension($doc["extension"]);
            $doc["totalCallIn"] = $this->call_model->get_total_today_by_extension($doc["extension"], array("direction" => "inbound"));
            $doc["totalCallOut"] = $this->call_model->get_total_today_by_extension($doc["extension"], array("direction" => "outbound"));

            $doc["chat_status"] = $this->chatstatus_model->get_today_by_extension($doc["extension"]);
        }
        $result["time"] = time();
        echo json_encode($result);
    }

    function change_chat_status_extension()
    {
        $request = json_decode(file_get_contents('php://input'), TRUE);
        $this->load->model("language_model");
        try {
            if(empty($request["extension"])) throw new Exception("@Undefined extension@");
            $extension = $request["extension"];
            $this->load->model("chatstatus_model");
            $result = $this->chatstatus_model->change_from_other($extension, $request);
            if(!$result) throw new Exception("@Change not success@");
            $current_status = $this->chatstatus_model->getOne(["statuscode"]);
            $message = !empty($current_status["status"]) ? $this->language_model->translate("{$extension} @Change to status@ @".$current_status["status"]["text"]."@", "NOTIFICATION") : "";
            echo json_encode(array("status" => 1, "message" => $message));          
        } catch (Exception $e) {
            echo json_encode(array('status' => 0, "message" => $e->getMessage()));
        }
    }

    function readActivity()
    {
        try {
            $this->load->library("crud");
            $_db = $this->config->item("_mongo_db");
            $this->crud->select_db($_db);
            $this->load->model("language_model");
            $this->load->library("mongo_private");
            $extensions = $this->mongo_private->where(
                array("issysadmin" => FALSE)
            )->distinct($this->sub . "User", "extension");
            $match = array("extension" => array('$in' => $extensions));
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read("Activity", $request, ["extension","agentname","directory","class","function","method", "uri", "ajaxs_elapsed_time", "createdAt"], $match);
            foreach ($response["data"] as &$doc) {
                $where = array();
                foreach (["directory","class","function","method"] as $field) {
                    $where[$field] = $doc[ $field ];
                }
                $definitionDoc = $this->mongo_db->where($where)->getOne("Activity_definition");
                if($definitionDoc) 
                {
                    $doc["definition"] = $definitionDoc["definition"];
                } 
                else 
                {
                    $navDoc = $this->mongo_db->where(array(
                        "uri" => array('$in' => [$doc["uri"], $doc["uri"]."/"]), 
                        "visible" => TRUE
                    ))->getOne($this->sub . "Navigator");
                    if(!empty($navDoc["icon"])) $doc["icon"] = $navDoc["icon"];
                    $doc["definition"] =  "@Access@ @page@ " . ($navDoc ? $navDoc["name"] : $doc["uri"]);
                }
            }
            $response = $this->language_model->translate($response,  "SIDEBAR");
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function readActivityAjax()
    {
        try {
            $id = $this->input->get("id");

            $this->load->library("crud");
            $_db = $this->config->item("_mongo_db");
            $this->crud->select_db($_db);
            $this->load->model("language_model");
            $activityDoc = $this->mongo_db->where_id($id)->select(["ajaxs.directory","ajaxs.class","ajaxs.function","ajaxs.method","ajaxs.uri","ajaxs.createdAt"])->getOne("Activity_log");
            $data = !empty($activityDoc["ajaxs"]) ? $activityDoc["ajaxs"] : [];
            $responseData = array();
            foreach ($data as &$ajax) {
                $ajax = (array) $ajax;
                $where = array();
                if($ajax["directory"] == "template") 
                {
                    $where["uri"] = $ajax["uri"];
                } 
                else 
                {
                    foreach (["directory","class","function","method"] as $field) {
                        $where[$field] = $ajax[ $field ];
                    }
                }
                $definitionDoc = $this->mongo_db->where($where)->getOne("Activity_definition");
                
                if($definitionDoc) {
                    $ajax["definition"] = $definitionDoc["definition"];
                    $responseData[] = $ajax;
                }
            }
            $responseData = $this->language_model->translate($responseData);
            $response = array("data" => $responseData, "total" => count($responseData));
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}