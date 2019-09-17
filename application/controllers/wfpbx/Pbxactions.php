<?php

/* 
 * Copyright © 2014 South Telecom
 * Bỏ
 */

class Pbxactions extends WFF_Controller{ 
    
    public function __construct() {
        parent::__construct();
        $this->load->library("mongo_db");
    }   
	
	public function changeToState() {
		date_default_timezone_set('Asia/Ho_Chi_Minh');
		$request 			= json_decode(file_get_contents('php://input'));
		$extension 			= $this->session->userdata("extension");
		$session_id			= $this->session->session_id;
		$time 				= time();
		
		try {
			$this->load->model("agentstatus_model");
			$this->agentstatus_model->change((array) $request);
			
			switch ($request->agentState) {
				case 3:
					$message = "Unvailable";
					break;
				case 4:
					$message = "ACW";
					break;
				case 1: default:
					$message = "Available";
					break;
			}
			echo json_encode(array("status" => 1, "message" => $message));			
		} catch (Exception $e) {
			echo json_encode(array('status' => 0, "message" => $e->getMessage()));
		}
    }
    
	private function unpause_queue($queue_name, $extension,$all=0)
	{
            
            $this->load->model("wfpbx_model");
            $this->wfpbx_model->unpause_queue($queue_name, $extension, $all);
	}
	
	private function pause_queue($queue_name, $extension,$all=0)
	{
        
            $this->load->model("wfpbx_model");
            $this->wfpbx_model->pause_queue($queue_name, $extension, $all);
	}
	
