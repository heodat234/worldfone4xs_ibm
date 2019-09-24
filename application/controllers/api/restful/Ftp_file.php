<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Ftp_file extends WFF_Controller {

	private $collection = "Follow_up";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->load->library("crud");
		$this->collection = set_sub_collection($this->collection);
	}

	function read()
	{
		try {
			$request = json_decode($this->input->get("q"), TRUE);

			$arr = array();
			$path = FCPATH.'upload\users\import\\';

	        $items = array_diff(scandir($path), array('..', '.'));
	        foreach ($items as $name) {
	                $row['file_path'] = $path . $name;
	                $file_info = new SplFileInfo($row['file_path']);
	                $row['file_name'] = $file_info->getFilename();
	                $ext = $file_info->getExtension();
	                array_push($arr, $row);
	        }
	        $response = array('data'=> $arr, 'total' => count($arr));
			echo json_encode($response);
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	
	function import()
	{
		try {
			$file_path = $this->input->post('file_path');
			var_dump($file_path);exit;
			$data["updatedBy"]	=	$this->session->userdata("extension");
			$result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
			echo json_encode(array("status" => $result ? 1 : 0));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	
}