<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Note extends WFF_Controller {

    private $collection = "worldfonepbxmanager";
    private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
        $this->sub = set_sub_collection("");
        $this->collection = $this->sub . $this->collection;
        $this->permission = $this->data["permission"];
	}

    function LO_index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $this->load->library("crud");
            // PERMISSION
            $match = array();
            // if(!in_array("viewall", $this->data["permission"]["actions"])) {
            //     $extension = $this->session->userdata("extension");
            //     $this->load->model("group_model");
            //     $members = $this->group_model->members_from_lead($extension);
            //     $match["userextension"] = ['$in' => $members];
            // }
            $response = $this->crud->read($this->collection, $request, ['direction','endtime','userextension','customernumber','note'], $match);
            foreach ($response['data'] as $key => &$doc) {
                $doc['time_note'] = date('d-m-Y H:i:s', $doc['endtime']) . ' - ' . (isset($doc['note']) ? $doc['note'] : '');
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

}