	// Dung cho monitor
	public function listAgentStates() {
		header('Content-Type: application/json');
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$query = $this->mongo_db->getOne('wff_config');
		
		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();          
			$secret=$query['secret_key'];

			$querystring[] = "secrect=".$secret;
			if (!isset($request->monitor)) {
				$querystring[] = "extension=".$extension;
			}
			curl_setopt_array($curl, array( 
			  CURLOPT_URL => $this->config->item('url_api_4x')."listAgentStates2.php?".implode("&", $querystring),
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  return false;
			} else {
				if($responseArr!=null){
					if (isset($request->monitor)) {
						if (!$this->session->userdata("isadmin")){
							$groups = $this->mongo_db->where_in("supervisor", array($this->session->userdata("extension")))->get("groups");
							$accessible_group = array();
							foreach ($groups as $group) {
								$accessible_group = array_merge($accessible_group, array_values($group["members"]));
							}
						}
						foreach ($responseArr['data'] as $ex_key => &$extension) {
							if (!$this->session->userdata("isadmin")) {
								$in_queues = array();
								foreach ($extension["queues"]["queue"] as $queue) {
									$in_queues[] = $queue["queuename"];
								}
								if (count(array_intersect($accessible_group, $in_queues)) == 0) {
									unset($responseArr['data'][$ex_key]);
									continue;
								}
							}
							//Direction In OnCall/Ring State
							$this->mongo_db->insert("test", $extension);
							switch ($extension["state"]) {
								case 'ONCALL':
                                    $curcdr_local= $this->mongo_db->where(array("userextension" => $extension["extension"], "workstatus" => "On-Call"))->order_by(array("starttime" => -1))->getOne("worldfonepbxmanager");
									$extension["curcdr"]["direction"] = $curcdr_local["direction"];
                                    $extension["curcdr"]["calluuid"] = $curcdr_local["calluuid"];
                                    $extension["curcdr"]["callernumber"] = $curcdr_local["customernumber"];
                                    $extension["curcdr"]["destinationnumber"] = $curcdr_local["customernumber"];
									break;
								case 'RINGING':
                                    $curcdr_local=$this->mongo_db->where(array("userextension" => $extension["extension"], "workstatus" => "Ring"))->order_by(array("starttime" => -1))->getOne("worldfonepbxmanager");
									$extension["curcdr"]["calluuid"] = $curcdr_local["calluuid"];
                                    $extension["curcdr"]["callernumber"] = $curcdr_local["customernumber"];
                                    $extension["curcdr"]["destinationnumber"] = $curcdr_local["customernumber"];
									break;
							}
							
							// Total Calls
							$extension["totalcalls"] = $this->mongo_db->where(array("userextension" => $extension["extension"], "workstatus" => array('$ne' => "Ring"), "starttime" => array('$gte' => strtotime('today midnight'))))->count("worldfonepbxmanager");
							// switch ($extension["state"]) {
							// 	case 'IDLE':
							// 		$extension["availabletime"] = $this->mongo_db->where(array("userextension" => $extension["extension"], "statuscode" => 1))->order_by(array("starttime" => -1))->getOne("agentstatuslogs")["duration"];
							// 		break;
							// 	case 'LOGGEDOFF':
							// 		$extension["unavailabletime"] = $this->mongo_db->where(array("userextension" => $extension["extension"], "statuscode" => 2))->order_by(array("starttime" => -1))->getOne("agentstatuslogs")["duration"];
							// 		break;
							// }
							// Status
							$status = $this->mongo_db->where(array("userextension" => $extension["extension"], "endtime" => ""))->order_by(array("starttime" => -1))->limit(1)->get("agentstatuslogs_realtime");
							// $status = $this->mongo_db->where(array("userextension" => $extension["extension"], "endtime" => ""))->order_by(array("starttime" => -1))->limit(1)->get("agentstatuslogs_realtime");
							// $status = $this->mongo_db->where(array("userextension" => $extension["extension"], "endtime" => array('$ne'=> ""), "endtime" => array('$ne'=> null)))->order_by(array("starttime" => -1))->limit(1)->get("agentstatuslogs_realtime");
							$status = (!empty($status[0])) ? $status[0] : array();
							//$extension["forbug"] = $extension["extension"] . ": " . print_r($status, true);
                            $extension["duration"] = (isset($status["duration"])) ? $status["duration"] : 0;
                                                        //insert new row
							if (count($status) > 0) {
								if (empty($status["substatuscode"])) {
									switch ($status["statuscode"]) {
										case '1':
											$extension["status"] = "Available";
											$extension["availabletime"] = $status["duration"];
											break;
										case '2':
											$extension["status"] = "Unavailable";
											$extension["unavailabletime"] = $status["duration"];
											break;
										case '3':
											$extension["status"] = "On Call";
											$oncall_details = $this->mongo_db->where(array("userextension" => $extension["extension"], "workstatus" => "On-Call"))->order_by(array("starttime" => -1))->getOne("worldfonepbxmanager");
											$extension["state"] = "ONCALL";
											$extension["curcdr"]["direction"] = $oncall_details["direction"];
                                                                                        $extension["curcdr"]["calluuid"] = $oncall_details["calluuid"];
											$extension["curcdr"]["callernumber"] = $oncall_details["customernumber"];
											$extension["curcdr"]["destinationnumber"] = $oncall_details["customernumber"];
											break;
										case '4':
											$extension["status"] = "ACW";
											break;
										default:
											$extension["status"] = "Offline";
											break;
									}
								} else {
									if ($status["statuscode"] == 1) {
										$extension["status"] = "Available";
									}
									if ($status["statuscode"] == 2) {
										$sub = $this->mongo_db->where(array("code" => $status["substatuscode"]))->getOne("unavailable")["value"];
										if (empty($sub)) $extension["status"] = "Unavailable";
										else $extension["status"] = $sub;
									}
									if ($status["statuscode"] == 3) {
										$extension["status"] = "On Call";
										$oncall_details = $this->mongo_db->where(array("userextension" => $extension["extension"], "workstatus" => "On-Call"))->order_by(array("starttime" => -1))->getOne("worldfonepbxmanager");
										$extension["state"] = "ONCALL";
										$extension["curcdr"]["direction"] = $oncall_details["direction"];
                                                                                $extension["curcdr"]["calluuid"] = $oncall_details["calluuid"];
										$extension["curcdr"]["callernumber"] = $oncall_details["customernumber"];
										$extension["curcdr"]["destinationnumber"] = $oncall_details["customernumber"];
									}
									if ($status["statuscode"] == 4) {
										$sub = $this->mongo_db->where(array("code" => $status["substatuscode"]))->getOne("acw")["value"];
										if (empty($sub)) $extension["status"] = "ACW";
										else $extension["status"] = $sub;
									}
								}
								$extension["statuscode"] = $status["statuscode"];
							} else {
								$extension["status"] = "Offline";
								$extension["statuscode"] = 2;
							}
							// Duration
							$Duration = $this->mongo_db->aggregate_pipeline("worldfonepbxmanager",
								array(
		                            array('$match' => array("userextension" => $extension["extension"],
		                                                "starttime" => array('$gte' => strtotime('today 00:00:00')))),
		                            array('$group' => array("_id" => array("userextension" => '$userextension'),
		                                                "Duration" => array('$sum' => '$callduration')))
	                                )
								);
							$extension["Duration"] = (isset($Duration[0]["Duration"])) ? $Duration[0]["Duration"] * 1000 : 0;
						}						
						echo json_encode(array_values($responseArr['data']));
					} else { 
						for( $iqq = 0; $iqq < count($responseArr['data'][0]['queues']['queue']) ; $iqq++ ){
							if( $responseArr['data'][0]['queues']['queue'][$iqq]['queuemembership'] == 'static' ){
								$responseArr['data'][0]['queues']['queue'][$iqq]['queueStatusLang'] = lang('queueStatic');
							}else if( $responseArr['data'][0]['queues']['queue'][$iqq]['queuemembership'] == 'dynamic' ){
								$responseArr['data'][0]['queues']['queue'][$iqq]['queueStatusLang'] = lang('queueDynamic');
							}else{
								$responseArr['data'][0]['queues']['queue'][$iqq]['queueStatusLang'] = lang('queueRealtime');
							}	
							
							if( $responseArr['data'][0]['queues']['queue'][$iqq]['queuememberpaused'] == '0' ){
								$responseArr['data'][0]['queues']['queue'][$iqq]['queuePausedLang'] = lang('queuePaused');
							}else{
								$responseArr['data'][0]['queues']['queue'][$iqq]['queuePausedLang'] = lang('queueUnpaused');
							}
							
						}
						echo json_encode($responseArr['data'][0]);
					}
				}else{
					return FALSE;
				}           
			}
		} else {
			return false;
		}
    }

