<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Data_library extends WFF_Controller {

    private $collection = "Datalibrary";
    private $call_collection = "worldfonepbxmanager";
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
                        // array('direction'=> 'outbound'),
                        array('assign' => array('$eq' => $config['extension'])),
                        array('createdAt'=> array( '$gte'=> $start, '$lte'=> $end))
                     )
                  )
               );
            }
            $lookup = array(
               '$lookup' => array(
                  "from" => $this->call_collection,
                   "localField" => "mobile_phone_no",
                   "foreignField" => "customernumber",
                   "as" => "call_detail"
               )
            );
            $lookup_1 = array(
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
                  'count_data' => array('$sum'=> 1),
                  "call_detail" => array( '$push' => '$call_detail' ),
                  "appointment_detail" => array( '$push' => '$appointment_detail' ),
               )
            );
            $project = array(
               '$project' => array_merge($project, array(
                  'call_detail'=> array(
                      '$filter'=> array(
                         'input'=> '$call_detail',
                         'as'=> 'item',
                         'cond'=> array( '$eq'=> [ '$$item.direction', "outbound" ] )
                      )
                   ),
                  
                  'count_data'            => 1,
                  'appointment_detail'        => 1

               ))
            );
            $this->kendo_aggregate->set_kendo_query($request)->selecting()->adding($match,$lookup,$lookup_1,$group,$project)->filtering();
            // Get total
            $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
            $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
            $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
            // Get data
            $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
            foreach ($data as &$value) {
              $call_detail = array_filter($value['call_detail'], function($item) {
                  return $item != [];
              });
              $appointment_detail = array_filter($value['appointment_detail'], function($item) {
                  return $item != [];
              });
              $value['count_appointment'] = count($appointment_detail);
              $value['customernumber'] = [];
              $value['count_success'] = $value['count_dont_pickup']  = 0;
              foreach ($call_detail as $call) {
                  array_push($value['customernumber'], $call['customernumber']);
                  if ($call['disposition'] == 'ANSWERED') {
                    $value['count_success'] ++;
                  }else{
                    $value['count_dont_pickup'] ++;
                  }
              }
              $value['count_called'] = count(array_unique($value['customernumber']));
              
            }
            $response = array("data" => $data, "total" => $total);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}