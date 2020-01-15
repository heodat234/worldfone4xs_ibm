<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "makeCallingList_logs.txt");

require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db               = new Mongo_db();
$arr_contractNo_partner = [];
$MAIN = [];
$today                  = date('Y-m-d', time());


$cus_assigned_partner   = $mongo_db->select(array("CONTRACTNR"))->get('LO_Cus_assigned_partner');

foreach ($cus_assigned_partner as $key => $value) {
    $arr_contractNo_partner[] = $value['CONTRACTNR'];
}


// Group A
    $officer_id = $mongo_db->where(array(
        'group_id' => array('$in' => ['A01','A02','A03'])
    ))->distinct('LO_LNJC05', 'officer_id');
    foreach ($officer_id as $key => $id) {
        $temp = $mongo_db->where(array(
            'group_id'      => array('$in' => ['A01','A02','A03']),
            'officer_id'    => $id,
        ))->get('LO_LNJC05');

        $temp_afterRemove = removeAssigned($arr_contractNo_partner, $temp, "account_number", 'A');
        $MAIN[$id] = $temp_afterRemove;
    }

//Group B+
    $group_id = $mongo_db->where(array(
        'group_id' => array('$nin' => ['A01','A02','A03'])
    ))->distinct('LO_LNJC05', 'group_id');

    foreach ($group_id as $key => $id) {
        
        $temp = $mongo_db->where(array(
            'group_id'      => $id,
        ))->get('LO_LNJC05');

        $temp_afterRemove = removeAssigned($arr_contractNo_partner, $temp, "account_number", 'B+');
        $MAIN[$id] = $temp_afterRemove;

    }

    $mongo_db->switch_db('LOAN_campaign_list');

    foreach ($MAIN as $key => $value) {
        if(!empty($value))
        $mongo_db->batch_insert('SIBS_' .$key. "_" . $today, $value);
    }
    brint('SIBS_' .$key. "_" . $today);
    brint(count($value));
    echo "DONE SIBS";



function removeAssigned($arr_contractNo_partner, $arrData, $fieldNo, $type) {
    global $mongo_db;
    $result = [];
    foreach ($arrData as $key => $value) {
        if($type == 'A'){
            $_temp = time() - $value['due_date'];
            $overdue_days = floor($_temp / (3600 * 24));

            if($overdue_days >= 1 && $overdue_days <= 6){
                $value['G_type'] = 'G1';
            }else if($overdue_days >= 7 && $overdue_days <= 19){
                $value['G_type'] = 'G2';
            }else if($overdue_days >= 20 && $overdue_days <= 31){
                $value['G_type'] = 'G3';
            }else{
                $value['G_type'] = '>31';
            }
        }
        if(isset($value[$fieldNo])){
            if(!in_array($value[$fieldNo], $arr_contractNo_partner)){

                $zaccf      = $mongo_db->where('account_number', $value[$fieldNo])->getOne('LO_ZACCF');
                $customer   = $mongo_db->where('account_number', $value[$fieldNo])->getOne('LO_Customer');
                $value["mobile_num"] = '';
                $report_release_sale = $mongo_db->where("account_number", $value[$fieldNo])->getOne('LO_Report_release_sale');
                if(!empty($zaccf)){
                    $phoneArr               = createRelationshipTable($zaccf, $value[$fieldNo], $mongo_db);
                    $value['other_phones']  = $phoneArr;
                    $value["mobile_num"]    = $zaccf['MOBILE_NO'];

                    $value['PRODGRP_ID']    = isset($zaccf['PRODGRP_ID'])   ? $zaccf['PRODGRP_ID']  : '' ;
                    $value['LIC_NO']        = isset($zaccf['LIC_NO']    )   ? $zaccf['LIC_NO']      : '' ;
                    $value['BIR_DT8']       = isset($zaccf['BIR_DT8']   )   ? $zaccf['BIR_DT8']     : '' ;
                    if($value['BIR_DT8'] == ''){
                        $value['BIR_DT8'] = $zaccf['cif_birth_date'];
                    }
                    $value['House_NO']      = isset($zaccf['House_NO']  )   ? $zaccf['House_NO']    : '' ;
                    if($value['House_NO'] != '' && strlen($value['House_NO']) > 7) $value['other_phones'][] = $value['House_NO'];

                    $value['OFFICE_NO']     = isset($zaccf['OFFICE_NO'] )   ? $zaccf['OFFICE_NO']     : '' ;
                    if($value['OFFICE_NO'] != '' && strlen($value['OFFICE_NO']) > 7) $value['other_phones'][] = $value['OFFICE_NO'];

                    $value['address']       = _isset($zaccf, 'ADDR_1') . _isset($zaccf, 'ADDR_2') .  _isset($zaccf, 'ADDR_3');

                    $temp                   = '';
                    $temp                   .= isset($zaccf['WRK_PST']) ? $zaccf['WRK_PST'] : '';
                    $temp                   .= isset($zaccf['W_CFBUST']) ? $zaccf['W_CFBUST'] : '';
                    if($temp == ''){
                        $temp               .= isset($zaccf['work_position2']) ? $zaccf['work_position2'] : '';
                        $temp               .= isset($zaccf['work_position']) ? '-'.$zaccf['work_position'] : '';
                    }
                    $value['WRK_PST']       = $temp;
                    $value['F_PDT']         = isset($zaccf['F_PDT'] )   ? $zaccf['F_PDT'] : '' ;
                    $value['W_ORG']         = isset($zaccf['W_ORG'] )   ? $zaccf['W_ORG'] : '' ;

                    if(isset($zaccf['CUS_SEX'])) {
                        $value['CUS_SEX']   = ($zaccf['CUS_SEX'] == 0) ? 'NỮ' : 'NAM';
                    }
                }


                $value['temp_address']      = _isset($report_release_sale, 'temp_address') . _isset($report_release_sale, 'temp_district') .  _isset($report_release_sale, 'temp_province');
                '' ;
                $value['permanent_address'] = _isset($report_release_sale, 'address') . _isset($report_release_sale, 'district') .  _isset($report_release_sale, 'province');

                unset($value['id']);
                if(!empty($customer)){
                    $value['action_code']   = isset($customer["action_code"]) ? $customer["action_code"] : '';
                    $value['profession']    = isset($customer["profession"]) ? $customer["profession"] : '';
                }

                if(isset($value['due_date']))
                    $value['overdue_days'] = time() - $value['due_date'];
                
                $result[] = $value;    
            }
        }

    }

    return $result;
}


