<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Assign extends CI_Controller {

	private $collection = "User";
	private $sub_collection = "Telesalelist";
	private $import_collection = "Import";
	private $jsondata_collection = "Jsondata";
	private $log_collection = "Assign_log";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
		$this->sub_collection = set_sub_collection($this->sub_collection);
		$this->import_collection = set_sub_collection($this->import_collection);
		$this->log_collection = set_sub_collection($this->log_collection);
	}

	function read($id_import)
	{
		$this->crud->select_db($this->config->item("_mongo_db"));
		$users = $this->crud->read($this->collection,array(),array('extension','agentname'));


		$this->mongo_db->switch_db();
		$match['id_import'] = $id_import;
		$match['assign'] = "";
        $response = $this->crud->read($this->sub_collection, $request = array(), array(), $match);
        $import = $this->crud->where_id($id_import)->getOne($this->import_collection);
        if (isset($import['count_fixed'])) {
        	$fixed = $import['count_fixed'];
        }
        if (isset($import['random'])) {
        	$after_random = $import['random'];
        }
        $count_fixed = 0;
        foreach ($users['data'] as &$doc) {
        	$doc['count_detail'] = 0;
        	foreach ($fixed as $key => $value) {
        		if ($doc['extension'] == $key) {
        			$doc['count_detail'] = $value;
        		}
        	}
        	foreach ($after_random as $key_random => $value_random) {
        		if ($doc['extension'] == $key_random) {
        			$doc['after_random'] = $value_random;
        		}
        	}
        	$doc['id_import'] = $id_import;
        	$count_fixed += $doc["count_detail"];
        }

        $count_random = $response['total'];
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
		$random = $data['random'];
		$extension = $data['extension'];
		// $match['id_import'] = $id;
		// $match['assign'] = '';

		exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/python/assign.py ' . $id . " ". $random ." ". $extension ." > /dev/null &");
		$update_data['random.'.$extension] = (int)$random;
		$this->crud->where_id($id)->update($this->import_collection, array('$inc' => $update_data));
  //       $assign_log = $this->crud->getOne($this->log_collection);
  //       $array_cmnd = [];
  //       if ($assign_log != NULL) {
  //       	foreach ($assign_log as $key => $value) {
	 //        	if ($key == $data['extension']) {
	 //        		$array_cmnd = $value;
	 //        	}
	 //        }
	 //        $match['id_no'] = array('$nin' => $array_cmnd);
  //       }
        
		// for ($i=0; $i < $data['random']; $i++) {
		// 	$insert_data["assign"]		= $data['extension'];
		// 	$insert_data["assigned_by"]	= 'BySystemRandom';
		// 	$user = $this->crud->where($match)->getOne($this->sub_collection);
		// 	if ($user != NULL) {
		// 		$this->crud->where_id($user['id'])->update($this->sub_collection, array('$set' => $insert_data));

		// 		if ($assign_log != NULL) {
		// 			$this->crud->where_id($assign_log['id'])->update($this->log_collection, array('$push' => array($data['extension'] => $user['id_no'])));
		// 		}
		// 	}
			
		// }
		echo json_encode(array("status" => -1, "data" => []));
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