<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Sibs_solve extends WFF_Controller {

    private $collection = "Sibs";

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
            $match = array();
            $request = json_decode($this->input->get("q"), TRUE);
            $telesaleList = $this->crud->where(array('assign' => $this->session->userdata("extension")))->get(set_sub_collection('Telesalelist'), array('cif', 'last_modified', 'assigned_by', 'id'));
            if(!empty($telesaleList)) {
                $listCIF = array_values(array_unique(array_column($telesaleList, 'cif')));
                $match['cif'] = array(
                    '$in'   => $listCIF
                );
            }
            $response = $this->crud->read($this->collection, $request, array(), $match);
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
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        try {
            $this->load->library("crud");
            $data = json_decode(file_get_contents('php://input'), TRUE);
            $data["created_at"]	= time();
            $data["created_by"]	= $this->session->userdata("extension");
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
            $data["updated_by"]  =   $this->session->userdata("extension");
            $data['updated_at'] = time();
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