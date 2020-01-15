<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Pbxevents extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('callevent_model');
    }

    function write_log() {
        $test = $_GET;
        $test["createdAt"] = date("Y-m-d H:i:s");
        $test["query"] = http_build_query($_GET);
        $this->mongo_db->insert("Pbxevents_debug", $test);      
    }

    public function index() {

        if($this->config->item("record_event")) {
            $this->write_log();
        }
        try {
            $secret = $this->input->get('secret');
            $connector = $this->callevent_model->get_key($secret);
            
            if(!$connector) throw new Exception("Unauthorization"); 
            $request = $_GET;
            if(!isset($request['callstatus'])) throw new Exception("No call status");
            $callstatus = $request['callstatus'];
            switch ($callstatus) {
                case "Start":
                        $this->start_process($request);
                        break;
                case "Dialing":
                        $this->dialing_process($request);
                        break;
                case "DialAnswer":
                        $this->dialanswer_process($request);
                        break;
                case "HangUp":
                        $this->hangup_process($request);
                        break;
                case "CDR":
                        $this->cdr_process($request,$secret,$connector);
                        break;
                case "Trim":
                        $this->trim_process($request);
                        break;
                case "SyncCurCalls":
                      $this->Sync_process($request,$secret,$connector);
                        break;
                case "VoiceMail":
                    $this->VoiceMail_process($request);
                    break;
                case "PutCDR":
                    $this->PutCDRs_process($request);
                    break;
                case "CDRExtension":
                    $this->CDRExtension_process($request);
                    break;
                case "DialAnswerTransfer":
                    $this->DialAnswerTransfer_process($request);
                    break;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function start_process($request){
        $data['direction']=$request['direction'];
        $data['callstatus']=$request['callstatus'];
        $data['dnis']=$request['dnis'];
        $data['workstatus']="New";
        $data['calluuid']=$request['calluuid'];
        $data['staffed']=0;
//        $data['calltype']=$request['calltype'];
        $data['disposition']='NO ANSWER';
        if(isset($request['queue'])){
             $data['queue']=$request['queue'];
        }
        if(isset($request['calltype'])){
            $data['calltype']=$request['calltype'];
        }
        if(isset($request['extension_available'])){
             $data['extension_available']=$request['extension_available'];
        }
        if($data['direction']=="outbound"){
            $data['userextension']=$request['callernumber'];
            $data['customernumber']=$request['destinationnumber'];
        }else{
            $data['userextension']=$request['destinationnumber'];
            $data['customernumber']=$request['callernumber'];
        } 
        if (preg_match("#^0(.*)$#i", $data['customernumber'])!=0){
                   $data['customernumber'] = $data['customernumber'];
           }else{
                   $data['customernumber'] = "0".$data['customernumber'];
           }
        $data['show_popup'] = 0;
        $data['starttime']=strtotime($request['starttime']);
        if(!$this->callevent_model->checkCalluuid($data['calluuid'])){
            // Check dial id
            if(isset($request['dialid'])){
                $dial_data = json_decode(base64_decode($request['dialid']), TRUE);
                $data = array_merge($data, $dial_data);   
            }
            if($this->callevent_model->set_cdr($data)){
                echo "200";
            }else{
                echo "401"; 
            }
        }else{
            echo "200";
        }
        
    }

    public function dialing_process($request){
        $data['direction']=$request['direction'];
        $data['callstatus']=$request['callstatus'];
        $data['workstatus']="Ring";
        $data['calluuid']=$request['calluuid'];
        $data['agentname']=$request['agentname'];
        $data['staffed']=0;
        if(isset($request['queue'])){
             $data['queue']=$request['queue'];    
        }
        if(isset($request['dialid'])){
            $dial_data = json_decode(base64_decode($request['dialid']), TRUE);
            $data = array_merge($data, $dial_data);   
        }
        if(isset($request['calltype'])){
            $data['calltype']=$request['calltype'];
        }
        if(isset($request['internal'])){
            $data['internal']=true;
        }
        if($data['direction']!="internal"){
            if($data['direction']=="outbound"){
                $data['userextension']=$request['callernumber'];
                if(!isset($data['internal'])){
                    if (preg_match("#^0(.*)$#i", $request['destinationnumber'])!=0||preg_match("#^(84|19|18)(.*)$#i", $request['destinationnumber'])!=0||strlen($request['destinationnumber'])>10){
                        $data['customernumber'] = $request['destinationnumber'];
                    }else{
                        $data['customernumber'] = "0".$request['destinationnumber'];
                    }
                }else{
                    $data['customernumber'] = $request['destinationnumber'];
                }

                $now=date('Y-m-d');
                $nowtimestamp =  strtotime($now);
                $fullName=$this->callevent_model->getAgentFullName($data['userextension']);
                $this->callevent_model->check_misscall($data['customernumber']);
                $data['disposition']='NO ANSWER';
            }else{
                $data['userextension']=$request['destinationnumber'];
                if(!isset($data['internal'])){
                    if (preg_match("#^0(.*)$#i", $request['callernumber'])!=0||preg_match("#^(84|19|18)(.*)$#i", $request['callernumber'])!=0||strlen($request['callernumber'])>10){
                        $data['customernumber'] = $request['callernumber'];
                    }else{
                        $data['customernumber'] = "0".$request['callernumber'];
                    }
                }else{
                    $data['customernumber'] = $request['callernumber'];
                }
                $fullName=$this->callevent_model->getAgentFullName($data['userextension']);
                $this->callevent_model->set_agentName($request['parentcalluuid'], $request['agentname'],$fullName);
               // if($this->callevent_model->checkCalluuid($data['calluuid'])){
                $this->callevent_model->set_agentGlide($request['parentcalluuid'], $request['destinationnumber']);
               // }
            }
        }
        if(isset($request['dnis'])){
            $data['dnis']=$request['dnis'];
        }
        if(isset($request['parentcalluuid'])){
            $data['calluuid2']=$request['parentcalluuid'];   
        }
        
        $data['agentfullname']=$fullName;

        $data['calltype']=@$request['calltype'];

        if($this->callevent_model->checkCalluuid($data['calluuid'])){ 
            unset($data['direction']);
            $data['starttime']=strtotime($request['starttime']);
            if($this->callevent_model->update_cdr($data)){
                   echo "200";
                }else{
                   echo "401"; 
                }
        }else{
            if($data['direction']=="internal"){
                $data['userextension']=$request['callernumber'];
                $data['customernumber'] = $request['destinationnumber'];
            }
            $data['show_popup'] = 0;
            $data['starttime']=strtotime($request['starttime']);
            if($this->callevent_model->set_cdr($data)){
                    echo "200";
                }else{
                   echo "401"; 
                }
        }
    }

    public function dialanswer_process($request){
        
        $data['calluuid']=$request['calluuid'];
        $cur_cdrs=$this->callevent_model->get_cdr($data['calluuid']);
        $cur_cdr=$cur_cdrs[0];
        //print_r($cur_cdr);
        if($cur_cdr['workstatus']=='On-Call'){
           $data['transfer_popup']=0;
           $data['transfernumber']=$request['destinationnumber'];
        }else{
            $data['callstatus']=$request['callstatus'];
            $data['workstatus']="On-Call";
            if(!isset($cur_cdr['internal'])){
                if($cur_cdr['direction']=="outbound") {
                    if($cur_cdr['calltype']=="Outbound_ACD"){
                        $data['userextension']=$request['destinationnumber'];
                        $data['agentname'] = $this->callevent_model->getAgentFullName($data['userextension']);
                    }
                } else {
                    $data['userextension']=$request['destinationnumber'];
                    if ((preg_match("#^0(.*)$#i", $request['callernumber'])!=0)||(preg_match("#^(84|19|18)(.*)$#i", $request['callernumber'])!=0)||strlen($request['callernumber'])>10){
                        $data['customernumber'] = $request['callernumber'];
                    }else{
                        $data['customernumber'] = "0".$request['callernumber'];
                    }
                }
            }
            if($request['answertime']){
                $data['answertime']= strtotime($request['answertime']);
            }
            if(isset($data['customernumber'])) {
                $this->callevent_model->check_misscall($data['customernumber']);
            }
            $data['calluuid2']=$request['childcalluuid'];
            $data['disposition']='ANSWERED';
        }
        
       
        if($this->callevent_model->update_cdr($data)){    
            echo "200";
        }else{
            echo "401"; 
        }
    }

    public function hangup_process($request){
        $data['callstatus']=$request['callstatus'];
        $data['workstatus']="Complete";
        $data['calluuid']=$request['calluuid'];
        $data['causetxt']="";
        if(isset($request['context'])){
            $data['connect_info'] =$request['context'];
        }
        if(isset($request['callernumber'])){
            $data['dnis'] =$request['callernumber'];
        }
        switch($request['causetxt']){
            case 0:                         
                $data['causetxt'] = "Cancel";
                break;
            case 1:                     
                $data['causetxt'] = "Not Available";
                break;
            case 16:                                        
                $data['causetxt'] = "Answer";
                break;
            case 17:    
                $data['causetxt'] = "Busy";
                break;
            case 18: 
                $data['causetxt'] = "No answer";
                break;
            case 19:
                $data['causetxt'] = "No answer";
                break;
            case 34 :           
                $data['causetxt'] = "No answer";
                break;
            case 38 :           
                $data['causetxt'] = "Error";
                break;
            case 20 :           
                $data['causetxt'] = "Call Back Fail";
                break;
            default :
                $data['causetxt']= "Error";
        }
        if($this->callevent_model->update_cdr($data)){
            echo "200";
         }else{
            echo "401"; 
         }
        $this->callevent_model->delete_cdr_realtime($data['calluuid']);
    }

    public function cdr_process($request){
        $data['workstatus']="Complete";
        $data['endtime']=strtotime($request['endtime']);
        $data['calluuid']=$request['calluuid'];
        $data['billduration']=(int)$request['billduration'];
        $data['totalduration']=(int)$request['totalduration'];
        if(isset($request['monitorfilename'])&&$request['monitorfilename']!=""){
             $data['record_file_name']=$request['monitorfilename'].".mp3";
        }
        if(isset($request['disposition'])&&$request['disposition']!="ANSWERED"){
            $data['disposition']=$request['disposition'];
        }
        $cdr = $this->callevent_model->get_cdr($data['calluuid']);
        if(!$cdr) throw new Exception("cdr_process not found cdr");

        if($cdr[0]['direction']=="inbound"&&$request['disposition']!="ANSWERED"){
            $data['disposition']="NO ANSWER";
        }
        if(isset($cdr[0]['answertime'])){
           $data['answertime']=$cdr[0]['answertime'];
        }
        if($cdr[0]['direction']=="inbound"){
             if($cdr[0]['disposition']=='ANSWERED'){
                $data['waittimeinqueue']=$cdr[0]['answertime']-$cdr[0]['starttime']-1;
                if(!isset($cdr[0]['glide_extension'])){
                   $data['glide_extension']= array($cdr[0]['userextension']);
                }
             }else{
                $data['waittimeinqueue']=$data['endtime']-$cdr[0]['starttime']-1;
             }
        }else{
            if($cdr[0]['direction']=="outbound" && isset($request['dnis'])){
                $data['dnis']=$request['dnis'];
            }
        }
        if(isset($data['endtime'], $data['answertime'])) {
            $data["callduration"] = !empty($data['answertime']) ? $data['endtime'] - $data['answertime'] : 0;
        } else $data["callduration"] = 0;

        // Update 19/12/2019. dung.huynh@southtelecom.vn
        if( isset($request['disposition']) && $request['disposition']=="FAILED" && $data['totalduration']>=40 ) {
            $data["disposition"] = "TIMEOUT";
        }
        //
       
        if($this->callevent_model->update_cdr($data)){
           if($cdr[0]['direction']=="inbound"&&$cdr[0]['disposition']=="NO ANSWER"){
                $data_misscall=$cdr[0];
                unset($data_misscall['_id']);
                unset($data_misscall['direction']);
                unset($data_misscall['workstatus']);
                if(isset($data_misscall['glide_extension'])&&count($data_misscall['glide_extension'])>0){
                    $data_misscall['process_by']=$data_misscall['glide_extension'][0];
                }else{
                    $data_misscall['process_by']="";
                }
                
                $this->callevent_model->set_misscall($data_misscall);
            }
            if(isset($cdr[0]['dialid'])) {
                $this->callevent_model->process_dialist($cdr[0]);
            }
            echo "200";
        } else {
            echo "401"; 
        }
        $this->callevent_model->delete_cdr_realtime($data['calluuid']);
    }
    public function trim_process($request){
            $calluuid=$request['calluuid']; 
        if($this->callevent_model->delete_cdr($calluuid)){
            echo "200";
         }else{
            echo "401"; 
         }
    }
    public function Sync_process($request,$secret,$connector){
            if($request['calluuids']==""){
                $calluuidlist=$this->callevent_model->get_curentcall1();
            }else{
                $calluuids= explode(";", $request['calluuids']);
                //print_r($calluuids);
                $calluuidlist=$this->callevent_model->get_curentcall($calluuids);
            }
            $calluuids= explode(";", $request['calluuids']);
            //print_r($calluuids);
            $calluuidlist=$this->callevent_model->get_curentcall($calluuids);
            foreach($calluuidlist as $calluuid){
                
                 $curl = curl_init();                          
                curl_setopt_array($curl, array(
                  CURLOPT_URL => $connector["pbx_url"]."externalcrm/getcdr2.php?calluuid=".$calluuid['calluuid']."&secrect=$secret&version=4",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 30,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "GET",
                  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
                  CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "cache-control: no-cache",
                    "content-type: application/json"
                  ),
                ));
                $response = curl_exec($curl);
                $responseObj = (array) json_decode($response);
                if($responseObj['status']==200){
                    if(isset($responseObj['data'])){
                        $dataUpdate=(array)$responseObj['data'];
                        $data['callstatus']="HangUp";
                        $data['workstatus']="Complete";
                        $data['starttime']=strtotime($dataUpdate['starttime']);
                        $data['answertime']=strtotime($dataUpdate['answertime']);
                        $data['endtime']=strtotime($dataUpdate['endtime']);
                        $data['calluuid']=$dataUpdate['calluuid'];
                        $data['billduration']=(int)$dataUpdate['billduration'];
                        $data['totalduration']=(int)$dataUpdate['totalduration'];
                        $data['disposition']=$dataUpdate['disposition'];
                        $data['waittimeinqueue']=$data['answertime']-$data['starttime']-1;
                        $data['wrapuptime']=0;
                        $data['holdtime']=0;
                        $data['talktime']=$data['billduration']-$data['waittimeinqueue'];
                        if($dataUpdate['direction']=='outbound'){
                            $data['userextension']=$dataUpdate['callernumber'];
                            if (preg_match("#^0(.*)$#i", $dataUpdate['destinationnumber'])!=0||preg_match("#^(84|19|18)(.*)$#i", $dataUpdate['destinationnumber'])!=0||strlen($dataUpdate['destinationnumber'])>10){
                                $data['customernumber'] = $dataUpdate['destinationnumber'];
                            }else{
                                $data['customernumber'] = "0".$dataUpdate['destinationnumber'];
                            }
                        }else{
                           $data['userextension']=$dataUpdate['destinationnumber'];
                            if ((preg_match("#^0(.*)$#i", $dataUpdate['callernumber'])!=0)||(preg_match("#^(84|19|18)(.*)$#i", $dataUpdate['callernumber'])!=0)||strlen($dataUpdate['callernumber'])>10){
                                $data['customernumber'] = $dataUpdate['callernumber'];
                            }else{
                                $data['customernumber'] = "0".$dataUpdate['callernumber'];
                            } 
                            if($data['disposition']!="ANSWERED"){
                                $data['disposition']="NO ANSWER";
                            }
                        }
                        $data['dnis']=$dataUpdate['dnis'];
                        $this->callevent_model->delete_cdr_realtime($data['calluuid']);
                        $this->callevent_model->update_cdr($data); 
                    }
                }else{
                    if(isset($responseObj['status'])){
                        if($responseObj['status']==401){
                            $data['callstatus']="HangUp";
                            $data['workstatus']="Complete";
                            $data['calluuid']= $calluuid['calluuid'];
                            $this->callevent_model->update_cdr($data);
                        }
                    }
                    
                }
                
                $err = curl_error($curl);
                curl_close($curl);
            }
             echo "200";
    }
   
    public function VoiceMail_process($request){      
        $data['voice_id']=$request['id'];
        $data['mailbox']=$request['mailbox'];
        $data['customernumber']=$request['src'];
        $data['filename']=$request['filename'];
        $data['create_time']=  strtotime($request['vm_time']);
        $data['duration']=  $request['duration'];
        if($this->callevent_model->set_voicemail($data)){
                echo "200";
             }else{
                echo "401"; 
             }
    }

    public function PutCDRs_process($request){
            $calluuid=$request['calluuid'];
            if(isset($request['calluuid'])){
                $dataUpdate=(array)$request;
                $data['callstatus']="HangUp";
                $data['workstatus']="Complete";
                $data['starttime']=(int) strtotime($dataUpdate['starttime']);
                $data['answertime']=(int) strtotime($dataUpdate['answertime']);
                $data['endtime']=(int) strtotime($dataUpdate['endtime']);
                $data['calluuid']=$dataUpdate['calluuid'];
                $data['billduration']=(int)$dataUpdate['billduration'];
                $data['totalduration']=(int)$dataUpdate['totalduration'];
                $data['disposition']=$dataUpdate['disposition'];
                $data['waittimeinqueue']=$data['endtime']-$data['starttime'];
                $data['wrapuptime']=0;
                $data['holdtime']=0;
                $data['talktime']= $data['answertime'] ? $data['endtime']-$data['answertime'] : 0;
                if($dataUpdate['direction']=='outbound'){
                    $data['userextension']=$dataUpdate['callernumber'];
                    if (preg_match("#^0(.*)$#i", $dataUpdate['destinationnumber'])!=0||preg_match("#^(84|19|18)(.*)$#i", $dataUpdate['destinationnumber'])!=0||strlen($dataUpdate['destinationnumber'])>10){
                        $data['customernumber'] = $dataUpdate['destinationnumber'];
                    }else{
                        $data['customernumber'] = "0".$dataUpdate['destinationnumber'];
                    }
                }else{
                   $data['userextension']=$dataUpdate['destinationnumber'];
                    if ((preg_match("#^0(.*)$#i", $dataUpdate['callernumber'])!=0)||(preg_match("#^(84|19|18)(.*)$#i", $dataUpdate['callernumber'])!=0)||strlen($dataUpdate['callernumber'])>10){
                        $data['customernumber'] = $dataUpdate['callernumber'];
                    }else{
                        $data['customernumber'] = "0".$dataUpdate['callernumber'];
                    }
                    if($data['disposition']!="ANSWERED"){
                        $data['disposition']="NO ANSWER";
                    }
                }
                $data['direction']=$dataUpdate['direction'];
                $data['dnis']=$dataUpdate['dnis'];
                // Check dial id
                if(isset($request['dialid'])){
                    $dial_data = json_decode(base64_decode($request['dialid']), TRUE);
                    $data = array_merge($data, $dial_data);   
                }
                // PUSH RESULT TO DIALLIST DETAIL
                $this->callevent_model->process_dialist($data);
                $this->callevent_model->handle_PutCDRs($data);
            }
                    
            echo 200;
            exit();
    }

    public function CDRExtension_process($request) {
        $data = $this->callevent_model->get_cdr($request["calluuid"]);
        if(isset($data['dialid'])){
            $this->callevent_model->handle_DialInProcess($data);
        }
        echo 200;
        exit();
    }

    public function DialAnswerTransfer_process($request){
        
        $data['calluuid']=$request['calluuid'];
        $cur_cdrs=$this->callevent_model->get_cdr($data['calluuid']);
        $cur_cdr=$cur_cdrs[0];
        $data['callstatus']=$request['callstatus'];
        $data['workstatus']="On-Call";
        if($cur_cdr["direction"] == "outbound"&&$cur_cdr["calltype"] != "Outbound_ACD") {
            $data['userextension']=$request['callernumber'];
        } else {
            $data['userextension']=$request['destinationnumber'];
        }
        $data['transfer_extension'] = $cur_cdr['userextension'];
        $data['transfer_agentname'] = $this->callevent_model->getAgentFullName($cur_cdr['userextension']);
        if(isset($request['agentname'])){
            $data['agentname']=$request['agentname'];
        }
       
        $data['transfered'] = true;
        $data['transfer_time'] = time();
        if($request['answertime']){
            $data['transfer_time']= strtotime($request['answertime']);
        }

        if($this->callevent_model->update_cdr($data)) {
            echo "200";
        } else {
            echo "401"; 
        }
    }
}