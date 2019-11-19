<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
Class Daily_all_user_report extends WFF_Controller {

    private $collection = "Daily_all_user_report";
    private $lnjc05_collection = "LNJC05";
    private $zaccf_collection = "ZACCF";
    private $sbv_collection = "SBV";
    private $group_collection = "Group_card";
    private $cdr_collection = "worldfonepbxmanager";
    private $group_team_collection = "Group";
    private $user_collection = "User";
    private $ln3206_collection = "LN3206F";
    private $duedate_collection = "Report_due_date";

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
        $this->ln3206_collection = set_sub_collection($this->ln3206_collection);
        $this->duedate_collection = set_sub_collection($this->duedate_collection);
    }

    function weekOfMonth($dateString) {
      list($year, $month, $mday) = explode("-", $dateString);
      $firstWday = date("w",strtotime("$year-$month-1"));
      return floor(($mday + $firstWday - 1)/7) + 1;
    }

    function save()
    {
        try {
            $now = getdate();
            $month = '9';
            $date = $now[0];
            $due_date = $this->mongo_db->where(array('due_date_add_1' => $date  ))->select(array('due_date'))->getOne($this->duedate_collection);

            // var_dump($month);exit;
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
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($group);

            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
            $group_officer = array(
               '$group' => array(
                  '_id' => '$officer_id',
                  'account_arr' => array('$push'=> '$account_number'),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($group_officer);

            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data_officer = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);

            $new_data = array();
            foreach ($data as &$value) {
               $gr = substr($value['_id'], 0,1);
               if ($gr == 'A') {
                  $new_data[$gr][0]['group'] = $gr;
               }else {
                  $new_data[$gr][0]['group'] = $gr;
                  array_push($new_data[$gr],$value);
               }

            }
            $insertData = [];
            foreach ($new_data as $key => &$value) {
               if ($key == 'A') {
                  $teams = $this->mongo_db->where(array('name' => array('$regex' => 'SIBS/Group A')  ))->select(array('name','members','lead'))->get($this->group_team_collection);
                  $i = 1;
                  foreach ($teams as &$row) {
                     $temp = [];
                     $temp['name']    = $row['name'];
                     $temp['group']   = $key;
                     $temp['team']    = $i;
                     $temp['team_lead']  = true;
                     $temp['date']       = $date;

                     if ($due_date ==null) {
                        $debt_groups = substr($row['debt_groups'][0], 1,2);
                        $due_date = $this->mongo_db->where(array('for_month' => (string)$month,'debt_group' => (string)$debt_groups  ))->select(array('due_date','due_date_add_1'))->getOne($this->duedate_collection);
                        $due_date_add_1 = $due_date['due_date_add_1'];
                        $result = $this->mongo_db->where(array('date' => $due_date_add_1,'extension' => $row['lead']  ))->select(array('count_data'))->getOne($this->collection);

                        $temp['count_data'] = $result['count_data'];
                        $temp['unwork'] = $this->mongo_db->where(array("userextension" => $row['lead'], 'disposition' =>array('$ne' => 'ANSWERED'), 'starttime' =>array( '$gte'=> $due_date_add_1, '$lte'=> $date)))->count($this->cdr_collection);
                        $match_cdr = array(
                          '$match' => array(
                              '$and' => array(
                                 array('starttime'=> array( '$gte'=> $due_date_add_1, '$lte'=> $date)),
                                 array('userextension' => $member)
                              )
                           )
                        );
                     }else{
                        $duedate = (string)(int)date('dmy',$due_date['due_date']);
                        $temp['count_data'] = $this->mongo_db->where(array("officer_id" => 'JIVF00'.$row['lead'], 'due_date' => $duedate ))->count($this->lnjc05_collection);
                        $temp['unwork'] = isset($phone['phone_arr']) ? $this->mongo_db->where(array("userextension" => ['$in' => $row['members']],  'disposition' =>array('$ne' => 'ANSWERED'), , 'starttime' =>array( '$gte'=> $date) ))->count($this->cdr_collection) : 0;
                        $match_cdr = array(
                          '$match' => array(
                              '$and' => array(
                                 array('starttime'=> array( '$gte'=> $date)),
                                 array('userextension' => ['$in' => $row['members']])
                              )
                           )
                        );
                     }
                     
                     $temp['talk_time'] = $temp['total_call'] = $temp['total_amount'] = $temp['count_spin'] = $temp['spin_amount'] = $temp['count_conn'] = $temp['conn_amount'] = $temp['count_paid'] = $temp['paid_amount'] = 0;

                     

                     
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
                     if (isset($data_cdr[0])) 
                     {
                        $temp['talk_time'] = $data_cdr[0]['talk_time'];

                        //contact
                        $temp['total_call'] = $data_cdr[0]['total_call'];
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
                        $temp['total_amount'] = isset($data_ct[0]) ? $data_ct[0]['total_amount'] : 0;

                        //spin
                        $count_spin = 0;
                        $arr_spin = [];
                        $arr_count_phone = array_count_values($data_cdr[0]['customernumber']);
                        foreach ($arr_count_phone as $key_phone => $value_phone) {
                           if ($value_phone > 1) {
                              $count_spin ++;
                              array_push($arr_spin, $key_phone);
                           }
                        }
                        $temp['count_spin'] = $count_spin;
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
                        $temp['spin_amount'] = isset($data_ct[0]) ? $data_ct[0]['spin_amount'] : 0;

                        //connected
                        $count_ans = 0;
                        $answer_arr = [];
                        foreach ($data_cdr[0]['disposition_arr'] as $key_dis => $disposition) {
                           if ($disposition == 'ANSWERED') {
                              $count_ans ++;
                              array_push($answer_arr, $data_cdr[0]['customernumber'][$key_dis]);
                           }
                        }
                        $temp['count_conn'] = $count_ans;
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
                        $temp['conn_amount'] = isset($data_conn[0]) ? $data_conn[0]['conn_amount'] : 0;

                        //paid
                        foreach ($data_officer as $office) {
                           if ($office['_id'] == 'JIVF00'.$row['lead']) {
                              $match_paid = array(
                                '$match' => array('account_number' => ['$in' => $office['account_arr']])
                              );
                              $group_paid = array(
                                 '$group' => array(
                                    '_id' => null,
                                    'paid_amount' => array('$sum'=> '$amt'),
                                    'count_paid'  => array('$sum' => 1)
                                 )
                              );
                              $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_paid,$group_paid);
                              $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                              $data_paid = $this->mongo_db->aggregate_pipeline($this->ln3206_collection, $data_aggregate);
                              $temp['count_paid'] = isset($data_paid[0]) ? $data_paid[0]['count_paid'] : 0;
                              $temp['paid_amount'] = isset($data_paid[0]) ? $data_paid[0]['paid_amount'] : 0;

                           }
                        }
                     }
                     array_push($insertData, $temp);

                     $temp_member = [];
                     $count_member = count($row['members']);
                     foreach ($row['members'] as $member) {
                        foreach ($users as $user) {
                           if ($member == $user['extension']) {
                              $temp_member['name'] = $user['agentname'];
                           }
                        }
                        $temp_member['extension'] = $member;
                        $temp_member['group'] = $key;
                        $temp_member['team'] = $i;
                        $temp_member['date'] = $date;
                        $temp_member['unwork'] = round($temp['unwork']/$count_member,2);
                        $temp_member['talk_time'] = round($temp['talk_time']/$count_member,2);
                        $temp_member['total_call'] = round($temp['total_call']/$count_member,2);
                        $temp_member['total_amount'] = round($temp['total_amount']/$count_member,2);
                        $temp_member['count_spin'] = round($temp['count_spin']/$count_member,2);
                        $temp_member['spin_amount'] = round($temp['spin_amount']/$count_member,2);
                        $temp_member['count_conn'] = round($temp['count_conn']/$count_member,2);
                        $temp_member['conn_amount'] = round($temp['conn_amount']/$count_member,2);
                        $temp_member['count_paid'] = round($temp['count_paid']/$count_member,2);
                        $temp_member['paid_amount'] = round($temp['paid_amount']/$count_member,2);
                        array_push($insertData, $temp_member);
                     }
                     $i++;
                     
                  }
              

               }else{
                  $i = 1;
                  foreach ($value as &$row) {
                     if (isset($row['_id'])) {
                        $temp = [];
                        $team = $this->mongo_db->where(array('debt_groups' => $row['_id'] ))->select(array('name','members','lead','debt_groups'))->getOne($this->group_team_collection);

                        $temp['name']       = $row['_id'];
                        $temp['group']      = $key;
                        $temp['team']       = $i;
                        $temp['team_lead']  = true;
                        $temp['date']       = $date;
                        // $temp['count_data'] = $row['count_data'];
                        $temp['unwork'] = $temp['talk_time'] = $temp['total_call'] = $temp['total_amount'] = $temp['count_spin'] = $temp['spin_amount'] = $temp['count_conn'] = $temp['conn_amount'] = $temp['count_paid'] = $temp['paid_amount'] = 0;
                        foreach ($team['members'] as $member) {
                           $temp_member = [];
                           foreach ($users as $user) {
                              if ($member == $user['extension']) {
                                 $temp_member['name']   = $user['agentname'];
                                 $temp_member['extension']   = $member;
                                 $temp_member['group']  = $key;
                                 $temp_member['team']   = $i;
                                 $temp_member['date']   = $date;
                                 if ($member == '0340') {
                                    $member_jc05 = 'JIVF00P340';
                                 }else{
                                    $member_jc05 = 'JIVF00'.$member;
                                 }

                                 if ($due_date == null) {
                                    $debt_groups = substr($team['debt_groups'][0], 1,2);
                                    $due_date = $this->mongo_db->where(array('for_month' => (string)$month,'debt_group' => (string)$debt_groups  ))->select(array('due_date','due_date_add_1'))->getOne($this->duedate_collection);
                                    $due_date_add_1 = $due_date['due_date_add_1'];
                                    $result = $this->mongo_db->where(array('date' => $due_date_add_1,'extension' => $member  ))->select(array('count_data'))->getOne($this->collection);

                                    $temp_member['count_data'] = $result['count_data'];
                                    $temp_member['unwork'] = $this->mongo_db->where(array("userextension" => $member, 'disposition' =>array('$ne' => 'ANSWERED'), 'starttime' =>array( '$gte'=> $due_date_add_1, '$lte'=> $date)))->count($this->cdr_collection);
                                    $match_cdr = array(
                                      '$match' => array(
                                          '$and' => array(
                                             array('starttime'=> array( '$gte'=> $due_date_add_1, '$lte'=> $date)),
                                             array('userextension' => $member)
                                          )
                                       )
                                    );

                                 }else{
                                    $duedate = (string)(int)date('dmy',$due_date['due_date']);
                                    $temp_member['count_data'] = $this->mongo_db->where(array("officer_id" => $member_jc05, 'due_date' => $duedate ))->count($this->lnjc05_collection);
                                    $temp_member['unwork'] = $this->mongo_db->where(array("userextension" => $member, 'disposition' =>array('$ne' => 'ANSWERED'),'starttime' => array('$gte' => $date) ))->count($this->cdr_collection);
                                 }

                                 $model = $this->crud->build_model($this->cdr_collection);
                                 $this->load->library("kendo_aggregate", $model);
                                 $this->kendo_aggregate->set_default("sort", null);

                                 
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
                                    $temp_member['talk_time']    = $data_cdr[0]['talk_time'];
                                    $temp_member['total_call']   = $data_cdr[0]['total_call'];

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
                                    $temp_member['total_amount'] = isset($data_ct[0]) ? $data_ct[0]['total_amount'] : 0;

                                    //spin
                                    $arr_count_phone = array_count_values($data_cdr[0]['customernumber']);
                                    foreach ($arr_count_phone as $key_phone => $value_phone) {
                                       if ($value_phone > 1) {
                                          $count_spin ++;
                                          array_push($arr_spin, $key_phone);
                                       }
                                    }
                                    $temp_member['count_spin'] = $count_spin;
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
                                    $temp_member['spin_amount'] = isset($data_ct[0]) ? $data_ct[0]['spin_amount'] : 0;

                                    //connected
                                    foreach ($data_cdr[0]['disposition_arr'] as $key_dis => $disposition) {
                                       if ($disposition == 'ANSWERED') {
                                          $count_ans ++;
                                          array_push($answer_arr, $data_cdr[0]['customernumber'][$key_dis]);
                                       }
                                    }
                                    $temp_member['count_conn'] = $count_ans;
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
                                    $temp_member['conn_amount'] = isset($data_conn[0]) ? $data_conn[0]['conn_amount'] : 0;

                                    //paid
                                    foreach ($data_officer as $office) {
                                       if ($office['_id'] == $member_jc05) {
                                          $match_paid = array(
                                            '$match' => array('account_number' => ['$in' => $office['account_arr']])
                                          );
                                          $group_paid = array(
                                             '$group' => array(
                                                '_id' => null,
                                                'paid_amount' => array('$sum'=> '$amt'),
                                                'count_paid'  => array('$sum' => 1)
                                             )
                                          );
                                          $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_paid,$group_paid);
                                          $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                          $data_paid = $this->mongo_db->aggregate_pipeline($this->ln3206_collection, $data_aggregate);
                                          $temp_member['count_paid'] = isset($data_paid[0]) ? $data_paid[0]['count_paid'] : 0;
                                          $temp_member['paid_amount'] = isset($data_paid[0]) ? $data_paid[0]['paid_amount'] : 0;

                                          $temp['count_paid'] += $temp_member['count_paid'];
                                          $temp['paid_amount'] += $temp_member['paid_amount'];
                                       }
                                    }

                                    //team
                                    $temp['count_data'] += $temp_member['count_data'];
                                    $temp['unwork'] += $temp_member['unwork'];
                                    $temp['talk_time'] += $temp_member['talk_time'];
                                    $temp['total_call'] += $temp_member['total_call'];
                                    $temp['total_amount'] += $temp_member['total_amount'];
                                    $temp['conn_amount'] += $temp_member['conn_amount'];
                                    $temp['count_conn'] += $temp_member['count_conn'];
                                    $temp['spin_amount'] += $temp_member['spin_amount'];
                                    $temp['count_spin'] += $temp_member['count_spin'];
                                    
                                 }
                                 
                              }
                             
                           }
                           array_push($insertData, $temp_member);
                        }
                        $i++;
                        array_push($insertData, $temp);
                     }
                  }
                  
               }

               
            }
            if (count($insertData) > 0) {
               $this->mongo_db->batch_insert($this->collection,$insertData);
            }

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