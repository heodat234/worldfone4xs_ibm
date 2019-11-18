<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Daily_all_user_report extends WFF_Controller {

    private $lnjc05_collection = "LNJC05";
    private $zaccf_collection = "ZACCF";
    private $sbv_collection = "SBV";
    private $collection = "Loan_group_report";
    private $group_collection = "Group_card";
    private $cdr_collection = "worldfonepbxmanager";
    private $group_team_collection = "Group";
    private $user_collection = "User";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->lnjc05_collection = set_sub_collection($this->lnjc05_collection);
        $this->zaccf_collection = set_sub_collection($this->zaccf_collection);
        $this->sbv_collection = set_sub_collection($this->sbv_collection);
        $this->collection = set_sub_collection($this->collection);
        $this->group_collection = set_sub_collection($this->group_collection);
        $this->cdr_collection = set_sub_collection($this->cdr_collection);
        $this->group_team_collection = set_sub_collection($this->group_team_collection);
        $this->user_collection = set_sub_collection($this->user_collection);
    }

    function weekOfMonth($dateString) {
      list($year, $month, $mday) = explode("-", $dateString);
      $firstWday = date("w",strtotime("$year-$month-1"));
      return floor(($mday + $firstWday - 1)/7) + 1;
    }

    function save()
    {
        try {
            $now =getdate();
            $week = $this->weekOfMonth(date('Y-m-d'));

            //sibs
            $this->mongo_db->switch_db('_worldfone4xs');
            $users = $this->mongo_db->where(array('active' => true  ))->select(array('extension','agentname'))->get($this->user_collection);
            $this->mongo_db->switch_db();
            
            $request = json_decode($this->input->get("q"), TRUE);
            $model = $this->crud->build_model($this->lnjc05_collection);
            $this->load->library("kendo_aggregate", $model);
            $this->kendo_aggregate->set_default("sort", null);

            // $match = array(
            //   '$match' => array('W_ORG' => array('$gt' => 0))
            // );
            $group = array(
               '$group' => array(
                  '_id' => '$group_id',
                  'officer_id_arr' => array('$push'=> '$officer_id'),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($group);

            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
            $group_officer = array(
               '$group' => array(
                  '_id' => '$officer_id',
                  'phone_arr' => array('$push'=> '$mobile_num'),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($group_officer);

            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data_officer = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);

            $new_data = array();
            foreach ($data as &$value) {
                $value['group'] = substr($value['_id'], 0,1);
                $new_data[$value['group']]['count'] = 0;
                $new_data[$value['group']]['officer_id_arr'] = array();
                $new_data[$value['group']]['value'] = array();
                $value['officer_id_arr'] = $value['officer_id_arr'];

            }
            foreach ($data as &$value) {
               $gr = $value['group'];
               if ($gr == 'A') {
                  $new_data[$gr]['group'] = $gr;
                  $new_data[$gr]['count'] += $value['count_data'];
                  $new_data[$gr]['officer_id_arr'] = array_merge($new_data[$gr]['officer_id_arr'],$value['officer_id_arr']);
               }else {
                  $new_data[$gr]['group'] = $gr;
                  $new_data[$gr]['count'] += $value['count_data'];
                  array_push($new_data[$value['group']]['value'],$value);
               }


            }
            foreach ($new_data as &$value) {
               if ($value['group'] == 'A') {
                  $value['teams'] = $this->mongo_db->where(array('name' => array('$regex' => 'SIBS/Group A')  ))->select(array('name','members','lead'))->get($this->group_team_collection);
                  $value['count_officer'] = array_count_values($value['officer_id_arr']);
                  $team['unwork'] = isset($phone['phone_arr']) ? $this->mongo_db->where(array("userextension" => ['$in' => $team['members']]))->count($this->cdr_collection) : 0;
                  foreach ($value['teams'] as &$team) {
                     foreach ($value['count_officer'] as $key => $row) {
                        if ('JIVF00'.$team['lead'] == $key) {
                           $team['count_acc'] = $row;
                        }
                     }
                     foreach ($team['members'] as $member) {
                        foreach ($users as $user) {
                           if ($member == $user['extension']) {
                              $team[$member]['name'] = $user['agentname'];
                              $team[$member]['extension'] = $member;
                              $team[$member]['count_acc'] = round( $team['count_acc']/count($team['members']),2);
                              $team[$member]['unwork'] = $this->mongo_db->where(array("userextension" => $member, 'disposition' =>array('$ne' => 'ANSWERED')))->count($this->cdr_collection);

                              $model = $this->crud->build_model($this->cdr_collection);
                              $this->load->library("kendo_aggregate", $model);
                              $this->kendo_aggregate->set_default("sort", null);

                              $match_cdr = array(
                                '$match' => array(
                                    '$and' => array(
                                       // array('createdAt'=> array( '$gte'=> $start, '$lte'=> $end))
                                       array('userextension' => '911')
                                    )
                                 )
                              );
                              $group_cdr = array(
                                 '$group' => array(
                                    '_id' => null,
                                    'talk_time' => array('$sum'=> '$billduration'),
                                    'total_call' =>array('$sum' => 1),
                                    'customernumber' => array('$push'=> '$customernumber'),
                                    'disposition_arr' => array('$push'=> '$disposition'),
                                 )
                              );
                              $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_cdr,$group_cdr);
                              $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                              $data_cdr = $this->mongo_db->aggregate_pipeline($this->cdr_collection, $data_aggregate);
                              
                              $count_spin = $count_ans = 0;
                              $arr_spin = $answer_arr = [];
                              if (isset($data_cdr[0])) {
                                 //contract
                                 $team[$member]['talk_time'] = $data_cdr[0]['talk_time'];
                                 $team[$member]['total_call'] = $data_cdr[0]['total_call'];

                                 $arr_unique_phone = array_values(array_unique($data_cdr[0]['customernumber']));
                                 $match_ct = array(
                                   '$match' => array('mobile_num' => ['$in' => $arr_unique_phone])
                                 );
                                 $group_ct = array(
                                    '$group' => array(
                                       '_id' => null,
                                       'total_amount' => array('$sum'=> '$current_balance'),
                                    )
                                 );
                                 $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_ct,$group_ct);
                                 $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                 $data_ct = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                                 $team[$member]['total_amount'] = isset($data_ct[0]) ? $data_ct[0]['total_amount'] : 0;

                                 //spin
                                 $arr_count_phone = array_count_values($data_cdr[0]['customernumber']);
                                 foreach ($arr_count_phone as $key_phone => $value_phone) {
                                    if ($value_phone > 1) {
                                       $count_spin ++;
                                       array_push($arr_spin, $key_phone);
                                    }
                                 }
                                 $team[$member]['count_spin'] = $count_spin;
                                 $match_spin = array(
                                   '$match' => array('mobile_num' => ['$in' => $arr_spin])
                                 );
                                 $group_spin = array(
                                    '$group' => array(
                                       '_id' => null,
                                       'spin_amount' => array('$sum'=> '$current_balance'),
                                    )
                                 );
                                 $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_spin,$group_spin);
                                 $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                 $data_ct = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                                 $team[$member]['spin_amount'] = isset($data_ct[0]) ? $data_ct[0]['spin_amount'] : 0;

                                 //connected
                                 foreach ($data_cdr[0]['disposition_arr'] as $key_dis => $disposition) {
                                    if ($disposition == 'ANSWERED') {
                                       $count_ans ++;
                                       array_push($answer_arr, $data_cdr[0]['customernumber'][$key_dis]);
                                    }
                                 }
                                 $team[$member]['count_conn'] = $count_ans;
                                 $match_conn = array(
                                   '$match' => array('mobile_num' => ['$in' => array_values(array_unique($answer_arr))])
                                 );
                                 $group_conn = array(
                                    '$group' => array(
                                       '_id' => null,
                                       'conn_amount' => array('$sum'=> '$current_balance'),
                                    )
                                 );
                                 $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_conn,$group_conn);
                                 $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                 $data_conn = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                                 $team[$member]['conn_amount'] = isset($data_conn[0]) ? $data_conn[0]['conn_amount'] : 0;
                              }
                              
                              
                           }
                        }
                     }                     
                     unset($team['members']);
                  }

                  unset($value['officer_id_arr'],$value['count_officer'],$value['value']);
               }else{
                  unset($value['officer_id_arr']);
                  foreach ($value['value'] as &$row) {
                     $row['count_officer'] = array_count_values($row['officer_id_arr']);
                     unset($row['officer_id_arr']);
                     // $row['officer_id_arr'] = array_unique($row['officer_id_arr']);
                     foreach ($row['count_officer'] as $key => $officer) {
                        foreach ($data_officer as $row_1) {
                           if ($row_1['_id'] == $key) {
                              // $value[$key]['account_arr'] = $row['account_arr'];
                              $value[$key]['extension'] = substr($key,-4);
                              $value[$key]['count_acc'] = $officer;
                              $value[$key]['unwork'] = isset($row['account_arr']) ? $this->mongo_db->where(array("_id" => ['$in' => $row_1['account_arr']]))->count($this->cdr_collection) : 0;
                           }
                        }

                     }
                     unset($row['count_officer']);
                  }
                  unset($value['value']);

               }

               
            }
            print_r($new_data['A']);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

   function countOfExtension($countData,$members)
   {
      $countMember = count($members);

   }

    function downloadExcel()
    {
        $file_path = $this->exportExcel();
        // $file_path = UPLOAD_PATH . "loan/export/CARD_LOAN_GROUP_REPORT_DAILY.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}