<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Assign extends CI_Controller {

	private $collection = "User";
	private $sub_collection = "Telesalelist";
	private $jsondata_collection = "Jsondata";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->sub_collection = set_sub_collection($this->sub_collection);
	}

	function read($id_import)
	{	
		$this->crud->select_db($this->config->item("_mongo_db"));
		$users = $this->crud->read($this->collection,array(),array('extension','agentname'));
		

		$this->mongo_db->switch_db();
		$match['id_import'] = $id_import;
        $response = $this->crud->read($this->sub_collection, $request = array(), array(), $match);

        $count_fiexd = 0;
        foreach ($users['data'] as &$doc) {
        	$doc['count_detail'] = 0;
        	foreach ($response['data'] as $row) {
        		if ($doc['extension'] == $row['assign']) {
        			$doc['count_detail'] +=1;
        		}
        	}
        	$doc['id_import'] = $id_import;
        	$count_fiexd += $doc["count_detail"];
        }

        $count_random = $response['total'] - $count_fiexd;

        foreach ($users['data'] as &$doc) {
        	$doc['count_random'] = $count_random;
        	$doc['checked'] = 0;
        }
        // Result
        $response = array("data" => $users['data'], "total" => $users['total'],"count_random" => $count_random);
		echo json_encode($response);
	}

	function detail($id)
	{
		$this->load->model("language_model");
		$response = $this->crud->where_id($id)->getOne($this->collection);
		$response = $this->language_model->translate($response);
		echo json_encode($response);
	}

	function create()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$data["createdBy"]	=	$this->session->userdata("extension");
		$result = $this->crud->create($this->collection, $data);
		echo json_encode(array("status" => $result ? 1 : 0, "data" => $result));
	}

	function update()
	{
		$data = json_decode(file_get_contents('php://input'), TRUE);
		$id = $data['id_import'];
		$match['id_import'] = $id;
		$match['assign'] = '';

        $response = $this->crud->read($this->sub_collection, $request = array(), array(), $match);
        $response = $response['data'];
		shuffle($response);
		
		for ($i=0; $i < $data['random']; $i++) { 
			$insert_data["assign"]	= $data['extension'];
			$this->crud->where_id($response[$i]['id'])->update($this->sub_collection, array('$set' => $insert_data));
		}
		echo json_encode(array("status" => 1, "data" => []));
	}

	function delete($id)
	{
		$permanent = TRUE;
		$result = $this->crud->where_id($id)->delete($this->collection, $permanent);
		if($result) {
			$this->crud->where_object_id("diallist_id", $id)->delete_all($this->sub_collection, $permanent);
		}
		echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
	}
}