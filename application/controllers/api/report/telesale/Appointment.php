<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Appointment extends WFF_Controller {

    private $collection = "Appointment";
    private $ts_collection = "Telesalelist";
    private $group_collection = "Group";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->collection = set_sub_collection($this->collection);
        $this->ts_collection = set_sub_collection($this->ts_collection);
        $this->group_collection = set_sub_collection($this->group_collection);
        $this->permission = $this->data["permission"];
    }
    function index()
    {
      try {
        $request = json_decode($this->input->get("q"), TRUE);
        $start = time() - 86400*30;
        $end = time();
        $match_app = array('$gte' => $start, '$lte' => $end);

        if (isset($request['filter'])) {
          $filters = $request['filter'];
          unset($request['filter']);
          foreach ($filters['filters'] as $value) {
            if ($value['operator'] == 'gte') {
              $start = $value['value'];
            }
            if ($value['operator'] == 'lte') {
              $end = $value['value'];
            }
          }
          $match_app = array('$gte' =>strtotime($start), '$lte' => strtotime($end));
        }
        
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
              '$match' => array('assign' => ['$in' => $members])
            );
        }

        $group = array(
           '$group' => array(
              '_id' => array('code'=>'$assign'),
              "name" => array( '$first' => '$assign_name' ),
              'cif_arr' => array( '$push'=> '$cif' )
           )
        );
        $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match,$group);
        // Get total
        $total_aggregate = $this->kendo_aggregate->get_total_aggregate();
        $total_result = $this->mongo_db->aggregate_pipeline($this->ts_collection, $total_aggregate);
        $total = isset($total_result[0]) ? $total_result[0]['total'] : 0;
        // Get data
        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
        $data = $this->mongo_db->aggregate_pipeline($this->ts_collection, $data_aggregate);
        foreach ($data as &$value) {
          $teams = isset($value["_id"]['code']) ? $this->mongo_db->where(
              array("members" => ['$regex' => $value["_id"]['code']])
            )->getOne($this->group_collection) : array();
          $value['team'] = !empty($teams) ? $teams['name'] : '';
           $value['count_appointment'] = !empty($value["cif_arr"]) ? $this->mongo_db->where(
              array("cif" => ['$in' => $value["cif_arr"]],'created_at' => $match_app)
            )->count($this->collection) : 0;
           $value['count_applied'] = !empty($value["cif_arr"]) ? $this->mongo_db->where(
              array("cif" => ['$in' => $value["cif_arr"]], 'status' => 'Applied','created_at' => $match_app)
            )->count($this->collection) : 0;
           $value['count_approve'] = !empty($value["cif_arr"]) ? $this->mongo_db->where(
              array("cif" => ['$in' => $value["cif_arr"]], 'status' => 'Approve','created_at' => $match_app)
            )->count($this->collection) : 0;
           $value['count_reject'] = !empty($value["cif_arr"]) ? $this->mongo_db->where(
              array("cif" => ['$in' => $value["cif_arr"]], 'status' => 'Reject','created_at' => $match_app)
            )->count($this->collection) : 0;
           $value['count_release'] = !empty($value["cif_arr"]) ? $this->mongo_db->where(
              array("cif" => ['$in' => $value["cif_arr"]], 'status' => 'CompleteRelease','created_at' => $match_app)
            )->count($this->collection) : 0;
        
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