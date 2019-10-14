<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class File extends WFF_Controller {

	private $collection = "File";
	private $collection_attach = "Attachment";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->collection_attach = set_sub_collection($this->collection_attach);
	}

	function read()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);
			if(!$this->session->userdata("isadmin")) {
				$extension = $this->session->userdata("extension");
				$filterCreatedBy = array(
					"logic" => "and",
					"filters" => array(
						array("field" => "createdBy", "operator" => "eq", "value" => $extension)
					)
				);
				if(isset($request["filter"])) {
					$request["filter"]["logic"] = "and";
					$request["filter"]["filters"][] = $filterCreatedBy;
				} else {
					$request["filter"] = array();
					$request["filter"]["logic"] = "and";
					$request["filter"]["filters"] = array();
					$request["filter"]["filters"][] = $filterCreatedBy;
				}
			}
			$response = $this->crud->read($this->collection_attach, $request);
			if($response["total"] < $request["take"]) {
				$request["take"] = $request["take"] - $response["total"];
				$response2 = $this->crud->read($this->collection, $request);
				$response["data"] = array_merge($response["data"], $response2["data"]);
				$response["total"] += $response2["total"];
			}
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