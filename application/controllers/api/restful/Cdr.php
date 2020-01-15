<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Cdr extends WFF_Controller {

    /**
     * API restful [worldfonepbxmanager] collection.
     * READ from base_url + api/restful/cdr 
     * DETAIL from base_url + api/restful/cdr/$id 
     */

    private $collection = "worldfonepbxmanager";

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

    function update($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["updatedBy"]  =   $this->session->userdata("extension");
            $result = $this->crud->where_id($id)->update($this->collection, array('$set' => $data));
            echo json_encode(array("status" => $result ? 1 : 0, "data" => []));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}