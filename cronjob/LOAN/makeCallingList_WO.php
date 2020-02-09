<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "makeCallingList_logs.txt");

require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db               = new Mongo_db();
$arr_contractNo_partner = [];
$WO                     = [];
$today                  = date('Y-m-d', time());


$cus_assigned_partner   = $mongo_db->select(array("CONTRACTNR"))->get('LO_Cus_assigned_partner');

foreach ($cus_assigned_partner as $key => $value) {
    $arr_contractNo_partner[] = $value['CONTRACTNR'];
}

    $WO                                 = $mongo_db->get('LO_WO_monthly');
    $WO_afterRemove                     = removeAssigned($arr_contractNo_partner, $WO, "ACCTNO");

    $mongo_db->switch_db('LOAN_campaign_list');
    
    if($WO_afterRemove) $mongo_db->batch_insert('WO_' . $today, $WO_afterRemove);
    echo "DONE WO";


function removeAssigned($arr_contractNo_partner, $arrData, $fieldNo) {
    global $mongo_db;
    $result = [];
    foreach ($arrData as $key => $value) {
        if(isset($value[$fieldNo])){
            if(!in_array($value[$fieldNo], $arr_contractNo_partner)){

                $zaccf      = $mongo_db->where('account_number', $value[$fieldNo])->getOne('LO_ZACCF');
                $customer   = $mongo_db->where('account_number', $value[$fieldNo])->getOne('LO_Customer');

                $report_release_sale = $mongo_db->where("acc_no", $value[$fieldNo])->getOne('LO_Report_release_sale');
                if(!empty($zaccf)){
                    $phoneArr               = createRelationshipTable($zaccf, $value[$fieldNo], $mongo_db);
                    $value['other_phones']  = $phoneArr;

                    $value['PRODGRP_ID']    = isset($zaccf['PRODGRP_ID'])   ? $zaccf['PRODGRP_ID']  : '' ;
                    $value['LIC_NO']        = isset($zaccf['LIC_NO']    )   ? $zaccf['LIC_NO']      : '' ;
                    $value['BIR_DT8']       = isset($zaccf['BIR_DT8']   )   ? $zaccf['BIR_DT8']     : '' ;
                    $value['House_NO']      = isset($zaccf['House_NO']  )   ? $zaccf['House_NO']    : '' ;
                    $value['OFFICE_NO']     = isset($zaccf['OFFICE_NO'] )   ? $zaccf['OFFICE_NO']     : '' ;

                    $temp                   = '';
                    $temp                   .= isset($zaccf['WRK_PST']) ? $zaccf['WRK_PST'] : '';
                    $temp                   .= isset($zaccf['W_CFBUST']) ? $zaccf['W_CFBUST'] : '';
                    $value['WRK_PST']       = $temp;

                }


                $value['temp_address']      = isset($report_release_sale['temp_address']) 
                && isset($report_release_sale['temp_district'])
                && isset($report_release_sale['temp_province']) ? 
                $zaccf['temp_address'] .' '. $zaccf['temp_district'] .' ' . $zaccf['temp_province'] :
                '' ;
                $value['permanent_address'] = isset($report_release_sale['address']) ? $report_release_sale['address'] : '';

                unset($value['id']);
                if(!empty($customer)){
                    $value['action_code'] = isset($customer["action_code"]) ? $customer["action_code"] : '';
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
            $REF[0]['PRODGRP_ID']   = $zaccf['PRODGRP_ID'];
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
               $REF[$i]['account_number']    = $account_number;
               $REF[$i]['PRODGRP_ID']       = $zaccf['PRODGRP_ID'];
               $REF[0]['LIC_NO']           = $zaccf['LIC_NO'];
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

    /*if(!empty($REF)):
        $mongo_db->batch_insert('LO_Relationship', $REF);
    endif;*/
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