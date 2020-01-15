<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Callevent_model extends CI_Model {

    private $sub = "";
    private $cdr = "worldfonepbxmanager";
    private $cdr_realtime = "worldfonepbxmanager_realtime";
    private $diallist_detail = "Diallist_detail";
    private $voicemails = "voicemails";
    private $misscall = "misscall";
    private $config_collection = "ConfigType";
    private $user_collection = "User";
    private $follow_up = "Follow_up";

    function __construct() {
        $this->load->library('mongo_db');
    }

    private function set_collection($sub) {
        $this->sub                  = "{$sub}";
        $this->cdr                  = "{$sub}_{$this->cdr}";
        $this->cdr_realtime         = "{$sub}_{$this->cdr_realtime}";
        $this->diallist_detail      = "{$sub}_{$this->diallist_detail}";
        $this->voicemails           = "{$sub}_{$this->voicemails}";
        $this->misscall             = "{$sub}_{$this->misscall}";
        $this->user_collection      = "{$sub}_{$this->user_collection}";
        $this->follow_up            = "{$sub}_{$this->follow_up}";
    }

    public function get_key($secret ) {
        $this->load->library("mongo_private");
        $config = $this->mongo_private->where(array('secret_key' => $secret))->getOne($this->config_collection);
        if(!empty($config["type"])) {
            $this->set_collection($config["type"]);
            $this->config_type = $config;
        }
        return $config;  
    }
    
    public function set_cdr($data) {
        $this->mongo_db->insert($this->cdr_realtime, $data);
        if(isset($data['_id'])){
            unset($data['_id']);
        }
        return $this->mongo_db->insert($this->cdr, $data);
    }
    public function set_misscall($data) {
        return $this->mongo_db->insert($this->misscall, $data);
    }
    public function set_agentName($calluuid, $agentname,$fullname) {
        return $this->mongo_db->where(array('calluuid' => $calluuid))->set(array("agentname"=>$agentname,"agentfullname"=>$fullname))->update($this->cdr);
    }
     public function set_agentGlide($calluuid, $userextension) {
        if($this->checkCalluuid2($calluuid)){
            return $this->mongo_db->where(array('calluuid' => $calluuid))->push("glide_extension",$userextension)->update($this->cdr);
        }else{
            return $this->mongo_db->where(array('calluuid' => $calluuid))->push("glide_extension",$userextension)->update($this->cdr);
        }
    }

    public function check_misscall($customer_number){
        if( !empty($this->config_type["auto_delete_misscall"]) ) {
            $this->mongo_db->where(array("customernumber"=>$customer_number))->delete_all($this->misscall);
        }
        if( !empty($this->config_type["auto_delete_followup"]) ) {
            $this->mongo_db->where(array("phone"=>$customer_number))->delete_all($this->follow_up);
        }
        
        return TRUE;       
    }
   
    public function update_cdr($data) {
        $this->mongo_db->where(array('calluuid' =>$data['calluuid']))->set($data)->update_all($this->cdr_realtime);
        return $this->mongo_db->where(array('calluuid' =>$data['calluuid']))->set($data)->update_all($this->cdr);
    }
    
    public function checkCalluuid($calluuid){
        $check = $this->mongo_db->where(array('calluuid' => $calluuid))->getOne($this->cdr);
        return $check ? true : false;
    }
    public function checkCalluuid2($calluuid){
        $check = $this->mongo_db->where(array('calluuid' => $calluuid,"glide_extension"=>null))->getOne($this->cdr);
        if(is_array($check)&&count($check)>0){
            return true;
        }
        return false;
    }
    
    public function checkCallOut($calluuid){
        $check = $this->mongo_db->where(array('calluuid' => $calluuid,'direction'=>'outbound' ))->getOne($this->cdr);
        if($check){
           return $check['userextension'];
        }
        return false;
    }
    public function checkCallInternal($calluuid){
        $check = $this->mongo_db->where(array('calluuid' => $calluuid,'internal'=>true ))->getOne($this->cdr);
        if($check){
           return true;
        }
        return false;
    }

    public function getAgentFullName($userextension){
        $this->load->library("mongo_private");
        $user = $this->mongo_private->where(array('extension' => $userextension))->getOne($this->user_collection);
        return isset($user['agentname']) ? $user['agentname'] : "";
    }
    
    public function delete_cdr($calluuid) {   
        $this->mongo_db->where(array('calluuid' => $calluuid))->delete_all($this->cdr_realtime);    
        return $this->mongo_db->where(array('calluuid' => $calluuid))->delete_all($this->cdr);
    }
    public function delete_cdr_realtime($calluuid) {
        $data = $this->mongo_db->where(array('calluuid' => $calluuid))->getOne($this->cdr_realtime);
        $this->mongo_db->where(array('calluuid' => $calluuid))->delete_all($this->cdr_realtime);
        
        // Set interactive
        $WFF =& get_instance();
        $WFF->load->model("Interactive_model");
        $WFF->Interactive_model->create("call", "",  $data, $this->config_type);

        // Update 07/12/2019 . Set group name and group id.
        $this->load->library("mongo_private");
        $user = $this->mongo_private->where(array('extension' => $data["userextension"]))->getOne($this->user_collection);

        if( isset($user["group_name"]) ) {
            $group_name = $user["group_name"];
            $group_id = isset($user["group_id"]) ? $user["group_id"] : "";
            $this->mongo_db->where(array('calluuid' => $calluuid))
                ->set("group_name", $group_name)->set("group_id", $group_id)->update($this->cdr);
        }
    }

    public function get_curentcall($calluuid) {
       return $this->mongo_db->where_not_in('calluuid', $calluuid)->where_ne('workstatus', 'Complete')->limit(100)->get($this->cdr);
    }
    public function get_curentcall1() {
       return $this->mongo_db->where_ne('workstatus', 'Complete')->limit(100)->get($this->cdr);
    }
    public function get_cdr($calluuid) {
        // Change 10/07/2019, Uu tien check realtime truoc
        $result =  $this->mongo_db->where(array('calluuid' => $calluuid))->get($this->cdr_realtime);
        return $result ? $result : $this->mongo_db->where(array('calluuid' => $calluuid))->get($this->cdr);
    }
    public function get_calllist() {
       
       return $this->mongo_db->where(array('customercode'=> null,'scheduled'=>null))->where_ne('connect_info',null)->limit(100)->get($this->cdr);
    }
    
    public function updateScheduled($calluuid) { 
        $this->mongo_db->where(array('calluuid'=>$calluuid))->set(array('scheduled'=>'done'))->update_all($this->cdr);
        
    }
    public function set_voicemail($data) {
        return $this->mongo_db->insert($this->voicemails, $data);
    }

    public function process_dialist($data) {
        if($this->sub == "LO") 
        {
            $diallist_detail_id = $data['dialid'];
            $fields = ["calluuid", "disposition", "userextension", "customernumber", "starttime", "causetxt","waittimeinqueue", "dialtype"];
            $callResult = array();
            foreach ($fields as $field) {
                if(in_array($field, ["starttime","waittimeinqueue"])) {
                    $callResult[$field] = isset($data[$field]) ? $data[$field] : 0;
                } else $callResult[$field] = isset($data[$field]) ? $data[$field] : "";
            }
            
            $update = array(
                '$inc' => array("tryCount" => 1), 
                '$push' => array("callResult" => $callResult)
            );
            // Assign
            if(isset($callResult["disposition"], $callResult["userextension"]) && $callResult["disposition"] == "ANSWERED") {
                $update['$set'] = ["assign" => $callResult["userextension"]];
            }
            // Update diallist detail
            $this->mongo_db->where_id($diallist_detail_id)->update($this->diallist_detail, $update);
            // Remove in process
            $this->handle_DialInProcess($data);
        }
    }

    public function handle_DialInProcess($data){
        // Xay ra khi make call 2, user ko bat may
        // Remove from process
        if(isset($data['dialQueueId'])) {
            $this->mongo_db->where(["dialQueueId" => $data['dialQueueId']])->delete_all("LO_Dial_in_process");
        }       
    }

    public function handle_PutCDRs($data){
        // Xay ra khi make call 3, KH ko bat may
        // Insert cdr
        $this->mongo_db->insert($this->cdr, $data);
        // Xu ly dial queue
        if(isset($data['dialQueueId'])) {
            $dialQueueCollection = "LO_Dial_queue";
            $dialQueue = $this->mongo_db->where_id($data['dialQueueId'])->getOne($dialQueueCollection);
            if(!$dialQueue) return;
            if(isset($dialQueue["diallistdetail_id"]) && isset($dialQueue["spin"]) && $dialQueue["spin"] == 1) {

                $diallistDetailId = $dialQueue["diallistdetail_id"];

                unset($dialQueue["id"], $dialQueue["called"], $dialQueue["calledAt"]);
                $dialQueue["spin"] = 2;
                $dialQueue["priority"] = 300;
                $dialQueue["createdAt"] = $this->mongo_db->date();
                $this->mongo_db->insert("LO_Dial_queue", $dialQueue);

                // GET Diallist Detail
                $diallistDetail = $this->mongo_db->where_id($diallistDetailId)->getOne("LO_Diallist_detail");
                // Update Action code
                $this->mongo_db->where_id($diallistDetailId)->set("action_code", "NOT")->update("LO_Diallist_detail");

                // House_NO

                if(!empty($diallistDetail["House_NO"]) && strlen($diallistDetail["House_NO"]) > 7) {
                    $dialQueue["phone"] = $diallistDetail["House_NO"];
                    $dialQueue["index"]++;
                    $this->mongo_db->insert($dialQueueCollection, $dialQueue);
                }

                // REFERENCE

                if(!empty($diallistDetail["LIC_NO"])) {
                    $REFS = $this->mongo_db->where("LIC_NO", $diallistDetail["LIC_NO"])->get( "LO_Relationship" );
                    foreach ($REFS as $doc) {
                        if(!empty($doc["phone"]) && strlen($doc["phone"]) > 7) {
                            $dialQueue["phone"] = $doc["phone"];
                            $dialQueue["index"]++;
                            $this->mongo_db->insert($dialQueueCollection, $dialQueue);
                        }
                    }
                }

                $this->mongo_db->where_id($dialQueue["diallistdetail_id"])->set("priority", $dialQueue["priority"])->update("LO_Diallist_detail");
            }
            if( isset($dialQueue["diallist_id"]) ) 
            {
                $diallist = $this->mongo_db->where_id($dialQueue["diallist_id"])->getOne("LO_Diallist");
                if(!isset($diallist["group_id"])) return;

                $group = $this->mongo_db->where_id($diallist["group_id"])->getOne("LO_Group");

                if(!isset($group["name"])) return;

                $this->mongo_db->where("calluuid", $data["calluuid"])->set("group_name", $group["name"])->update($this->cdr);
            }
        }       
    }
}