function createRelationshipTable($zaccf, $account_number, $mongo_db){
    global $mongo_db;

    $REF = [];
    $phoneArr = [];
    
    if(!empty($zaccf["WRK_REF"]) && $zaccf["WRK_REF"] != null && $zaccf["WRK_REF"] != '' && $zaccf["WRK_REF"] != ' '){

        $raw        = $zaccf['WRK_REF'];
        $splited    = explode("-", $raw);

        if(count($splited) >=3){
            $REF[0]['account_number']   = $account_number;
            $REF[0]['PRODGRP_ID']       = $zaccf['PRODGRP_ID'];
            $REF[0]['LIC_NO']           = $zaccf['LIC_NO'];
            $REF[0]['name']             = $splited[0];
            $REF[0]['relation']         = $splited[1];
            $REF[0]['phone']            = $splited[count($splited) - 1];
            $phoneArr[]                 = $REF[0]['phone'];
        }

        $REF[0]['raw']                  = $raw;

        pushToQueueImport($REF[0]);
    }

    for($i=1; $i<=7; $i++){
        if(!empty($zaccf["WRK_REF$i"]) && $zaccf["WRK_REF$i"] != null && $zaccf["WRK_REF$i"] != '' && $zaccf["WRK_REF$i"] != ' '){
            $raw = $zaccf["WRK_REF$i"];
            $splited = explode("-", $raw);
            if(count($splited) >=3){
               $REF[$i]['account_number']   = $account_number;
               $REF[$i]['PRODGRP_ID']       = $zaccf['PRODGRP_ID'];
               $REF[$i]['LIC_NO']           = $zaccf['LIC_NO'];
               $REF[$i]['name']             = $splited[0];
               $REF[$i]['relation']         = $splited[1];
               $REF[$i]['type']             = $splited[count($splited) - 2];
               $REF[$i]['phone']            = $splited[count($splited) - 1];
               $phoneArr[]                  = $REF[$i]['phone'];
            }
            $REF[$i]['raw']     = $raw;

            pushToQueueImport($REF[$i]);
        }
    }

    return $phoneArr;
    
}

function pushToQueueImport($doc) {
    global $queue;

    $queueData = array(
        "startTimestamp"    => time(),
        "doc"               => $doc,
        "collection"        => 'LO_Relationship',
        "key_field"         => 'raw'
    );

    $queue->useTube('import')->put(json_encode($queueData));
}

function brint($txt) {
    if(gettype($txt) != 'object' && gettype($txt) != 'array')
        print_r($txt . PHP_EOL);
    else
        print_r($txt);
}