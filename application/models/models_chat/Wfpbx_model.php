<?php

class Wfpbx_model extends CI_Model {

    public function __construct() {
        $this->load->library('mongo_db');
        
    }
public function getAgentState($userextension=0) {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            if($userextension!=0){
                $query="listAgentStates2.php?secrect=$secret&version=4&extension=$userextension";
            }else{
                $query="listAgentStates2.php?secrect=$secret&version=4";
            }
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }
 public function getAgentStateByQueue($queue=0) {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            if($queue!=0){
                $query="listAgentStates2.php?secrect=$secret&version=4&queuename=$queue";
            }else{
                $query="listAgentStates2.php?secrect=$secret&version=4";
            }
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }   
 public function pause_queue($queue_name, $extension,$all=0) {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            $query="pauseQueueMember2.php?queuename=".$queue_name."&extension=".$extension."&secrect=".$secret;
            if($all!=0){
                $query=$query."&all";
            }
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }
     public function unpause_queue($queue_name, $extension,$all=0) {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            $query="unpauseQueueMember2.php?queuename=".$queue_name."&extension=".$extension."&secrect=".$secret;
            if($all!=0){
                $query=$query."&all";
            }
            
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }
    public function remove_queue($queue_name, $extension) {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            $query="removeQueueMember2.php?queuename=".$queue_name."&extension=".$extension."&secrect=".$secret;
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }
     public function add_queue($queue_name, $extension) {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            $query="addQueueMember2.php?queuename=".$queue_name."&extension=".$extension."&secrect=".$secret;
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }
 public function getAgent($userextension=0,$issupervisor=0,$issadmin=0) {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            $query="listAgent2.php?secrect=$secret&version=4";
            if($userextension!=0){
                $query=$query."&extension=$userextension";
            }
            if($issupervisor!=0){
                $query=$query."&issupervisor=$issupervisor";
            }
            if($issadmin!=0){
                $query=$query."&isadmin=$isadmin";
            }
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }
public function getListQueueName() {
        $query = $this->mongo_db->getOne('wff_config');
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            $query="listQueueNames2.php?secrect=$secret&version=4";
            $resuil=  $this->send($query);
            $data=  json_decode($resuil,true);
            if($data['status']==200){
                return $data['data'];
            }else{
                return "";
            }
        } else {
           return "";
        }
    }
    public function makeCall($phone,$diallistId=0) { 
        
        $query = $this->mongo_db->getOne('wff_config');	
        $urlDiallistId = '';
        if($diallistId!=0){
                $urlDiallistId = "&dialid=".$diallistId;
        }
        if (is_array($query) && count($query) > 0) {
            $secret=$query['secret_key']; 
            $extension = $this->session->userdata("extension");
            $query="makecall2.php?callernum=".$extension."&destnum=".$phone.$urlDiallistId."&secrect=".$secret;
            $resuil=  $this->send($query);
            if($resuil==200){
                return $resuil;
            }else{
                return "";
            }
        } else {
            return "";
        }
    }
public function send($query) {
    $url=$this->config->item('url_api_4x').$query;
    $curl = curl_init(); 
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Basic ",
            "cache-control: no-cache",
            "content-type: application/json"
      ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if (!$err) {
        if($response!=null){
            return $response;
        }         
    }
}
public function time_since($since) {
        $chunks = array(
            array(60 * 60 * 24 * 365 , 'year'),
            array(60 * 60 * 24 * 30 , 'month'),
            array(60 * 60 * 24 * 7, 'week'),
            array(60 * 60 * 24 , 'day'),
            array(60 * 60 , 'hour'),
            array(60 , 'minute'),
            array(1 , 'second')
        );

        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            if (($count = floor($since / $seconds)) != 0) {
                break;
            }
        }

        $print = ($count == 1) ? '1 '.$name." ago" : "$count {$name}s ago";
        return $print;
    }

    public function getAgentName() {
        $result = $this->mongo_db->get("extension_status");
        return $result;
    }
}
