<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Appointment extends WFF_Controller {

    private $collection = "Appointment";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
        $this->permission = $this->data["permission"];
    }
    function index()
    {
      try {
        $request = json_decode($this->input->get("q"), TRUE);

        $config = $this->session->userdata();

        $model = $this->crud->build_model($this->collection);
        $this->load->library("kendo_aggregate", $model);
        $this->kendo_aggregate->set_default("sort", null);

        // PERMISSION
        $match = array();
        if(!in_array("viewall", $this->data["permission"]["actions"])) {
            $extension = $this->session->userdata("extension");
            $this->load->model("group_model");
            $members = $this->group_model->members_from_lead($extension);
            // $match["userextension"] = ['$in' => $members];
            $match = array(
              '$match' => array('tl_code' => ['$in' => $members])
            );
        }

        // if ($config['issupervisor'] || $config['isadmin']) {
        //    $match = array();
        // }
        // else if(!$config['issupervisor'] && !$config['isadmin']){
        //    $match = array(
        //       '$match' => array('assign' => array('$eq' => $config['extension']))
        //    );
        // }
        $group = array(
           '$group' => array(
              '_id' => array('code'=>'$tl_code'),
              "name" => array( '$first' => '$tl_name' ),
              'count_appointment' => array('$sum'=> 1),
              'status' => array( '$push'=> '$status' )
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
             $value['count_applied'] = $value['count_approve'] = $value['count_reject'] = $value['count_release'] = 0;
             $arr = array_count_values($value['status']);
             if (isset($arr['Applied'])) {
                $value['count_applied'] = $arr['Applied'];
             }
             if (isset($arr['Approve'])) {
                $value['count_approve'] = $arr['Approve'];
             }
             if (isset($arr['Reject'])) {
                $value['count_reject'] = $arr['Reject'];
             }
             if (isset($arr['Release'])) {
                $value['count_release'] = $arr['Release'];
             }
          }
          // var_dump($data);exit;
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
            if (isset($request['filter'])) {
               $start = strtotime($request['filter']['filters'][0]['value']);
               $end = strtotime($request['filter']['filters'][1]['value']);
               unset($request['filter']);
            }else{
               $start = time() - 86400*30;
               $end = time();
            }
            $config = $this->session->userdata();

            $model = $this->crud->build_model($this->collection);
            $this->load->library("kendo_aggregate", $model);
            $group = array(
               '$group' => array(
                  '_id' => array('code'=>'$tl_code'),
                  "name" => array( '$first' => '$tl_name' ),
                  'count_appointment' => array('$sum'=> 1),
                  'status' => array( '$push'=> '$status' )
               )
            );
            if ($config['issupervisor']) {
               $match = array(
                  '$match' => array(
                     '$and' => array(
                        array('created_at'=> array( '$gte'=> $start, '$lte'=> $end))
                     )
                  )
               );
            }
            else if(!$config['issupervisor'] && !$config['isadmin']){
               $match = array(
                  '$match' => array(
                     '$and' => array(
                        array('tl_code' => array('$eq' => $config['extension'])),
                        array('created_at'=> array( '$gte'=> $start, '$lte'=> $end))
                     )
                  )
               );
            }

            $this->kendo_aggregate->set_kendo_query($request)->selecting()->adding($match,$group)->filtering();
            // Get total
            $total_aggregate = $this->kendo_aggregate->get_total_aggregate();//  pre($total_aggregate);
            $total_result = $this->mongo_db->aggregate_pipeline($this->collection, $total_aggregate);
            $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
            // Get data
            $data_aggregate = $this->kendo_aggregate->sorting()->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->collection, $data_aggregate);
            foreach ($data as &$value) {
               $value['count_applied'] = $value['count_approve'] = $value['count_reject'] = $value['count_release'] = 0;
               $arr = array_count_values($value['status']);
               if (isset($arr['Applied'])) {
                  $value['count_applied'] = $arr['Applied'];
               }
               if (isset($arr['Approve'])) {
                  $value['count_approve'] = $arr['Approve'];
               }
               if (isset($arr['Reject'])) {
                  $value['count_reject'] = $arr['Reject'];
               }
               if (isset($arr['Release'])) {
                  $value['count_release'] = $arr['Release'];
               }
            }
            // var_dump($data);exit;
            $response = array("data" => $data, "total" => $total);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}