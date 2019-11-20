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
    private $diallist_detail_collection = "Diallist_detail";

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
        $this->diallist_detail_collection = set_sub_collection($this->diallist_detail_collection);
    }


    function save()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $now = getdate();
            $today = $now['mday'].'-'.$now['month'].'-'.$now['year'];
            $date = strtotime("$today");
            // $date = 1569862800;
            $due_date = $this->mongo_db->where(array('due_date_add_1' => $date  ))->select(array('due_date','debt_group'))->getOne($this->duedate_collection);

            //sibs
            $this->mongo_db->switch_db('_worldfone4xs');
            $users = $this->mongo_db->where(array('active' => true  ))->select(array('extension','agentname'))->get($this->user_collection);
            $this->mongo_db->switch_db();


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
            $match_officer = array(
               '$match' => array(
                  '$and' => array(
                     array('due_date'=> array( '$gte'=> $date)),
                  )
               )
            );

            $group_officer = array(
               '$group' => array(
                  '_id' => '$officer_id',
                  'account_arr' => array('$push'=> '$account_number'),
                  'count_data' => array('$sum'=> 1),
               )
            );
            $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_officer,$group_officer);

            $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
            $data_officer = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
            // var_dump($users);exit;
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
                     $temp['extension']  = $row['lead'];
                     if ($due_date != null) {

                        $duedate = $due_date['due_date'];
                        $temp['due_date']          = $duedate;
                        $temp['count_data'] = $this->mongo_db->where(array("officer_id" => 'JIVF00'.$row['lead'], 'due_date' => array( '$gte'=> $duedate) ))->count($this->lnjc05_collection);
                        $temp['unwork']   = isset($phone['phone_arr']) ? $this->mongo_db->where(array("userextension" => ['$in' => $row['members']],  'disposition' =>array('$ne' => 'ANSWERED'), 'starttime' =>array( '$gte'=> $date) ))->count($this->cdr_collection) : 0;
                        $match_cdr = array(
                          '$match' => array(
                              '$and' => array(
                                 array('starttime'=> array( '$gte'=> $date)),
                                 array('userextension' => ['$in' => $row['members']])
                              )
                           )
                        );

                     }else {

                        $result = $this->mongo_db->where(array('due_date' => ['$exists' => true],'team_lead' => ['$exists' => true],'extension' => $row['lead']  ))->select(array('count_data','due_date'))->order_by(array('date'=> -1))->getOne($this->collection);
                        $due_date_add_1     = isset($result['due_date']) ? $result['due_date'] : $date;
                        $temp['count_data'] = isset($result['count_data']) ? $result['count_data'] : 0;
                        $temp['unwork'] = $this->mongo_db->where(array("userextension" => $row['lead'], 'disposition' =>array('$ne' => 'ANSWERED'), 'starttime' =>array( '$gte'=> $due_date_add_1, '$lte'=> $date)))->count($this->cdr_collection);
                        $match_cdr = array(
                          '$match' => array(
                              '$and' => array(
                                 array('starttime'=> array( '$gte'=> $due_date_add_1, '$lte'=> $date)),
                                 array('userextension' => ['$in' => $row['members']])
                              )
                           )
                        );

                     }

                     $temp['talk_time'] = $temp['total_call'] = $temp['total_amount'] = $temp['count_spin'] = $temp['spin_amount'] = $temp['count_conn'] = $temp['conn_amount'] = $temp['count_paid'] = $temp['paid_amount'] = $temp['ptp_amount'] = $temp['count_ptp'] = $temp['paid_amount_promise'] = $temp['count_paid_promise'] = 0;

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

                        if (isset($duedate)) {
                           $match_ct = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array( '$gte'=> $date)),
                                    array('mobile_num' => ['$in' => $arr_unique_phone])
                                 )
                              )
                           );
                        }else{
                           $match_ct = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                    array('mobile_num' => ['$in' => $arr_unique_phone])
                                 )
                              )
                           );
                        }
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

                        if (isset($duedate)) {
                           $match_spin = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array( '$gte'=> $date)),
                                    array('mobile_num' => ['$in' => $arr_spin])
                                 )
                              )
                           );
                        }else{
                           $match_spin = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                    array('mobile_num' => ['$in' => $arr_spin])
                                 )
                              )
                           );
                        }

                        $group_spin = array(
                           '$group' => array(
                              '_id' => null,
                              'spin_amount' => array('$sum'=> '$current_balance'),
                           )
                        );
                        $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_spin,$group_spin);
                        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                        $data_ct = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                        $temp['count_spin'] = $count_spin;
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

                        if (isset($duedate)) {
                           $match_conn = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array( '$gte'=> $date)),
                                    array('mobile_num' => ['$in' => array_values(array_unique($answer_arr))])
                                 )
                              )
                           );
                        }else{
                           $match_conn = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                    array('mobile_num' => ['$in' => array_values(array_unique($answer_arr))])
                                 )
                              )
                           );
                        }
                        $group_conn = array(
                           '$group' => array(
                              '_id' => null,
                              'conn_amount' => array('$sum'=> '$current_balance'),
                           )
                        );
                        $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_conn,$group_conn);
                        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                        $data_conn = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                        $temp['count_conn']  = $count_ans;
                        $temp['conn_amount'] = isset($data_conn[0]) ? $data_conn[0]['conn_amount'] : 0;

                        //promise to pay
                        if (isset($duedate)) {
                           $match_ptp = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('createdAt'=> array( '$gte'=> $date)),
                                    array('officer_id'=> $row['lead']),
                                    array('$or' => [ array( 'action_code'=>  'BPTP'), array('action_code'=>  'PTP Today')])
                                 )
                              )
                           );
                        }else{
                           $match_ptp = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('createdAt'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                    array('officer_id'=> $row['lead']),
                                    array('$or' => [ array( 'action_code'=>  'BPTP'), array('action_code'=>  'PTP Today')])
                                 )
                              )
                           );
                        }

                        $group_ptp = array(
                           '$group' => array(
                              '_id' => null,
                              'account_arr' => array('$push'=> '$account_number'),
                              'count_ptp' => array('$sum'=> 1)
                           )
                        );
                        $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_ptp,$group_ptp);
                        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                        $data_ptp = $this->mongo_db->aggregate_pipeline($this->diallist_detail_collection, $data_aggregate);

                        $account_ptp_arr   = isset($data_ptp[0]) ? array_values(array_unique($data_ptp[0]['account_arr'])) : array();
                        if (isset($duedate)) {
                           $match_ptp_1 = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array( '$gte'=> $date)),
                                    array('account_number' => ['$in' => $account_ptp_arr])
                                 )
                              )
                           );
                        }else{
                           $match_ptp_1 = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                    array('account_number' => ['$in' => $account_ptp_arr])
                                 )
                              )
                           );
                        }
                        $group_ptp_1 = array(
                           '$group' => array(
                              '_id' => null,
                              'ptp_amount' => array('$sum'=> '$current_balance'),
                           )
                        );
                        $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_ptp_1,$group_ptp_1);
                        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                        $data_ptp_1 = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                        $temp['count_ptp']    = isset($data_conn[0]) ? $data_conn[0]['count_ptp'] : 0;
                        $temp['ptp_amount']   = isset($data_ptp_1[0]) ? $data_ptp_1[0]['ptp_amount'] : 0;

                        //paid keep promise to pay
                        if (isset($duedate)) {
                           $match_paid_promise = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('created_at'=> array( '$gte'=> $date)),
                                    array('account_number' => ['$in' => $account_ptp_arr])
                                 )
                              )
                           );
                        }else{
                           $match_paid_promise = array(
                              '$match' => array(
                                 '$and' => array(
                                    array('created_at'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                    array('account_number' => ['$in' => $account_ptp_arr])
                                 )
                              )
                           );
                        }

                        $group_paid_promise = array(
                           '$group' => array(
                              '_id' => null,
                              'paid_amount_promise' => array('$sum'=> '$amt'),
                              'count_paid_promise'  => array('$sum' => 1)
                           )
                        );
                        $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_paid_promise,$group_paid_promise);
                        $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                        $data_paid_promise = $this->mongo_db->aggregate_pipeline($this->ln3206_collection, $data_aggregate);
                        $temp['count_paid_promise'] = isset($data_paid_promise[0]) ? $data_paid_promise[0]['count_paid_promise'] : 0;
                        $temp['paid_amount_promise'] = isset($data_paid_promise[0]) ? $data_paid_promise[0]['paid_amount_promise'] : 0;


                        //paid
                        foreach ($data_officer as $office) {
                           if ($office['_id'] == 'JIVF00'.$row['lead']) {
                              if (isset($duedate)) {
                                 $match_paid = array(
                                    '$match' => array(
                                       '$and' => array(
                                          array('created_at'=> array( '$gte'=> $date)),
                                          array('account_number' => ['$in' => $office['account_arr']])
                                       )
                                    )
                                 );
                              }else{
                                 $match_paid = array(
                                    '$match' => array(
                                       '$and' => array(
                                          array('created_at'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                          array('account_number' => ['$in' => $office['account_arr']])
                                       )
                                    )
                                 );
                              }
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
                     $temp['spin_rate']      = $temp['count_spin']/$temp['total_call'];
                     $temp['ptp_rate_acc']   = $temp['count_ptp']/$temp['total_call'];
                     $temp['ptp_rate_amt']   = $temp['ptp_amount']/$temp['total_amount'];
                     $temp['paid_rate_acc']  = $temp['count_paid_promise']/$temp['count_ptp'];
                     $temp['paid_rate_amt']  = $temp['paid_amount_promise']/$temp['ptp_amount'];
                     $temp['conn_rate']      = $temp['count_conn']/$temp['total_call'];
                     $temp['collect_ratio_acc'] = $temp['count_paid']/$temp['total_call'];
                     $temp['collect_ratio_amt'] = $temp['paid_amount']/$temp['total_amount'];
                     array_push($insertData, $temp);

                     $temp_member = [];
                     $count_member = count($row['members']);
                     foreach ($row['members'] as $member) {
                        foreach ($users as $user) {
                           if ($member == $user['extension']) {
                              $temp_member['name'] = $user['agentname'];
                           }
                        }
                        $temp_member['extension']  = $member;
                        $temp_member['group']      = $key;
                        $temp_member['team']       = $i;
                        $temp_member['date']       = $date;
                        $temp_member['count_data'] = round($temp['count_data']/$count_member,2);
                        $temp_member['unwork']     = round($temp['unwork']/$count_member,2);
                        $temp_member['talk_time']  = round($temp['talk_time']/$count_member,2);
                        $temp_member['total_call'] = round($temp['total_call']/$count_member,2);
                        $temp_member['total_amount']  = round($temp['total_amount']/$count_member,2);
                        $temp_member['count_spin']    = round($temp['count_spin']/$count_member,2);
                        $temp_member['spin_amount']   = round($temp['spin_amount']/$count_member,2);
                        $temp_member['count_conn']    = round($temp['count_conn']/$count_member,2);
                        $temp_member['conn_amount']   = round($temp['conn_amount']/$count_member,2);
                        $temp_member['count_paid']    = round($temp['count_paid']/$count_member,2);
                        $temp_member['paid_amount']   = round($temp['paid_amount']/$count_member,2);
                        $temp_member['count_ptp']     = round($temp['count_ptp']/$count_member,2);
                        $temp_member['ptp_amount']    = round($temp['ptp_amount']/$count_member,2);
                        $temp_member['count_paid_promise']    = round($temp['count_paid_promise']/$count_member,2);
                        $temp_member['paid_amount_promise']    = round($temp['paid_amount_promise']/$count_member,2);

                        $temp_member['spin_rate']     = $temp_member['count_spin']/$temp_member['total_call'];
                        $temp_member['ptp_rate_acc']  = $temp_member['count_ptp']/$temp_member['total_call'];
                        $temp_member['ptp_rate_amt']  = $temp_member['ptp_amount']/$temp_member['total_amount'];
                        $temp_member['paid_rate_acc'] = $temp_member['count_paid_promise']/$temp_member['count_ptp'];
                        $temp_member['paid_rate_amt'] = $temp_member['paid_amount_promise']/$temp_member['ptp_amount'];
                        $temp_member['conn_rate']     = $temp_member['count_conn']/$temp_member['total_call'];
                        $temp_member['collect_ratio_acc'] = $temp_member['count_paid']/$temp_member['total_call'];
                        $temp_member['collect_ratio_amt'] = $temp_member['paid_amount']/$temp_member['total_amount'];
                        array_push($insertData, $temp_member);
                     }
                     $i++;

                  }


               }
               else{
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
                        $temp['count_data'] = $temp['unwork'] = $temp['talk_time'] = $temp['total_call'] = $temp['total_amount'] = $temp['count_spin'] = $temp['spin_amount'] = $temp['count_conn'] = $temp['conn_amount'] = $temp['count_paid'] = $temp['paid_amount'] = $temp['ptp_amount'] = $temp['count_ptp'] = $temp['count_paid_promise'] = $temp['paid_amount_promise'] = 0;
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
                                 $debt_group = substr($team['debt_groups'][0], 1,2);
                                 if ($due_date != null && $due_date['debt_group'] == $debt_group) {
                                    $duedate = $due_date['due_date'];
                                    $temp_member['due_date']   = $duedate;
                                    $temp['due_date']          = $duedate;

                                    $temp_member['count_data'] = $this->mongo_db->where(array("officer_id" => $member_jc05, 'due_date' => $duedate ))->count($this->lnjc05_collection);
                                    $temp_member['unwork'] = $this->mongo_db->where(array("userextension" => $member, 'disposition' =>array('$ne' => 'ANSWERED'),'starttime' => array('$gte' => $date) ))->count($this->cdr_collection);
                                    $match_cdr = array(
                                      '$match' => array(
                                          '$and' => array(
                                             array('starttime'=> array( '$gte'=> $date)),
                                             array('userextension' => $member)
                                          )
                                       )
                                    );
                                 }else {

                                    $result = $this->mongo_db->where(array('due_date' => ['$exists' => true],'extension' => $member  ))->select(array('count_data','due_date'))->order_by(array('date'=> -1))->getOne($this->collection);
                                    $due_date_add_1            = isset($result['due_date']) ? $result['due_date'] : $date;
                                    $temp_member['count_data'] = isset($result['count_data']) ? $result['count_data'] : 0;
                                    $temp_member['unwork']     = $this->mongo_db->where(array("userextension" => $member, 'disposition' =>array('$ne' => 'ANSWERED'), 'starttime' =>array( '$gte'=> $due_date_add_1, '$lte'=> $date)))->count($this->cdr_collection);
                                    $match_cdr = array(
                                      '$match' => array(
                                          '$and' => array(
                                             array('starttime'=> array( '$gte'=> $due_date_add_1, '$lte'=> $date)),
                                             array('userextension' => $member)
                                          )
                                       )
                                    );

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
                                    if (isset($duedate)) {
                                       $match_ct = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array( '$gte'=> $date)),
                                                array('mobile_num' => ['$in' => $arr_unique_phone])
                                             )
                                          )
                                       );
                                    }else{
                                       $match_ct = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                                array('mobile_num' => ['$in' => $arr_unique_phone])
                                             )
                                          )
                                       );
                                    }
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

                                    if (isset($duedate)) {
                                       $match_spin = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array( '$gte'=> $date)),
                                                array('mobile_num' => ['$in' => $arr_spin])
                                             )
                                          )
                                       );
                                    }else{
                                       $match_spin = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                                array('mobile_num' => ['$in' => $arr_spin])
                                             )
                                          )
                                       );
                                    }
                                    $group_spin = array(
                                       '$group' => array(
                                          '_id' => null,
                                          'spin_amount' => array('$sum'=> '$current_balance'),
                                       )
                                    );
                                    $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_spin,$group_spin);
                                    $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                    $data_ct = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                                    $temp_member['count_spin']    = $count_spin;
                                    $temp_member['spin_amount']   = isset($data_ct[0]) ? $data_ct[0]['spin_amount'] : 0;

                                    //connected
                                    foreach ($data_cdr[0]['disposition_arr'] as $key_dis => $disposition) {
                                       if ($disposition == 'ANSWERED') {
                                          $count_ans ++;
                                          array_push($answer_arr, $data_cdr[0]['customernumber'][$key_dis]);
                                       }
                                    }

                                    if (isset($duedate)) {
                                       $match_conn = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array( '$gte'=> $date)),
                                                array('mobile_num' => ['$in' => array_values(array_unique($answer_arr))])
                                             )
                                          )
                                       );
                                    }else{
                                       $match_conn = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                                array('mobile_num' => ['$in' => array_values(array_unique($answer_arr))])
                                             )
                                          )
                                       );
                                    }

                                    $group_conn = array(
                                       '$group' => array(
                                          '_id' => null,
                                          'conn_amount' => array('$sum'=> '$current_balance'),
                                       )
                                    );
                                    $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_conn,$group_conn);
                                    $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                    $data_conn = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                                    $temp_member['count_conn']    = $count_ans;
                                    $temp_member['conn_amount']   = isset($data_conn[0]) ? $data_conn[0]['conn_amount'] : 0;

                                    //promise to pay
                                    if (isset($duedate)) {
                                       $match_ptp = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('createdAt'=> array( '$gte'=> $date)),
                                                array('officer_id'=> $member),
                                                array('$or' => [ array( 'action_code'=>  'BPTP'), array('action_code'=>  'PTP Today')])
                                             )
                                          )
                                       );
                                    }else{
                                       $match_ptp = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('createdAt'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                                array('officer_id'=> $member),
                                                array('$or' => [ array( 'action_code'=>  'BPTP'), array('action_code'=>  'PTP Today')])
                                             )
                                          )
                                       );
                                    }

                                    $group_ptp = array(
                                       '$group' => array(
                                          '_id' => null,
                                          'account_arr' => array('$push'=> '$account_number'),
                                          'count_ptp' => array('$sum'=> 1)
                                       )
                                    );
                                    $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_ptp,$group_ptp);
                                    $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                    $data_ptp = $this->mongo_db->aggregate_pipeline($this->diallist_detail_collection, $data_aggregate);

                                    $account_ptp_arr   = isset($data_ptp[0]) ? array_values(array_unique($data_ptp[0]['account_arr'])) : array();
                                    if (isset($duedate)) {
                                       $match_ptp_1 = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array( '$gte'=> $date)),
                                                array('account_number' => ['$in' => $account_ptp_arr])
                                             )
                                          )
                                       );
                                    }else{
                                       $match_ptp_1 = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('due_date'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                                array('account_number' => ['$in' => $account_ptp_arr])
                                             )
                                          )
                                       );
                                    }
                                    $group_ptp_1 = array(
                                       '$group' => array(
                                          '_id' => null,
                                          'ptp_amount' => array('$sum'=> '$current_balance'),
                                       )
                                    );
                                    $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_ptp_1,$group_ptp_1);
                                    $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                    $data_ptp_1 = $this->mongo_db->aggregate_pipeline($this->lnjc05_collection, $data_aggregate);
                                    $temp_member['count_ptp']    = isset($data_conn[0]) ? $data_conn[0]['count_ptp'] : 0;
                                    $temp_member['ptp_amount']   = isset($data_ptp_1[0]) ? $data_ptp_1[0]['ptp_amount'] : 0;

                                    //paid keep promise to pay
                                    if (isset($duedate)) {
                                       $match_paid_promise = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('created_at'=> array( '$gte'=> $date)),
                                                array('account_number' => ['$in' => $account_ptp_arr])
                                             )
                                          )
                                       );
                                    }else{
                                       $match_paid_promise = array(
                                          '$match' => array(
                                             '$and' => array(
                                                array('created_at'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                                array('account_number' => ['$in' => $account_ptp_arr])
                                             )
                                          )
                                       );
                                    }

                                    $group_paid_promise = array(
                                       '$group' => array(
                                          '_id' => null,
                                          'paid_amount_promise' => array('$sum'=> '$amt'),
                                          'count_paid_promise'  => array('$sum' => 1)
                                       )
                                    );
                                    $this->kendo_aggregate->set_kendo_query($request)->filtering()->adding($match_paid_promise,$group_paid_promise);
                                    $data_aggregate = $this->kendo_aggregate->paging()->get_data_aggregate();
                                    $data_paid_promise = $this->mongo_db->aggregate_pipeline($this->ln3206_collection, $data_aggregate);
                                    $temp_member['count_paid_promise'] = isset($data_paid_promise[0]) ? $data_paid_promise[0]['count_paid_promise'] : 0;
                                    $temp_member['paid_amount_promise'] = isset($data_paid_promise[0]) ? $data_paid_promise[0]['paid_amount_promise'] : 0;


                                    //paid
                                    foreach ($data_officer as $office) {
                                       if ($office['_id'] == $member_jc05) {
                                          if (isset($duedate)) {
                                             $match_paid = array(
                                                '$match' => array(
                                                   '$and' => array(
                                                      array('created_at'=> array( '$gte'=> $date)),
                                                      array('account_number' => ['$in' => $office['account_arr']])
                                                   )
                                                )
                                             );
                                          }else{
                                             $match_paid = array(
                                                '$match' => array(
                                                   '$and' => array(
                                                      array('created_at'=> array('$gte'=> $due_date_add_1, '$lte'=> $date)),
                                                      array('account_number' => ['$in' => $office['account_arr']])
                                                   )
                                                )
                                             );
                                          }

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

                                    $temp_member['spin_rate']     = $temp_member['count_spin']/$temp_member['total_call'];
                                    $temp_member['ptp_rate_acc']  = $temp_member['count_ptp']/$temp_member['total_call'];
                                    $temp_member['ptp_rate_amt']  = $temp_member['ptp_amount']/$temp_member['total_amount'];
                                    $temp_member['paid_rate_acc'] = $temp_member['count_paid_promise']/$temp_member['count_ptp'];
                                    $temp_member['paid_rate_amt'] = $temp_member['paid_amount_promise']/$temp_member['ptp_amount'];
                                    $temp_member['conn_rate']     = $temp_member['count_conn']/$temp_member['total_call'];
                                    $temp_member['collect_ratio_acc'] = $temp_member['count_paid']/$temp_member['total_call'];
                                    $temp_member['collect_ratio_amt'] = $temp_member['paid_amount']/$temp_member['total_amount'];

                                    //team
                                    $temp['count_data']     += $temp_member['count_data'];
                                    $temp['unwork']         += $temp_member['unwork'];
                                    $temp['talk_time']      += $temp_member['talk_time'];
                                    $temp['total_call']     += $temp_member['total_call'];
                                    $temp['total_amount']   += $temp_member['total_amount'];
                                    $temp['conn_amount']    += $temp_member['conn_amount'];
                                    $temp['count_conn']     += $temp_member['count_conn'];
                                    $temp['spin_amount']    += $temp_member['spin_amount'];
                                    $temp['count_spin']     += $temp_member['count_spin'];
                                    $temp['count_ptp']      += $temp_member['count_ptp'];
                                    $temp['ptp_amount']     += $temp_member['ptp_amount'];
                                    $temp['count_paid_promise']     += $temp_member['count_paid_promise'];
                                    $temp['paid_amount_promise']     += $temp_member['count_paid_promise'];

                                    $temp['spin_rate']      += $temp_member['spin_rate'];
                                    $temp['ptp_rate_acc']   += $temp_member['ptp_rate_acc'];
                                    $temp['ptp_rate_amt']   += $temp_member['ptp_rate_amt'];
                                    $temp['paid_rate_acc']  += $temp_member['paid_rate_acc'];
                                    $temp['paid_rate_amt']  += $temp_member['paid_rate_amt'];
                                    $temp['conn_rate']      += $temp_member['conn_rate'];
                                    $temp['collect_ratio_acc'] += $temp_member['collect_ratio_acc'];
                                    $temp['collect_ratio_amt'] += $temp_member['collect_ratio_amt'];
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
               // print_r($insertData);
               $this->mongo_db->batch_insert($this->collection,$insertData);
               print_r('success');
            }

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

   function exportExcel()
   {
      $now = date('d/m/Y');
      $now = getdate();
      $today = $now['mday'].'-'.$now['month'].'-'.$now['year'];
      $date = strtotime("$today");
      $data = $this->mongo_db->where(array('date' => 1569862800  ))->get($this->collection);
      // if($data) {
      //     $row = 4;
      //     foreach ($data as $value) {
      //       if ($value['group'] == 'A') {
      //          if (isset($value['team_lead'])) {
      //             $team = $value['team'];
      //          }
      //          var_dump($value);
      //          // $worksheet->setCellValue("B".$row, $value['group_id']);
      //          $row++;
      //       }


      //     }
      // }
      // // var_dump($data);
      // exit;



      $filename = "DAILY ALL USER REPORT.xlsx";
      $spreadsheet = new Spreadsheet();
      $spreadsheet->getProperties()
      ->setCreator("South Telecom")
      ->setLastModifiedBy("Thanh Hung")
      ->setTitle("DAILY ALL USER REPORT")
      ->setSubject("DAILY ALL USER REPORT")
      ->setDescription("Office 2007 XLSX, generated using PHP classes.")
      ->setKeywords("office 2007 openxml php")
      ->setCategory("Report");

      $worksheet = $spreadsheet->getSheet(0);
      $worksheet->setTitle('Daily All User');
      $fieldToCol = array();
      // Title row
      $row = 1;
      $worksheet->setCellValue("A1", "No");
      $worksheet->getColumnDimension('A')->setAutoSize(true);
      $worksheet->setCellValue("B1", "Date");
      $worksheet->mergeCells('C1:Y1')->setCellValue("C1", $now['mday'].'-'.$now['mon'].'-'.$now['year']);
      $style = array('font' => array('bold' => true), 'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
      $worksheet->getStyle("C1")->applyFromArray($style);

      $worksheet->mergeCells('A2:B3')->setCellValue("A2", "A GROUP");
      $worksheet->mergeCells('C2:C3')->setCellValue("C2", "Total handled accounts");
      $worksheet->mergeCells('D2:D3')->setCellValue("D2", "Unwork accounts");
      $worksheet->mergeCells('E2:E3')->setCellValue("E2", "Talk time (minutes)");
      $worksheet->mergeCells('F2:G2')->setCellValue("F2", "Contacted");
      $worksheet->setCellValue("F3", "No.of accounts");
      $worksheet->setCellValue("G3", "No.of amount");
      $worksheet->mergeCells('H2:I2')->setCellValue("H2", "Spin");
      $worksheet->setCellValue("H3", "No.of accounts");
      $worksheet->setCellValue("I3", "No.of amount");
      $worksheet->mergeCells('J2:K2')->setCellValue("J2", "Promise to pay");
      $worksheet->setCellValue("J3", "No.of accounts");
      $worksheet->setCellValue("K3", "No.of amount");
      $worksheet->mergeCells('L2:M2')->setCellValue("L2", "Connected");
      $worksheet->setCellValue("L3", "No.of accounts");
      $worksheet->setCellValue("M3", "No.of amount");
      $worksheet->mergeCells('N2:Q2')->setCellValue("N2", "Paid");
      $worksheet->setCellValue("N3", "No.of accounts");
      $worksheet->setCellValue("O3", "Actual Amount received");
      $worksheet->setCellValue("P3", "No.of accounts (keep promise to pay)");
      $worksheet->setCellValue("Q3", "Actual Amount received (keep promise to pay)");
      $worksheet->setCellValue("R2", "Spin rate");
      $worksheet->setCellValue("R3", "Account");
      $worksheet->mergeCells('S2:V2')->setCellValue("S2", "PTP rate");
      $worksheet->setCellValue("S3", "PTP rate (Promised accounts)");
      $worksheet->setCellValue("T3", "PTP rate (PromisedAmount)");
      $worksheet->setCellValue("U3", "PTP rate (total paid accounts)");
      $worksheet->setCellValue("V3", "PTP rate (total paid amount)");
      $worksheet->setCellValue("W2", "Connected rate");
      $worksheet->setCellValue("W3", "Account");
      $worksheet->mergeCells('X2:Y2')->setCellValue("X2", "Collected ratio");
      $worksheet->setCellValue("X3", "Account");
      $worksheet->setCellValue("Y3", "Amount");

      $worksheet->getStyle("A2:Y3")->getFill()
      ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      ->getStartColor()->setRGB('FFFF00');
      $style = array('font' => array('bold' => true), 'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
      $worksheet->getStyle("A2:Y3")->applyFromArray($style);
      if($data) {
          $row = 4;
          foreach ($data as $value) {
            if ($value['group'] == 'A') {
               if (isset($value['team_lead'])) {
                  $worksheet->getStyle("A"."$row".":Y"."$row")->getFill()
                     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                     ->getStartColor()->setRGB('FF9966');
               }
               $worksheet->setCellValue("B".$row, $value['name']);
               $worksheet->setCellValue("C".$row, $value['count_data']);
               $worksheet->setCellValue("D".$row, $value['unwork']);
               $worksheet->setCellValue("E".$row, isset($value['talk_time']) ? $value['talk_time'] : 0);
               $worksheet->setCellValue("F".$row, isset($value['total_call']) ? $value['total_call'] : 0);
               $worksheet->setCellValue("G".$row, isset($value['total_amount']) ? $value['total_amount'] : 0);
               $worksheet->setCellValue("H".$row, isset($value['count_spin']) ? $value['count_spin'] : 0);
               $worksheet->setCellValue("I".$row, isset($value['spin_amount']) ? $value['spin_amount'] : 0);
               $worksheet->setCellValue("J".$row, isset($value['count_ptp']) ? $value['count_ptp'] : 0);
               $worksheet->setCellValue("K".$row, isset($value['ptp_amount']) ? $value['ptp_amount'] : 0);
               $worksheet->setCellValue("L".$row, isset($value['count_conn']) ? $value['count_conn'] : 0);
               $worksheet->setCellValue("M".$row, isset($value['conn_amount']) ? $value['conn_amount'] : 0);
               $worksheet->setCellValue("N".$row, isset($value['count_paid']) ? $value['count_paid'] : 0);
               $worksheet->setCellValue("O".$row, isset($value['paid_amount']) ? $value['paid_amount'] : 0);
               $worksheet->setCellValue("P".$row, isset($value['count_paid_promise']) ? $value['count_paid_promise'] : 0);
               $worksheet->setCellValue("Q".$row, isset($value['paid_amount_promise']) ? $value['paid_amount_promise'] : 0);
               $worksheet->setCellValue("R".$row, isset($value['spin_rate']) ? $value['spin_rate'] : 0);
               $worksheet->setCellValue("S".$row, isset($value['ptp_rate_acc']) ? $value['ptp_rate_acc'] : 0);
               $worksheet->setCellValue("T".$row, isset($value['ptp_rate_amt']) ? $value['ptp_rate_amt'] : 0);
               $worksheet->setCellValue("U".$row, isset($value['paid_rate_acc']) ? $value['paid_rate_acc'] : 0);
               $worksheet->setCellValue("V".$row, isset($value['paid_rate_amt']) ? $value['paid_rate_amt'] : 0);
               $worksheet->setCellValue("W".$row, isset($value['conn_rate']) ? $value['conn_rate'] : 0);
               $worksheet->setCellValue("X".$row, isset($value['collect_ratio_acc']) ? $value['collect_ratio_acc'] : 0);
               $worksheet->setCellValue("Y".$row, isset($value['collect_ratio_amt']) ? $value['collect_ratio_amt'] : 0);
               $row++;
            }


          }
      }
      $styleArray = array(
          'allborders' => array(
              'outline' => array(
                  'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                  'color' => array('argb' => 'FFFF0000'),
              ),
          ),
      );
      $styleArray = array(
         'borders' => array(
             'allborders' => array(
                 'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                 'color' => array('argb' => 'FFFF0000'),
             )
         )
     );
      $worksheet->getStyle("A1:Y30")->applyFromArray($styleArray);

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $file_path = UPLOAD_PATH . "loan/export/" . $filename;
      $writer->save($file_path);
      print_r($file_path);
      // echo json_encode(array("status" => 1, "data" => $file_path));

   }

    function downloadExcel()
    {
        // $file_path = $this->exportExcel();
        $file_path = UPLOAD_PATH . "loan/export/DAILY ALL USER REPORT.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }
}