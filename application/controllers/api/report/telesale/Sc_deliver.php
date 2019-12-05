<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Sc_deliver extends WFF_Controller {

    private $call_collection = "worldfonepbxmanager";
    private $collection = "Telesalelist";
    private $app_collection = "Appointment";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
        $this->call_collection = set_sub_collection($this->call_collection);
        $this->app_collection = set_sub_collection($this->app_collection);
    }

    function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);

            $config = $this->session->userdata();

            $model = $this->crud->build_model($this->collection);
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_default("sort", null);

            // if ($config['issupervisor'] || $config['isadmin']) {
            //    $match = array();
            // }
            // else if(!$config['issupervisor'] && !$config['isadmin']){
            //    $match = array(
            //       '$match' => array('assign' => array('$eq' => $config['extension']))
            //    );
            // }
            $match = array();
            if(!in_array("viewall", $this->data["permission"]["actions"])) {
                $extension = $this->session->userdata("extension");
                $this->load->model("group_model");
                $members = $this->group_model->members_from_lead($extension);
                $match = array(
                  '$match' => array('assign' => ['$in' => $members])
                );
            }
            $group = array(
               '$group' => array(
                  '_id' => array('code'=>'$source'),
                  "id_no_arr" => array( '$push' => '$id_no' ),
                  "phone_arr" => array( '$push' => '$phone' ),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match, $group);
            // Get total
            $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
            $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
            $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
            // Get data
            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
            foreach ($data as &$value) {              
              $value['count_data'] = !empty($value["phone_arr"]) ? 
              $this->mongo_db->where(
                array("customernumber" => ['$in' => $value["phone_arr"]], "direction" => "outbound")
              )->count($this->call_collection) : 0;
              $value['count_appointment'] = !empty($value["id_no"]) ? $this->mongo_db->where_in("cmnd", $value["id_no_arr"])->count($this->app_collection) : 0;
              unset($value["phone_arr"], $value["id_no_arr"]);
            }
            $response = array("data" => $data, "total" => $total);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function index_old()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);

            $config = $this->session->userdata();

            $model = $this->crud->build_model($this->collection);
            $this->load->library("kendo_aggregate", $model);
            $project = array();
            foreach ($model as $key => $value) {
               $project[$key] = 1;
            }

            if (isset($request['filter'])) {
               $start = strtotime($request['filter']['filters'][0]['value']);
               $end = strtotime($request['filter']['filters'][1]['value']);
               unset($request['filter']);
            }else{
               $start = time() - 86400*30;
               $end = time();
            }
            if ($config['issupervisor']) {
               $match = array(
                  '$match' => array(
                     '$and' => array(
                        array('createdAt'=> array( '$gte'=> $start, '$lte'=> $end))
                     )
                  )
               );
            }
            else if(!$config['issupervisor'] && !$config['isadmin']){
               $match = array(
                  '$match' => array(
                     '$and' => array(
                        array('assign' => array('$eq' => $config['extension'])),
                        array('createdAt'=> array( '$gte'=> $start, '$lte'=> $end))
                     )
                  )
               );
            }
            $lookup_call = array(
               '$lookup' => array(
                  "from" => $this->call_collection,
                   "localField" => "phone",
                   "foreignField" => "customernumber",
                   "as" => "call_detail"
               )
            );
            $lookup = array(
               '$lookup' => array(
                  "from" => $this->app_collection,
                   "localField" => "id_no",
                   "foreignField" => "cmnd",
                   "as" => "appointment_detail"
               )
            );
        
            $group = array(
               '$group' => array(
                  '_id' => array('code'=>'$source'),
                  "appointment_detail" => array( '$push' => '$appointment_detail' ),
                  "call_detail" => array( '$push' => '$call_detail' ),
               )
            );
            $project = array(
               '$project' => array_merge($project, array(
                  'call_detail'=> array(
                      '$filter'=> array(
                         'input'=> '$call_detail',
                         'as'=> 'item',
                         'cond'=> array( '$eq'=> [ '$$item.direction', "outbound" ])
                      )
                   ),
                  'appointment_detail'        => 1

               ))
            );
            $this->kendo_aggregate->set_kendo_query($request)->selecting()->adding($match/*,$lookup_call,$lookup*/,$group)->filtering();
            // Get total
            $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
            $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
            $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
            // Get data
            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
            foreach ($data as &$value) {              
              $call_detail = array_filter($value['call_detail'], function($item) {
                  return $item != [];
              });
              $appointment_detail = array_filter($value['appointment_detail'], function($item) {
                  return $item != [];
              });
              $value['count_data'] = count($call_detail);
              $value['count_appointment'] = count($appointment_detail);
              
            }
            // var_dump($data);exit;
            $response = array("data" => $data, "total" => $total);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}