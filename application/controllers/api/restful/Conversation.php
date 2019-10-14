<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Conversation extends WFF_Controller {

	private $collection = "chatGroups";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function read1()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			$response = $this->crud->read($this->collection, $request);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function detail($id)
	{
		try {
			$response = $this->crud->where_id($id)->getOne($this->collection);
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function create()
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["createdBy"]	=	$this->session->userdata("extension");

			$index_collecion = "Index";
			$this->mongo_db->where(array("collection" => $this->collection))->update($index_collecion, array('$inc' => array("index" => 1)), array("upsert" => true));
			$indexDoc = $this->mongo_db->where(array("collection" => $this->collection))->order_by(array("index" => 1))->getOne($index_collecion);
			$data["ticket_id"] = "#TCK" . (isset($indexDoc["index"]) ? $indexDoc["index"] : 1);
			$data["reply"] = 0;
			$result = $this->crud->create($this->collection, $data);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => [$result]));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function update($id)
	{
		try {
			$data = json_decode(file_get_contents('php://input'), TRUE);
			$data["updatedBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function delete($id)
	{
		try {
			$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
			echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
	
	function read() {

        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $customer_info = $this->mongo_db->where_id($request['id'])/*where(array('_id'  => $request['id']))*/->getOne(set_sub_collection('Customer'));
            $people_id_filter = array();
            if (isset($customer_info['socials'])) {
                foreach ($customer_info['socials'] as $social) {
                    $people_id_filter[] = array("field" => "to.id", "operator" => "eq", "value" => $social['people_id']);

                }
            }
            if ($people_id_filter) {
                $filterPeople = array(
                    "logic"     => "or",
                    "filters"   => $people_id_filter,
                );
                $request["filter"]["logic"] = 'and';
                $request["filter"]["filters"][] = $filterPeople;
                $response = $this->crud->read('chatGroups', $request);
                foreach ($response['data'] as $key => $value) {
                    $page_info = $this->mongo_db->where(array('id'  => $value['page_id']))->getOne('pageapps');
                    if ($page_info) {
                        $response['data'][$key]['page_name'] = $page_info['name'];
                    }else{
                        $response['data'][$key]['page_name'] = '';
                    }
                }
            }else{
                $response['data'] = array();
                $response['total'] = 0;
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }


        /*header('Content-Type: application/json');
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        $request = json_decode(file_get_contents('php://input'));
        $pipelineData = array();
        $pipelineTotal = array();
        $data = array();
        $queryString = array();
*/
        /*$queryString = array(
            'date_added' => array(
                '$gte'   => strtotime($request->start . ' 00:00:00'),
                '$lte'   => strtotime($request->end . ' 23:59:59')
            )
        );

        if(!empty($request->groups[0]) && empty($request->agents[0])) {
            $queryString['group_id'] = array(
                '$in' => $request->groups
            );
        }

        if(!empty($request->agents[0])) {
            $queryString['group_id'] = array(
                '$in' => $request->groups
            );
            $queryString['from.id'] = array(
                '$in' => $request->agents
            );
        }*/

        /*array_push($pipelineData, array('$match' => $queryString), array('$skip' => ($request->page - 1) * $request->pageSize), array('$limit' => $request->pageSize));
        array_push($pipelineTotal, array('$match' => $queryString), array('$count' => "total"));

        $data['data'] = $this->mongo_db->aggregate_pipeline('chatGroups', $pipelineData);
        $total = $this->mongo_db->aggregate_pipeline('chatGroups', $pipelineTotal);

        $data['total'] = (!empty($total[0])) ? $total[0]['total'] : 0;

        $listPageNameTemp = $this->chathistory_model->getListPageName();
        $listPageName = array();
        $listGroupId = array();

        if(!empty($listPageNameTemp)) {
            $listPageName = array_column($listPageNameTemp, 'name', 'id');
            $listGroupId = array_column($listPageNameTemp, 'group_id', 'id');
        }

        foreach ($data['data'] as $key => &$value) {
            if(!empty($listPageName) && !empty($value['page_id'])) {
                $value['page_name'] = (!empty($listPageName[$value['page_id']])) ? $listPageName[$value['page_id']] : '';
                $chatManagermentInfo = $this->chathistory_model->getChatGroupManagerById($listGroupId[$value['page_id']]);
                $value['supervisor'] = $chatManagermentInfo[0]['supervisor'];
            }
        }
        echo json_encode($data);*/
    }

    function getGroup() {
        header('Content-Type: application/json');
        if(!empty($this->is_admin)) {
            $data = $this->chathistory_model->getAllGroup();
        }
        else {
            $data = $this->chathistory_model->getGroupBySupervisorOrAgent($this->extension);
        }
        echo json_encode($data);
    }

    function getListAgent() {
        header('Content-Type: application/json');
        $listAgent = array();
        $groupId = $this->input->post('groupId');
        if(!empty($groupId[0])) {
            if(!empty($this->is_admin) || !empty($this->issupervisor)) {
                foreach ($groupId as $key => $value) {
                    $temp = $this->mongo_db->where(array('_id' => new mongoId($value)))->getOne('chatGroup_Manager');
                    $listAgentTemp = array_merge(array($temp['supervisor']), $temp['agents']);
                    $listAgent = array_merge($listAgent, $listAgentTemp);
                }
            }
            else {
                $listAgent = array($this->extension);
            }
        }
        else {
            if(!empty($this->is_admin)) {
                $temp = $this->mongo_db->get('chatGroup_Manager');
            }
            elseif(!empty($this->issupervisor)) {
                $temp = $this->mongo_db->where(array('supervisor' => $this->extension))->get('chatGroup_Manager');
            }
            else {
                $temp = $this->mongo_db->where(array('agents' => $this->extension))->get('chatGroup_Manager');
            }
            foreach ($temp as $key => $value) {
                $listAgentTemp = array_merge(array($value['supervisor']), $value['agents']);
                $listAgent = array_merge($listAgent, $listAgentTemp);
            }
        }
        $listAgent = array_values(array_unique($listAgent));
        echo json_encode($listAgent);
    }
    /**/
}