<?php

function importFrom_Loan_campaign_list($TYPE, $collection, $diallist_id, $index, $members = null) {
    global $mongo_db;
    $diallist_detail_collection = "LO_Diallist_detail";
    $money = $mongo_db->where('type', 'LO_')->getOne('LO_Dial_config');

    $mongo_db->switch_db('LOAN_campaign_list');

    $i              = 0;
    $diallist_id    = new MongoDB\BSON\ObjectId($diallist_id);
    $phoneField     = getPhoneField($collection);
    $data           = $mongo_db->get($collection);
    $mongo_db->switch_db('worldfone4xs');

    for($i=0; $i < count($data); $i++){

        if($TYPE == 'SIBS'){
            $data[$i]['Donotcall'] = conditionDoNotCall($data[$i], $money) ? 'Y' : 'N';
        }
        if($members != null){
            if($TYPE == 'SIBS'){
                $last_assign = $mongo_db->where("account_number", $data[$i]["account_number"])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
                if(isset($last_assign['assign'])){
                    if(in_array($last_assign['assign'], $members)){
                        $data[$i]["assign"] = $last_assign['assign'];
                    }else{
                        $data[$i]["assign"] = substr($data[$i]["officer_id"], -4);
                    }
                }else{
                    $data[$i]["assign"] = substr($data[$i]["officer_id"], -4);
                }
            }else{
               $last_assign = $mongo_db->where("account_number", $data[$i]["account_number"])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
               if(isset($last_assign['assign'])){
                if(in_array($last_assign['assign'], $members))
                    $data[$i]["assign"] = $last_assign['assign'];
                else{
                   $data[$i]["assign"] = isset($members[0]) ? $members[0] : null;
                   $members            = reorder_array($members);
                }
               }else{
                   $data[$i]["assign"] = isset($members[0]) ? $members[0] : null;
                   $members            = reorder_array($members);
               }
            }
        }

        $data[$i]["diallist_id"] = $diallist_id;
        $data[$i]["createdBy"] = 'System';
        $data[$i]["createdAt"] = time();
        $data[$i]["index"] = ++$index;
        $data[$i]["priority"] = 99;
        $data[$i]["from"] = $collection;
        if(isset($data[$i][$phoneField]) && !empty($data[$i][$phoneField])) {
            $data[$i]["phone"] = ((string)$data[$i][$phoneField][0] == '0') ? (string) $data[$i][$phoneField] : '0'. $data[$i][$phoneField];
        }

    }
    $mongo_db->switch_db('worldfone4xs');
    if(!empty($data))
        $mongo_db->batch_insert($diallist_detail_collection, $data);
   
}

function importFrom_Loan_campaign_listA($TYPE, $collection, $diallist_id, $index, $g_type, $members = null) {
    global $mongo_db;
    $diallist_detail_collection = "LO_Diallist_detail";
    $money = $mongo_db->where('type', 'LO_')->getOne('LO_Dial_config');

    $mongo_db->switch_db('LOAN_campaign_list');

    $i              = 0;
    $diallist_id    = new MongoDB\BSON\ObjectId($diallist_id);
    $phoneField     = getPhoneField($collection);
    $data           = $mongo_db->where('G_type', $g_type)->get($collection);
    
    $mongo_db->switch_db('worldfone4xs');
    for($i=0; $i < count($data); $i++){
        if($TYPE == 'SIBS'){
            $data[$i]['Donotcall'] = conditionDoNotCall($data[$i], $money) ? 'Y' : 'N';
        }

        if($members != null){
            if($TYPE == 'SIBS'){
                $last_assign = $mongo_db->where("account_number", $data[$i]["account_number"])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
                if(isset($last_assign['assign'])){
                    if(in_array($last_assign['assign'], $members))
                        $data[$i]["assign"] = $last_assign['assign'];
                    else{
                        $data[$i]["assign"] = substr($data[$i]["officer_id"], -4);
                    }
                }
                else{
                    $data[$i]["assign"] = substr($data[$i]["officer_id"], -4);
                }

            }else{
                $last_assign = $mongo_db->where("account_number", $data[$i]["account_number"])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
                if(isset($last_assign['assign'])){
                    if(in_array($last_assign['assign'], $members))
                        $data[$i]["assign"] = $last_assign['assign'];
                    else{
                        $data[$i]["assign"] = isset($members[0]) ? $members[0] : null;
                        $members            = reorder_array($members);
                    }
                }else{
                    $data[$i]["assign"] = isset($members[0]) ? $members[0] : null;
                    $members            = reorder_array($members);
                }
            }
        }

        $data[$i]["diallist_id"] = $diallist_id;
        $data[$i]["createdBy"] = 'System';
        $data[$i]["createdAt"] = time();
        $data[$i]["index"] = ++$index;
        $data[$i]["priority"] = 99;
        $data[$i]["from"] = $collection;
        if(isset($data[$i][$phoneField]) && !empty($data[$i][$phoneField])) {
            $data[$i]["phone"] = ((string)$data[$i][$phoneField][0] == '0') ? (string) $data[$i][$phoneField] : '0'. $data[$i][$phoneField];
        }

    }
    
    if(!empty($data))
        $mongo_db->batch_insert($diallist_detail_collection, $data);
   
}

