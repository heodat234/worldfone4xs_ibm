<?php
require_once dirname(__DIR__) . "/Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();

$collection = "LO_Diallist_detail";

// $list_diallist_detail_miss_phone = $mongo_db->where(array('$or' => array(array('createdAt' => array('$gte' => 1585069200)), array('updatedAt' => array('$gte' => 1585069200))), 'mobile_num' => ''))->count($collection);
// print_r($list_diallist_detail_miss_phone);
$list_diallist_detail_miss_phone = $mongo_db->where(array('$or' => array(array('createdAt' => array('$gte' => 1585069200)), array('updatedAt' => array('$gte' => 1585069200))), 'mobile_num' => ''))->get($collection);
foreach($list_diallist_detail_miss_phone as $key => $value) {
    $zaccf = $mongo_db->where(array('account_number' => $value['account_number']))->getOne('LO_ZACCF');
    if(!empty($zaccf)) {
        $phoneArr = createRelationshipTable($zaccf, $value[$fieldNo], $mongo_db);
        $value['other_phones'] = $phoneArr;
        $value['mobile_num'] = '';
        if($zaccf['MOBILE_NO'] != 'NA')
            $value["mobile_num"] = $zaccf['MOBILE_NO'];
        else
            $value['mobile_num'] = isset($phoneArr[0]) ? $phoneArr[0] : 0;

        $value['House_NO'] = isset($zaccf['House_NO']) ? $zaccf['House_NO'] : '';
        if ($value['House_NO'] != '' && strlen($value['House_NO']) > 7) {
            $value['other_phones'][] = $value['House_NO'];
        }

        $value['OFFICE_NO'] = isset($zaccf['OFFICE_NO']) ? $zaccf['OFFICE_NO'] : '';
        if ($value['OFFICE_NO'] != '' && strlen($value['OFFICE_NO']) > 7) {
            $value['other_phones'][] = $value['OFFICE_NO'];
        }

        $value['address'] = _isset($zaccf, 'ADDR_1') . _isset($zaccf, 'ADDR_2') . _isset($zaccf, 'ADDR_3');

        $temp = '';
        $temp .= isset($zaccf['WRK_PST']) ? $zaccf['WRK_PST'] : '';
        $temp .= isset($zaccf['W_CFBUST']) ? $zaccf['W_CFBUST'] : '';
        if ($temp == '') {
            $temp .= isset($zaccf['work_position2']) ? $zaccf['work_position2'] : '';
            $temp .= isset($zaccf['work_position']) ? '-' . $zaccf['work_position'] : '';
        }
        $value['WRK_PST'] = $temp;
        $value['F_PDT'] = isset($zaccf['F_PDT']) ? $zaccf['F_PDT'] : '';
        $value['W_ORG'] = isset($zaccf['W_ORG']) ? $zaccf['W_ORG'] : '';

        if (isset($zaccf['CUS_SEX'])) {
            $value['CUS_SEX'] = ($zaccf['CUS_SEX'] == 0) ? 'NỮ' : 'NAM';
        }

        $mongo_db->where(array('account_number' => $value['account_number']))->set($value)->update($collection);

    }
}


function createRelationshipTable($zaccf, $account_number, $mongo_db)
{
    global $mongo_db;

    $REF = [];
    $phoneArr = [];

    if (!empty($zaccf["WRK_REF"]) && $zaccf["WRK_REF"] != null && $zaccf["WRK_REF"] != '' && $zaccf["WRK_REF"] != ' ') {

        $raw = $zaccf['WRK_REF'];
        $splited = explode("-", $raw);

        if (count($splited) >= 3) {
            $REF[0]['account_number'] = $account_number;
            $REF[0]['PRODGRP_ID'] = $zaccf['PRODGRP_ID'];
            $REF[0]['LIC_NO'] = $zaccf['LIC_NO'];
            $REF[0]['name'] = $splited[0];
            $REF[0]['relation'] = $splited[1];
            $REF[0]['phone'] = $splited[count($splited) - 1];
            $phoneArr[] = $REF[0]['phone'];
        }

        $REF[0]['raw'] = $raw;

        pushToQueueImport($REF[0]);
    }

    for ($i = 1; $i <= 7; $i++) {
        if (!empty($zaccf["WRK_REF$i"]) && $zaccf["WRK_REF$i"] != null && $zaccf["WRK_REF$i"] != '' && $zaccf["WRK_REF$i"] != ' ') {
            $raw = $zaccf["WRK_REF$i"];
            $splited = explode("-", $raw);
            if (count($splited) >= 3) {
                $REF[$i]['account_number'] = $account_number;
                $REF[$i]['PRODGRP_ID'] = $zaccf['PRODGRP_ID'];
                $REF[$i]['LIC_NO'] = $zaccf['LIC_NO'];
                $REF[$i]['name'] = $splited[0];
                $REF[$i]['relation'] = $splited[1];
                $REF[$i]['type'] = $splited[count($splited) - 2];
                $REF[$i]['phone'] = $splited[count($splited) - 1];
                $phoneArr[] = $REF[$i]['phone'];
            }
            $REF[$i]['raw'] = $raw;

            pushToQueueImport($REF[$i]);
        }
    }

    return $phoneArr;

}