    public function countAgentStates() {
		header('Content-Type: application/json');
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$query = $this->mongo_db->getOne('wff_config');
		
		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();          
			$secret=$query['secret_key'];

			$querystring[] = "secrect=".$secret;
			if (!isset($request->monitor)) {
				$querystring[] = "extension=".$extension;
			}
			curl_setopt_array($curl, array( 
			  CURLOPT_URL => $this->config->item('url_api_4x')."listAgentStates2.php?".implode("&", $querystring),
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			$countResponse = array();
			if ($err) {
			  return false;
			} else {
				if($responseArr!=null){
					$countResponse['data'][0]['total'] = count($responseArr['data']);
					$countLoggedin = 0;
					$countAvailable = 0;
					foreach ($responseArr['data'] as $extension) {
						$status = $this->mongo_db->where(array("userextension" => $extension["extension"], "endtime" => ""))->order_by(array("starttime" => -1))->limit(1)->get("agentstatuslogs_realtime");
						$status = (!empty($status[0])) ? $status[0] : array();
						if (count($status) > 0) {
							if ($status["statuscode"] == 1 or $status["statuscode"] == 2 or $status["statuscode"] = 3 or $status["statuscode"] == 4) {
								$countLoggedin++;
								if ($status["statuscode"] == 1) {
									$countAvailable++;
								}
							}	
						}
					}
					$countResponse['data'][0]['loggedin'] = $countLoggedin;
					$countResponse['data'][0]['available'] = $countAvailable;
					echo json_encode($countResponse['data']);					
				}else{
					return FALSE;
				}           
			}
		} else {
			return false;
		}
    }
	
	public function listQueueNames() { 
		$query = $this->mongo_db->getOne('wff_config');

		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();          
			$secret=$query['secret_key']; 
			
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->config->item('url_api_4x')."listQueueNames2.php?secrect=".$secret,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  echo json_encode(array());
			} else {
				if($responseArr!=null){
					echo json_encode($responseArr['data']);
				}           
			}
		} else {
			echo json_encode(array());
		}
    }
	
	public function makeCall() { 
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$config = $this->mongo_db->getOne('wff_config');

		try {
			if(!$config) throw new Exception("No config");
			$curl = curl_init();          
			$secret=$config['secret_key']; 
			$queryArr = array(
				"callernum" => $extension,
				"destnum"	=> trim($request->dial),
				"secrect"	=> $secret
			);
			if(isset($request->diallistId)){
				$queryArr["dialid"] = $request->diallistId;
			}
			$query = http_build_query($queryArr);
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $config["pbx_url"]."makecall2.php?{$query}",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if($err) throw new Exception("Curl error");
			if($responseArr != 200) throw new Exception("Call error");
			echo json_encode(array("status" => 1, "message" => "Call success"));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
    }
	
	public function pauseOneQueue() { 
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$config = $this->mongo_db->getOne('wff_config');

		try {
			if(!$config) throw new Exception("No config");
			$curl = curl_init();          
			$secret=$config['secret_key']; 
			$query = http_build_query(array(
				"queuename" => $request->queuename,
				"extension"	=> $extension,
				"secrect"	=> $secret
			));
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $config["pbx_url"]."pauseQueueMember2.php?{$query}",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if($err) throw new Exception("Curl error");
			if(empty($responseArr['status'])) throw new Exception("No success");
			echo json_encode(array("status" => 1, "message" => $responseArr['status']));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
    }
	
	public function unpauseQueueMember() { 
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$config = $this->mongo_db->getOne('wff_config');

		try {
			if(!$config) throw new Exception("No config");
			$curl = curl_init();          
			$secret=$config['secret_key']; 
			$query = http_build_query(array(
				"queuename" => $request->queuename,
				"reason"	=> $request->readon,
				"extension"	=> $extension,
				"secrect"	=> $secret
			));
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $config["pbx_url"]."unpauseQueueMember2.php?$query",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if($err) throw new Exception("Curl error");
			if(empty($responseArr['status'])) throw new Exception("No success");
			echo json_encode(array("status" => 1, "message" => $responseArr['status']));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
    }
	
	public function addQueueMember() { 
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$query = $this->mongo_db->getOne('wff_config');

		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();          
			$secret=$query['secret_key']; 
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->config->item('url_api_4x')."addQueueMember2.php?queuename=".$request->queuename."&extension=".$extension."&secrect=".$secret,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  echo json_encode(array());
			} else {
				if($responseArr!=null){
					echo json_encode($responseArr['status']);
				}else{
					echo json_encode(array());
				}            
			}

		} else {
			echo json_encode(array());
		}
    }
	
	public function removeQueueMember() { 
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$query = $this->mongo_db->getOne('wff_config');

		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();          
			$secret=$query['secret_key']; 
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->config->item('url_api_4x')."removeQueueMember2.php?queuename=".$request->queuename."&extension=".$extension."&secrect=".$secret,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  echo json_encode(array());
			} else {
				if($responseArr!=null){
					//return $responseArr['data'];
				}else{
					echo json_encode(array());
				}            
			}

		} else {
			echo json_encode(array());
		}
    }  
