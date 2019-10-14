<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Config extends CI_Controller {

	/**
     * API restful [Config] collection.
     * READ   from base_url + api/restful/config METHOD GET with FORM request q = kendoQuery
     * DETAIL from base_url + api/restful/config/$id METHOD GET
     * CREATE from base_url + api/restful/config METHOD POST with APPLICATION/JSON request
     * UPDATE from base_url + api/restful/config METHOD PUT with APPLICATION/JSON request
     * DELETE from base_url + api/restful/config/$id METHOD DELETE
     */

	private $collection = "Config";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function read()
	{
		$request = json_decode($this->input->get("q"), TRUE);
		$response = $this->crud->read($this->collection, $request);
		foreach ($response["data"] as &$doc) {
			foreach ($doc as $key => $value) {
				if( in_array($key, ["email_password","sms_password"]) ) {
					$doc["has_" . $key] = (bool) $value;
					unset($doc[$key]);
				}
			}
		}
		echo json_encode($response);
	}

	function detail($id)
	{
		$response = $this->crud->where_id($id)->getOne($this->collection);
		foreach ($response as $key => $value) {
			if( in_array($key, ["email_password","sms_password"]) ) {
				$doc["has_" . $key] = (bool) $value;
				unset($doc[$key]);
			}
		}
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["createdBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $data));
	}

	function update($id)
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["updatedBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
		// Unactive remain
		if(!empty($data["active"])) {
			$this->mongo_db->where(array("_id" => array('$ne' => new MongoDB\BSON\ObjectId($id))))->update_all($this->collection, array('$set' => array('active' => false)));
		}
		echo json_encode(array("status" => $result ? 1 : 0));
	}

	function delete($id)
	{
		$result = $this->crud->where_id($id)->delete($this->collection, TRUE);
		echo json_encode(array("status" => $result ? 1 : 0));
	}
}