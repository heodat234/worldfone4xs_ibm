<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Lawsuit_report extends WFF_Controller {

    private $collection = "Lawsuit";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
    }

    function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
    function saveAsExcel()
    {
        try {
            $request    = $this->input->post();
            $start      = strtotime($request['startDate']);
            $end        = strtotime(str_replace('/', '-', $request['endDate'])) ;
                      
            $match = array(
                     '$and' => array(
                        array('created_at'=> array( '$gte'=> $start, '$lte'=> $end))
                     )               
                 );
            $response = $this->crud->read($this->collection, $request,'',$match);
            var_dump($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}