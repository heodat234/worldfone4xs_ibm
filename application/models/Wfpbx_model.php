<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Bỏ, không sử dụng nữa
class wfpbx_model extends CI_Model {

    public function __construct() {
        $this->load->library('mongo_db');
        $this->load->config("_mongo");
        $this->mongo_db->switch_db( $this->config->item("session_mongo_db") );
    }

    public function getAgentState($userextension) {
        
        $config = $this->mongo_db->getOne('Config');
        try {
            if(!$config) throw new Exception("No config");
            $secret = $config['secret_key']; 
            $query="listAgentStates2.php?secrect=$secret&version=4&extension=$userextension";
            $result=  $this->send($query, $config);
            $data =  json_decode($result,true);
            if($data['status']!=200) throw new Exception("No config");
            return $data['data'];
        } catch(Exception $e) {
            show_error($e->getMessage());
        }
    }
 public function pause_queue($queue_name, $extension,$all=0) {        
        $config = $this->mongo_db->getOne('Config');
        if ($config) {
            $secret = $config['secret_key']; 
            $query="pauseQueueMember2.php?queuename=".$queue_name."&extension=".$extension."&secrect=".$secret;
            if($all!=0){
                $query=$query."&all";
            }
            $resuil=  $this->send($query, $config);
            //print_r($resuil);
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
        $config = $this->mongo_db->getOne('Config');
        if ($config) {
            $secret=$config['secret_key']; 
            $query="unpauseQueueMember2.php?queuename=".$queue_name."&extension=".$extension."&secrect=".$secret;
            if($all!=0){
                $query=$query."&all";
            }
            
            $resuil=  $this->send($query, $config);
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
        $config = $this->mongo_db->getOne('Config');
		
        if ($config) {
            $secret=$config['secret_key']; 
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
            $resuil=  $this->send($query, $config);
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
        $config = $this->mongo_db->getOne('Config');
        if ($config) {
            $secret=$config['secret_key']; 
            $query="listQueueNames2.php?secrect=$secret&version=4";
            $resuil=  $this->send($query, $config);
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
public function send($query, $config) {
    $this->mongo_db->switch_db();
    $url = $config["pbx_url"].$query;
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

    if($err) throw new Exception("Curl error: ".print_r($err));

    return $response;       
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
    
}
