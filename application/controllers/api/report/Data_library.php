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
                        array('source'=> 'CE'),
                        // array('createdAt'=> array( '$gte'=> (string)$start, '$lte'=> (string)$end))
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
                   "localField" => "userextension",
                   "foreignField" => "tl_code",
                   "as" => "appointment_detail"
               )
            );
            $group = array(
               '$group' => array(
                  '_id' => array('code'=>'$source'),
                  'count_data' => array('$sum'=> 1),
                  // 'disposition' => array( '$push'=> '$disposition' ),
                  // 'count_appointment' => array('$addToSet'=> '$count_appointment'),
               )
            );
            // $project = array(
            //    '$project' => array_merge($project, array(
            //       'count_appointment'          => array('$size' => '$appointment_detail'),

            //    ))
            // );
            // var_dump($match);
            $this->kendo_aggregate->set_kendo_query($request)->selecting()->adding($match)->filtering();
            // Get total
            $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
            $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
            $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
            // Get data
            $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
            // foreach ($data as &$value) {
            //    $value['count_appointment'] = $value['count_appointment'][0];
            //    $value['count_success'] = $value['count_dont_pickup']  = 0;
            //    $arr = array_count_values($value['disposition']);
            //    if (isset($arr['ANSWERED'])) {
            //       $value['count_success'] = $arr['ANSWERED'];
            //    }
            //    if (isset($arr['NO ANSWER'])) {
            //       $value['count_dont_pickup'] = $arr['NO ANSWER'];
            //    }
            // }
            // var_dump($data);exit;
            $response = array("data" => $data, "total" => $total);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}