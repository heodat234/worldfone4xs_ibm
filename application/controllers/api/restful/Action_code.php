<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Action_code extends WFF_Controller {

	private $collection = "Action_code";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->sub = set_sub_collection();
		$this->collection = $this->sub . $this->collection;
	}

	function read()
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
			$result = $this->crud->create($this->collection, $data);
			// Xu ly chien dich cheo
			/*if(isset($data["LIC_NO"]) && isset($data["account_number"]) && isset($data["action_code"])) {
				$doc = $this->mongo_db->where("LIC_NO", $data["LIC_NO"])
				->where("account_number", $data["account_number"])
				->where_gt("createdAt", strtotime('today midnight'))
				->getOne( $this->sub . "Diallist_detail" );

				if($doc) {
					$this->mongo_db->where_id($doc["id"])
					->set("action_code", $data["action_code"])
					->update( $this->sub . "Diallist_detail" );

					switch ($data["action_code"]) {
						case 'value':
							$this->mongo_db->where("diallistdetail_id", $doc["id"])->
							set('called', true)->
							update($this->sub . "Dial_queue");
							break;
						
						default:
							# code...
							break;
					}
				}
			}
			$this->mongo_db->where("")->get($this->sub . "Diallist_detail");*/
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
}