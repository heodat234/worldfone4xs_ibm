<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Daily_working_days_report extends WFF_Controller {

    private $collection             = "Daily_working_days_report";
    private $ln3206_collection      = "LN3206F";
    private $zaccf_collection       = "ZACCF";
    private $lnjc05_collection      = "LNJC05";
    private $group_team_collection     = "Group";
    private $jsonData_collection     = "Jsondata";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("mongo_db");
        $this->collection           = set_sub_collection($this->collection);
        $this->ln3206_collection    = set_sub_collection($this->ln3206_collection);
        $this->zaccf_collection     = set_sub_collection($this->zaccf_collection);
        $this->lnjc05_collection    = set_sub_collection($this->lnjc05_collection);
        $this->group_team_collection      = set_sub_collection($this->group_team_collection);
        $this->jsonData_collection      = set_sub_collection($this->jsonData_collection);
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

    function save()
    {
        $this->mongo_db->switch_db('_worldfone4xs');
        $groupProducts = $this->mongo_db->where(array('tags'=> ['group', 'debt', 'product'] ))->get($this->jsonData_collection);
        $this->mongo_db->switch_db();
        print_r($groupProducts);
    }

    // function exportExcel()
    // {
    //     shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/exportDailyAssignment.py  > /dev/null &');
    // }
    // function downloadExcel()
    // {
    //   $this->exportExcel();
    //     $file_path = UPLOAD_PATH . "/loan/export/DailyAssignment.xlsx";
    //     echo json_encode(array("status" => 1, "data" => $file_path));
    // }
}