////////////////////////// End Hung

//Tin begin->>>>>>>>>
public function transfer() { 
            //$this->mongo_db->insert("debugs",array('post'=>  $this->input->post()));
            $type=  $this->input->post('type');
            $extension= $this->input->post('extension');
            $phone= $this->input->post('phone');
            $calluuid=$this->input->post('calluuid');
            $query = $this->mongo_db->getOne('wff_config');
            if (is_array($query) && count($query) > 0) {
                $secret=$query['secret_key']; 
                $query="transfercall2.php?calluuid=$calluuid"
                        . "&targetextension=$extension&type=$type"
                        . "&secrect=$secret&version=3";
                $resuil=  $this->send($query);
                if($resuil==="200"){
                    $this->mongo_db->where(array('calluuid'=>$calluuid))->set(array('transfer_state'=> $type,'transfernumber'=>$extension,'transferlocation'=>'inside'))->update('worldfonepbxmanager');
                }
                 echo $resuil;   
               // echo "200";
            } else {
                    echo "500";
            }
}
public function excuteTransfer() { 
            $calluuid=$this->input->post('calluuid');
            $query = $this->mongo_db->getOne('wff_config');
            if (is_array($query) && count($query) > 0) {
                $secret=$query['secret_key']; 
                $query="transfercall2.php?calluuid=$calluuid"
                        . "&type=excute"
                        . "&secrect=$secret&version=3";
                $resuil=  $this->send($query);
                if($resuil==="200"){
                    $this->mongo_db->where(array('calluuid'=>$calluuid))->set('transfer_state', 'done')->update('worldfonepbxmanager');
                }
                echo $resuil;  
            } else {
                    echo json_encode(array());
            }
}
public function turnbackTranfer() { 

            $calluuid=$this->input->get('calluuid');
            $query = $this->mongo_db->getOne('wff_config');
            if (is_array($query) && count($query) > 0) {
                $secret=$query['secret_key']; 
                $query="transfercall2.php?calluuid=$calluuid"
                        . "&type=turnback"
                        . "&secrect=$secret&version=3";
                $resuil=  $this->send($query);
                if($resuil==="200"){
                    $this->mongo_db->where(array('calluuid'=>$calluuid))->set('transfer_state', null)->update('worldfonepbxmanager');
                }
                echo $resuil;
                    
            } else {
                    echo json_encode(array());
            }
}
public function getListQueueName() {
    $calluuid=$this->input->get('calluuid');
    $query = $this->mongo_db->getOne('wff_config');
    if (is_array($query) && count($query) > 0) {
        $secret=$query['secret_key']; 
        $query="listQueueNames2.php?secrect=$secret&version=4";
        $resuil=  $this->send($query);
        echo $resuil;
    } else {
        echo json_encode(array());
    }
}
public function send($query) {
    $url=$this->config->item('url_api_4x')."".$query;
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
//tin end <<<<

// nhan
	public function listQueues() { 
		$query = $this->mongo_db->getOne('wff_config');

		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();          
			$secret=$query['secret_key']; 
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->config->item('url_api_4x')."listQueues2.php?secrect=".$secret,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  echo json_encode(array());
			} else {
				if($responseArr!=null){
					echo json_encode($responseArr['data']);
				}           
			}
		} else {
			echo json_encode(array());
		}
    }
	
	public function listGroups() { 
		$query = $this->mongo_db->getOne('wff_config');

		if (is_array($query) && count($query) > 0) {
			if ($this->session->userdata("isadmin")){
				$groups = $this->mongo_db->get("groups");
			} else {
				$groups = $this->mongo_db->where_in("supervisor", array($this->session->userdata("extension")))->get("groups");
			}
			echo json_encode($groups);
		} else {
			echo json_encode(array());
		}
    }

    public function callTypeList() {
    	$query = $this->mgongo_db->getOne('wff_config');

    	if (is_array($query) && count($query) > 0) {
    		$data = $this->mongodatasourceresult->read('worldfonepbxmanager', array('DISTINCT' => 'callType'));	
    		echo json_encode($data);
    	} else {
    		echo json_encode(array());
    	}
    }
	
	public function spy() { 
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$query = $this->mongo_db->getOne('wff_config');

		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();
			$secret=$query['secret_key'];
			$querystring[] = "calluuid=".$request->calluuid;
			$querystring[] = "spying=".$extension;
			$querystring[] = "spied=".$request->spied;
			$querystring[] = "mode=".$request->mode;
			$querystring[] = "secrect=".$secret;
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->config->item('url_api_4x')."chanspycall2.php?".implode("&", $querystring),
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response, true);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  echo json_encode(array());
			} else {
				if($responseArr!=null){
					echo json_encode($responseArr);
				}else{
					echo json_encode(array());
				}            
			}

		} else {
			echo json_encode(array());
		}
    }
	
	public function hangup() { 
		$request = json_decode(file_get_contents('php://input'));
		$extension = $this->session->userdata("extension");
		$query = $this->mongo_db->getOne('wff_config');

		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();
			$secret=$query['secret_key'];
			$querystring[] = "calluuid=".$request->calluuid;
			$querystring[] = "secrect=".$secret;
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->config->item('url_api_4x')."hangupcall2.php?".implode("&", $querystring),
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response, true);
			$err = curl_error($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			if ($err) {
			  echo json_encode(array('error' => 'error curl', 'code' => 1));
			} else {
				if($responseArr!=null){
					echo json_encode($responseArr);
				}else{
					echo json_encode(array('error' => $httpcode, 'code' => 2));
				}            
			}

		} else {
			echo json_encode(array('error' => 'Not config', 'code' => 3));
		}
    }
	
	// public function listMissedCalls() { 
 //        header('Content-Type: application/json'); 
	// 	$misscall = $this->mongo_db->where(array("calldate" => array('$gte' => strtotime('today 00:00:00'))))->order_by(array("calldate" => -1))->limit(5)->get("misscall");
	// 	echo json_encode($misscall);
 //    }
	
	public function listMissedCalls() { 
        header('Content-Type: application/json'); 
		// $misscall = $this->mongo_db->where(array("calldate" => array('$gte' => strtotime('today 00:00:00'))))->order_by(array("calldate" => -1))->limit(5)->get("misscall");
		//$userextension = $this->session->userdata("extension");
		$agentname = $this->session->userdata('agentname');
		$misscall = $this->mongo_db->aggregate_pipeline("misscall",
			array(
					array(
						'$match' => array(
								'starttime' => array('$gte' => strtotime('today midnight'))
							)
						),
					array(
						'$group' => array(
								'_id' => array("agentname" => '$agentname'),
								'count' => array('$sum' => 1),
								'extension_available'=> array( '$push' => '$extension_available' )
							)
						),
					array(
						'$project' => array(
							'agentname' => '$_id.agentname',
							'extension_available' => array('$reduce' => array(
									'input' => '$extension_available',
									'initialValue' => [],
									'in' => array('$setUnion' => array('$$value', '$$this'))
								)
							),
							'count' => 1
						)
					)
				));
		echo json_encode($misscall);
    }
	
    public function listRecentCalls() { 
        header('Content-Type: application/json'); 
		$misscall = $this->mongo_db->where(array("starttime" => array('$gte' => strtotime('today 00:00:00'))))->order_by(array("starttime" => -1))->limit(10)->get("worldfonepbxmanager");
		echo json_encode($misscall);
    }
    public function test() {
		$extension = $this->session->userdata("extension");
		$query = $this->mongo_db->getOne('wff_config');
		if (!$this->session->userdata("isadmin")){
			$groups = $this->mongo_db->where_in("supervisor", array($this->session->userdata("extension")))->get("groups");
			$accessible_group = array();
			foreach ($groups as $group) {
				$accessible_group = array_merge($accessible_group, array_values($group["members"]));
			}
		}

		if (is_array($query) && count($query) > 0) {
			//call webservice login
			$curl = curl_init();          
			$secret=$query['secret_key'];

			$querystring[] = "secrect=".$secret;
			curl_setopt_array($curl, array( 
			  CURLOPT_URL => $this->config->item('url_api_4x')."listAgentStates2.php?".implode("&", $querystring),
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => json_encode(array("secret"=>$secret)),
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic ",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));
			$response = curl_exec($curl);
			$responseArr = json_decode($response,true);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  return false;
			} else {
				if($responseArr!=null){
						foreach ($responseArr['data'] as $ex_key => &$extension) {
							if (!$this->session->userdata("isadmin")) {
								$in_queues = array();
								foreach ($extension["queues"]["queue"] as $queue) {
									$in_queues[] = $queue["queuename"];
								}
								if (count(array_intersect($accessible_group, $in_queues)) == 0) {
									unset($responseArr['data'][$ex_key]);
									continue;
								}
							}
							// Total Calls
							$extension["totalcalls"] = $this->mongo_db->where(array("userextension" => $extension["extension"], "workstatus" => array('$ne' => "Ring"), "starttime" => array('$gte' => strtotime('today 00:00:00'))))->count("worldfonepbxmanager");
							// Status
							$status = $this->mongo_db->where(array("userextension" => $extension["extension"], "endtime" => ""))->order_by(array("starttime" => -1))->limit(1)->get("agentstatuslogs_realtime");
							$status = (!empty($status)) ? $status : array();
							$extension["forbug"] = $extension["extension"] . ": " . print_r($status, true);
                                                        $extension["duration"] = (isset($status["duration"])) ? $status["duration"] : 0;
							if (count($status) > 0) {
								if (empty($status["substatuscode"])) {
									switch ($status["statuscode"]) {
										case '1':
											$extension["status"] = "Available";
											$extension["availabletime"] = $status["duration"];
											break;
										case '2':
											$extension["status"] = "Unavailable";
											$extension["unavailabletime"] = $status["duration"];
											break;
										case '3':
											$extension["status"] = "On Call";
											break;
										case '4':
											$extension["status"] = "ACW";
											break;
										default:
											$extension["status"] = "Unavailable";
											break;
									}
								} else {
									if ($status["statuscode"] == 1) {
										$extension["status"] = "Available";
									}
									if ($status["statuscode"] == 2) {
										$sub = $this->mongo_db->where(array("code" => $status["substatuscode"]))->getOne("unavailable")["value"];
										if (empty($sub)) $extension["status"] = "Unavailable";
										else $extension["status"] = $sub;
									}
									if ($status["statuscode"] == 3) {
										$extension["status"] = "On Call";
									}
									if ($status["statuscode"] == 4) {
										$sub = $this->mongo_db->where(array("code" => $status["substatuscode"]))->getOne("acw")["value"];
										if (empty($sub)) $extension["status"] = "ACW";
										else $extension["status"] = $sub;
									}
								}
								$extension["statuscode"] = $status["statuscode"];
							} else {
								$extension["status"] = "Unavailable";
								$extension["statuscode"] = 2;
							}
							// Duration
							$Duration = $this->mongo_db->aggregate_pipeline("worldfonepbxmanager",
								array(
		                            array('$match' => array("userextension" => $extension["extension"],
		                                                "starttime" => array('$gte' => strtotime('today 00:00:00')))),
		                            array('$group' => array("_id" => array("userextension" => '$userextension'),
		                                                "Duration" => array('$sum' => '$totalduration')))
	                                )
								);
							$extension["Duration"] = (isset($Duration[0]["Duration"])) ? $Duration[0]["Duration"] * 1000 : 0;
						}						
						echo json_encode($responseArr['data']);
				}else{
					return FALSE;
				}           
			}
		} else {
			return false;
		}
    }
}