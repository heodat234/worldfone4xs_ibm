<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Agent_chat_summary extends WFF_Controller {

	private $collection = "Agent_status";
	private $asc_collection = "Agent_status_code";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->asc_collection = set_sub_collection($this->asc_collection);
	}
	public function getGroups(){
		try {

            // $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read('chatGroup_Manager');
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
	}

	function ListAgent() {
		$Queue  = array();
		$data = $this->input->post("data");
		$extension=$this->session->userdata("extension");
		$i=0;
		// if($this->session->userdata('isadmin') == 1){
			foreach ($data as $key => $value) {
				$dataList  = $this->ListAgentFromChatGroupManager($value);
				foreach ($dataList["agents"] as $keydataList => $valuedataList) {
					$Queue[$i]["Value"] = $valuedataList;  
					$Queue[$i]["Text"] = $valuedataList; 
					$i++; 
				}
			}
		/*}
		else{
			foreach ($data as $key => $value) {
				$dataList  = $this->xmodel->C0324_ListAgentOneLine($value) ;
				foreach ($dataList["extension"] as $keydataList => $valuedataList) {
					if($extension==$valuedataList){
						$Queue[$valuedataList]["Value"] = $valuedataList ;  
						$Queue[$valuedataList]["Text"] = $valuedataList ; 
						$i++; 
						break;
					}
				}
			}
		} */               
		echo json_encode(array_values($Queue)) ;
	}

	function ListAgents($return = 0) {
		$agents = array();
		$i=0;
		$response = $this->mongo_db->get('chatGroup_Manager');
		foreach ($response as $key => $dataList) {
			foreach ($dataList["agents"] as $keydataList => $valuedataList) {
				$agents[$i] = $valuedataList;   
				$i++; 
			}
		}
        if (empty($return)) {
        	echo json_encode(array_values($agents));
        }else{
        	return array_values($agents);
        }
		
	}

	function ListGroups($return = 0) {
		$agents = array();
		// $i=0;
		$response = $this->mongo_db->get('chatGroup_Manager');
		foreach ($response as $key => $dataList) {
			$agents[] = $dataList['id'];
		}
        if (empty($return)) {
        	echo json_encode(array_values($agents));
        }else{
        	return array_values($agents);
        }
		
	}

	private function ListAgentFromChatGroupManager($id) {
		$response = $this->mongo_db->where_id($id)->getOne('chatGroup_Manager');
		return $response;
	}

	function read()	{
		try {
			$request =  json_decode($this->input->get("q"), TRUE);
			$collection = $this->collection;
			$model = $this->crud->build_model($collection);
	        // Kendo to aggregate
	        $this->load->library("kendo_aggregate", $model);
	        $lookup = array('$lookup' => array(
	        		"from" => $this->asc_collection,
				    "localField" => "statuscode",
				    "foreignField" => "value",
				    "as" => "status"
	        	)
	    	);
	    	$unwind = array('$unwind' => array(
	    			'path'							=> '$status',
			    	'preserveNullAndEmptyArrays'	=> TRUE
	    		)
	    	);
	        $this->kendo_aggregate->set_kendo_query($request)->selecting()->adding($lookup, $unwind)->filtering();
	        // Get total
	        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
	        $total_result = $this->mongo_db->aggregate_pipeline($collection, $total_aggregate);
	        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
	        // Get data
	        
	        $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
	        $data = $this->mongo_db->aggregate_pipeline($collection, $data_aggregate);
	        // Result
        	$response = array("data" => $data, "total" => $total);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
	public function agentsum() {
        if ($this->input->server('REQUEST_METHOD') === 'POST') {           
            header('Content-Type: application/json');
            $request = json_decode(file_get_contents('php://input'));
            $userextension = "";
            $Stime = strtotime($request->from . " 00:00:00");
            $Etime = strtotime($request->to . " 23:59:59");
            $i = 0;
            $data = array();
            // var_dump($request);
            if(!empty($request->agentList)) {
                $agentlist = json_decode(json_encode($request->agentList), true);
            }
            else {
                $agentlist = $this->ListAgents(1);
            }

            $total_conversation_all = 0;
         
            foreach ($agentlist as $key => $agent) {
				$fb_message               = $this->getSumChatByAgent('new_facebook_chat',$agent, $Stime, $Etime);                                                                                                                                                  ;
				$fb_comment               = $this->getSumChatByAgent('new_facebook_comment',$agent, $Stime, $Etime);
				$zalo_message             = $this->getSumChatByAgent('new_zalo_chat',$agent, $Stime, $Etime);
				$livechat_message         = $this->getSumChatByAgent('new_livechat_chat',$agent, $Stime, $Etime);
				$total_conversation       = $fb_message + $fb_comment + $zalo_message + $livechat_message;
				
				$percent_fb_message       = $fb_message>0 ? ($fb_message/$total_conversation)*100 : 0;
				$percent_fb_comment       = $fb_comment>0 ? ($fb_comment/$total_conversation)*100 : 0;
				$percent_zalo_message     = $zalo_message>0 ? ($zalo_message/$total_conversation)*100 : 0;
				$percent_livechat_message = $livechat_message>0 ? ($livechat_message/$total_conversation)*100 : 0;
				
				$data[$key]["agent"]                    = $agent;
				$data[$key]["fb_message"]               = $fb_message;				
				$data[$key]["fb_comment"]               = $fb_comment;
				$data[$key]["zalo_message"]             = $zalo_message;
				$data[$key]["livechat_message"]         = $livechat_message;
				$data[$key]["total_conversation"]       = $total_conversation;


				$data[$key]["percent_fb_message"]       = $percent_fb_message;
				$data[$key]["percent_fb_comment"]       = $percent_fb_comment;
				$data[$key]["percent_zalo_message"]     = $percent_zalo_message;
				$data[$key]["percent_livechat_message"] = $percent_livechat_message;

				$total_conversation_all += $total_conversation;
            }


            $datagrid = array(
                "total" => count($data),
                "data" => array_values($data),
                "total_conversation_all"	=> $total_conversation_all,
                    );
            echo json_encode($datagrid);
        }
    }

    public function getSumChatByAgent($type, $agent, $Stime, $Etime) {
		$pipeline = array(
			array(
				'$match' => array(
					'$and' => array(
						array('from.id' => $agent),
						array('type' => $type),
						array('date_added' => array('$gte' => $Stime, '$lt' => $Etime)),
					)
				)
			),
			array(
				'$group' => array(
					'_id' => null,
					// 'sumduration' => array('$sum' => '$duration'),
					'count' => array('$sum'=> 1)
				)
			)
		);
		$result = $this->mongo_db->aggregate_pipeline('chatGroups', $pipeline);
		// var_dump($result);
		if (isset($result[0]["count"])) {
			return $result[0]["count"];
		} else {
			return 0;
		}


	}

	public function getSumChatByGroup($group_id, $type, $Stime, $Etime) {
		$pipeline = array(
			array(
				'$match' => array(
					'$and' => array(
						array('type' => $type),
						array('group_id' => $group_id),
						array('date_added' => array('$gte' => $Stime, '$lt' => $Etime)),
					)
				)
			),
			array(
				'$group' => array(
					'_id' => null,
					// 'sumduration' => array('$sum' => '$duration'),
					'count' => array('$sum'=> 1)
				)
			)
		);
		$result = $this->mongo_db->aggregate_pipeline('chatGroups', $pipeline);
		// var_dump($result);
		if (isset($result[0]["count"])) {
			return $result[0]["count"];
		} else {
			return 0;
		}


	}

	public function getSumChatByDate($type, $Stime, $Etime) {
		$pipeline = array(
			array(
				'$match' => array(
					'$and' => array(
						array('type' => $type),
						array('date_added' => array('$gte' => $Stime, '$lt' => $Etime)),
					)
				)
			),
			array(
				'$group' => array(
					'_id' => null,
					// 'sumduration' => array('$sum' => '$duration'),
					'count' => array('$sum'=> 1)
				)
			)
		);
		$result = $this->mongo_db->aggregate_pipeline('chatGroups', $pipeline);
		// var_dump($result);
		if (isset($result[0]["count"])) {
			return $result[0]["count"];
		} else {
			return 0;
		}


	}
	/**/
	public function groupagentsum() {
    	if ($this->input->server('REQUEST_METHOD') === 'POST') {           
            header('Content-Type: application/json');
            $request = json_decode(file_get_contents('php://input'));
            $userextension = "";
            $Stime = strtotime($request->from . " 00:00:00");
            $Etime = strtotime($request->to . " 23:59:59");
            $i = 0;
            $data = array();
            // var_dump($request);
            if(!empty($request->groupList)) {
                $group_list = json_decode(json_encode($request->groupList), true);
            }
            else {
                $group_list = $this->ListGroups(1);
            }

            $total_conversation_all = 0;
            foreach ($group_list as $key => $group) {
            	$group_info = $this->mongo_db->where_id($group)->getOne('chatGroup_Manager');
            	$group_name = $group_info['name'];
            	$fb_message = $this->getSumChatByGroup($group,'new_facebook_chat', $Stime, $Etime);                                                                                                                                                  ;
            	$fb_comment = $this->getSumChatByGroup($group,'new_facebook_comment', $Stime, $Etime);
            	$zalo_message = $this->getSumChatByGroup($group,'new_zalo_chat', $Stime, $Etime);
            	$livechat_message = $this->getSumChatByGroup($group,'new_livechat_chat', $Stime, $Etime);
            	$total_conversation = $fb_message + $fb_comment + $zalo_message + $livechat_message;

            	$percent_fb_message       = $fb_message>0 ? ($fb_message/$total_conversation)*100 : 0;
				$percent_fb_comment       = $fb_comment>0 ? ($fb_comment/$total_conversation)*100 : 0;
				$percent_zalo_message     = $zalo_message>0 ? ($zalo_message/$total_conversation)*100 : 0;
				$percent_livechat_message = $livechat_message>0 ? ($livechat_message/$total_conversation)*100 : 0;

            	$data[$key]["group"] = $group_name;
            	$data[$key]["fb_message"] = $fb_message;
            	$data[$key]["fb_comment"] = $fb_comment;
            	$data[$key]["zalo_message"] = $zalo_message;
            	$data[$key]["livechat_message"] = $livechat_message;
            	$data[$key]["total_conversation"] = $total_conversation;

            	$data[$key]["percent_fb_message"]       = $percent_fb_message;
				$data[$key]["percent_fb_comment"]       = $percent_fb_comment;
				$data[$key]["percent_zalo_message"]     = $percent_zalo_message;
				$data[$key]["percent_livechat_message"] = $percent_livechat_message;
				$total_conversation_all += $total_conversation;

			}

			$datagrid = array(
				"total" => count($data),
				"data" => array_values($data),
				"total_conversation_all"	=> $total_conversation_all,
			);
			echo json_encode($datagrid);
		}
	}

	/**/
	public function dateagentsum() {
    	if ($this->input->server('REQUEST_METHOD') === 'POST') {           
            header('Content-Type: application/json');
            $request = json_decode(file_get_contents('php://input'));
            $Stime = strtotime($request->from);
            $Etime = strtotime($request->to);
            /*$Stime = strtotime($request->from . " 00:00:00");
            $Etime = strtotime($request->to . " 23:59:59");*/

            $begin = new DateTime($request->from);
            $end = new DateTime($request->to);

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);
            $data = array();
            $total_conversation_all = 0;
            foreach ($period as $key => $dt) {
            	if ($key>=90) {
            		break;
            	}
            	$Stime = strtotime($dt->format("Y-m-d 00:00:00"));
            	$Etime = strtotime($dt->format("Y-m-d 23:59:59"));

            	$fb_message = $this->getSumChatByDate('new_facebook_chat',$Stime, $Etime);                                                                                                                                                  ;
            	$fb_comment = $this->getSumChatByDate('new_facebook_comment',$Stime, $Etime);
            	$zalo_message = $this->getSumChatByDate('new_zalo_chat',$Stime, $Etime);
            	$livechat_message = $this->getSumChatByDate('new_livechat_chat',$Stime, $Etime);
            	$total_conversation = $fb_message + $fb_comment + $zalo_message + $livechat_message;

            	$percent_fb_message       = $fb_message>0 ? ($fb_message/$total_conversation)*100 : 0;
				$percent_fb_comment       = $fb_comment>0 ? ($fb_comment/$total_conversation)*100 : 0;
				$percent_zalo_message     = $zalo_message>0 ? ($zalo_message/$total_conversation)*100 : 0;
				$percent_livechat_message = $livechat_message>0 ? ($livechat_message/$total_conversation)*100 : 0;


            	$data[$key]["date"] = $dt->format("d/m/Y");
            	$data[$key]["fb_message"] = $fb_message;
            	$data[$key]["fb_comment"] = $fb_comment;
            	$data[$key]["zalo_message"] = $zalo_message;
            	$data[$key]["livechat_message"] = $livechat_message;
            	$data[$key]["total_conversation"] = $total_conversation;

            	$data[$key]["percent_fb_message"]       = $percent_fb_message;
				$data[$key]["percent_fb_comment"]       = $percent_fb_comment;
				$data[$key]["percent_zalo_message"]     = $percent_zalo_message;
				$data[$key]["percent_livechat_message"] = $percent_livechat_message;
				
				$total_conversation_all += $total_conversation;

            }

            $datagrid = array(
            	"total" => count($data),
            	"data" => array_values($data),
            	"total_conversation_all"	=> $total_conversation_all,
            );
            echo json_encode($datagrid);
    	}
	}
}