function SIBS_createDiallistDetailFor_D_E($collection, $diallist_id, $index, $members = null) {
    global $mongo_db;
    $diallist_detail_collection = "LO_Diallist_detail";
    $money = $mongo_db->where('type', 'LO_')->getOne('LO_Dial_config');

    $mongo_db->switch_db('LOAN_campaign_list');

    $i              = 0;
    $diallist_id    = new MongoDB\BSON\ObjectId($diallist_id);
    $phoneField     = getPhoneField($collection);
    $data           = $mongo_db->get($collection);
    $duedate_plus1_check = checkTodayIsDueDatePlus1($collection);

    $mongo_db->switch_db('worldfone4xs');

    for($i=0; $i < count($data); $i++){

        $data[$i]['Donotcall'] = conditionDoNotCall($data[$i], $money) ? 'Y' : 'N';

        if($members != null && $duedate_plus1_check == false){
            $last_assign = $mongo_db->where("account_number", $data[$i]["account_number"])->order_by(array('_id' => -1))->getOne('LO_Diallist_detail');
            if(isset($last_assign['assign'])){
                if(in_array($last_assign['assign'], $members)){
                    $data[$i]["assign"] = $last_assign['assign'];
                }else{
                    $data[$i]["assign"] = substr($data[$i]["officer_id"], -4);
                }
            }else{
                $data[$i]["assign"] = substr($data[$i]["officer_id"], -4);
            }
        }else{
            $data[$i]["assign"] = substr($data[$i]["officer_id"], -4);
        }

        $data[$i]["diallist_id"] = $diallist_id;
        $data[$i]["createdBy"] = 'System';
        $data[$i]["createdAt"] = time();
        $data[$i]["index"] = ++$index;
        $data[$i]["priority"] = 99;
        $data[$i]["from"] = $collection;
        if(isset($data[$i][$phoneField]) && !empty($data[$i][$phoneField])) {
            $data[$i]["phone"] = ((string)$data[$i][$phoneField][0] == '0') ? (string) $data[$i][$phoneField] : '0'. $data[$i][$phoneField];
        }

    }
    $mongo_db->switch_db('worldfone4xs');
    if(!empty($data))
        $mongo_db->batch_insert($diallist_detail_collection, $data);
   
}

function reorder_array($array){
    $result = [];
    
    if(count($array) <= 1) return $array;

    for($i=0; $i<= count($array) -2;$i++){
        $result[$i + 1] = $array[$i];
    }
    $result[0] = $array[count($array) -1];
    return $result;
}

function getPhoneField($collection){
    if(strpos($collection, 'SIBS') === 0){
        return "mobile_num";
    }else if(strpos($collection, 'CARD') === 0){
        return "phone";
    }else if(strpos($collection, 'WO') === 0){
        return "PHONE";
    }
}

function conditionDoNotCall($data, $money){
    if (isset($data['PRODGRP_ID'])):
        if($data['PRODGRP_ID'] == '103' || $data['PRODGRP_ID'] == '602' || $data['PRODGRP_ID'] == '802')
            return false;

        $check40k = $data["overdue_amount_this_month"] - $data["advance_balance"];

        $money = isset($money["conditionDonotCall"]) ? $money["conditionDonotCall"] : 40000;
        if($check40k < $money){

            if($data["installment_type"] == 'n' && $data["outstanding_principal"] == 0){
                return true;
            }else if($data["installment_type"] != 'n' && $data["outstanding_principal"] > 0){
                return true;
            }
            return false;
        }else{
            return false;
        }

    endif;
}

function checkTodayIsDueDatePlus1($collection){
    global $mongo_db;
    $midnight = strtotime('today midnight');

    $time1 = $midnight -1000;
    $time2 = $midnight + 86000;

    $check_duedate_plus1 = $mongo_db->where("due_date_add_1", ['$gte' => $time1, '$lte' => $time2])->getOne('LO_Report_due_date');

    if(empty($check_duedate_plus1)) return false;
    if(isset($check_duedate_plus1['debt_group'])){
        $check_E = 'E' . $check_duedate_plus1['debt_group'];
        $check_D = 'D' . $check_duedate_plus1['debt_group'];
        if(strpos($collection, $check_E) !== false){
            return true;
        }

        if(strpos($collection, $check_D) !== false){
            return true;
        }
    }
    return false;
}