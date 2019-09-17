<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Callevent_model extends CI_Model {

    private $cdr = "worldfonepbxmanager";
    private $cdr_realtime = "worldfonepbxmanager_realtime";
    private $diallist_detail = "Diallist_detail";
    private $voicemails = "voicemails";
    private $misscall = "misscall";
    private $config_collection = "ConfigType";
    private $user_collection = "User";

    function __construct() {
        $this->load->library('mongo_db');
    }

    private function set_collection($sub) {
        $this->cdr              = "{$sub}_{$this->cdr}";
        $this->cdr_realtime     = "{$sub}_{$this->cdr_realtime}";
        $this->diallist_detail  = "{$sub}_{$this->diallist_detail}";
        $this->voicemails       = "{$sub}_{$this->voicemails}";
        $this->misscall         = "{$sub}_{$this->misscall}";
        $this->user_collection  = "{$sub}_{$this->user_collection}";
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
    public function unset_misscall($calluuid) {       
        return $this->mongo_db->where(array('calluuid' => $calluuid))->delete($this->misscall);
        
    }
    public function check_misscall($customer_number){
        return $this->mongo_db->where(array("customernumber"=>$customer_number))->delete_all($this->misscall);       
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
        $this->mongo_db->where(array('calluuid' => $calluuid))->delete($this->cdr_realtime);    
        return $this->mongo_db->where(array('calluuid' => $calluuid))->delete($this->cdr);
    }
    public function delete_cdr_realtime($calluuid) {
        $data = $this->mongo_db->where(array('calluuid' => $calluuid))->getOne($this->cdr_realtime);
        $this->mongo_db->where(array('calluuid' => $calluuid))->delete_all($this->cdr_realtime);
        
        // Set interactive
        $WFF =& get_instance();
        $WFF->load->model("Interactive_model");
        $WFF->Interactive_model->create("call", "",  $data, $this->config_type);
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

    public function updateCusname($customerid, $customername,$calluuid) { 
        echo"updateCusname".$customerid;
        $this->mongo_db->where(array('calluuid'=>$calluuid))->set(array('customername' => $customername,'customercode'=>$customerid,'scheduled'=>'done'))->update_all($this->cdr);
        
    }
    
    public function updateScheduled($calluuid) { 
        $this->mongo_db->where(array('calluuid'=>$calluuid))->set(array('scheduled'=>'done'))->update_all($this->cdr);
        
    }
    public function set_voicemail($data) {
        return $this->mongo_db->insert($this->voicemails, $data);
    }

    public function process_dialist($data_cdr, $secret) {
        $dialid = $data_cdr['dialid'];    
        $_id =  new MongoDB\BSON\ObjectId($dialid);
        $fields = ["calluuid", "disposition", "userextension", "customernumber", "starttime", "causetxt"];
        $callResult = array();
        foreach ($fields as $field) {
            if($field == "starttime") {
                $callResult[$field] = isset($data_cdr[$field]) ? $data_cdr[$field] : 0;
            } else $callResult[$field] = isset($data_cdr[$field]) ? $data_cdr[$field] : "";
        }
        // Update diallist detail
        $this->mongo_db->where(array("_id" => $_id))->update($this->diallist_detail, array(
            '$inc' => array("tryCount" => 1), 
            '$addToSet' => array("callResult" => $callResult)
        ));  
    }

    public function put($secret,$callernum,$destnum,$interval,$urlDiallistId){
        $this->load->library('beanstalk');
        $callJob = new stdClass();
        $callJob->secret = $secret;
        $callJob->callernum = $callernum;
        $callJob->destnum= $destnum;
        $callJob->suburl=$urlDiallistId;
        $callJob->startTimestamp = time() + $interval;
        $this->mongo_db->insert("debugs",array("calljob"=>$callJob));
        $this->beanstalk->queue->useTube("calljobs")->put(json_encode($callJob),0, $callJob->startTimestamp - time(),300);
    }
}
