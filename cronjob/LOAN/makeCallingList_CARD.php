<?php
require_once "Function/card_function.php";
//function start with func_

require_once "Model/card_model.php";
//function start with get

//other functions are declared here or Header.php

$CARD = [];
$today = date('Y-m-d', time());

$group_mapping = getGroupMappingCampaign();
$xoay_vong_chia_nhom_by_id = processXoayVongChiaNhom($group_mapping);
$cus_assigned_partner = getCusAssignPartner();

$listofaccount = getListOfAccount();

$afterRemove = processData($cus_assigned_partner, $listofaccount, "account_number");

$mongo_db->switch_db('LOAN_campaign_list');

if ($afterRemove) {
    $mongo_db->batch_insert('CARD_' . $today, $afterRemove);
}

$arrName = splitByDueDay('CARD_' . $today);

// $arrName = [2=> 'CARD_02_2019-12-26', 3=> 'CARD_03_2019-12-26', 12 => 'CARD_12_2019-12-26', 22 => 'CARD_22_2019-12-26', 23 => 'CARD_23_2019-12-26', 30 => 'CARD_30_2019-12-26', 31 => 'CARD_31_2019-12-26'];
// $arrName = [12=> 'CARD_12_2020-02-05'];

foreach ($arrName as $key => $value) {
    splitGroupCard($value, (int) $key);
}

echo "DONE CARD";

function processData($cus_assigned_partner, $arrData, $fieldNo)
{
    global $mongo_db;
    $result = [];
    $to = count($arrData);

    for ($i = 0; $i < $to; $i++) {
        $value = $arrData[$i];
        if (!isset($value[$fieldNo])) {
            continue;
        }

        $account_number = $value[$fieldNo];
        if (!in_array($account_number, $cus_assigned_partner)) {

            $kydue = func_getKyDue($value["overdue_date"]);

            $SBV = getSBV($account_number, $kydue);
            if (empty($SBV)) {
                continue;
            }

            $value['phone'] = '';
            $value = processSBV($SBV, $value, $account_number);

            $report_release_sale = getReportReleaseSale($account_number);
            $value['temp_address'] = _isset($report_release_sale, 'temp_address') . _isset($report_release_sale, 'temp_district') . _isset($report_release_sale, 'temp_province');
            '';
            $value['permanent_address'] = _isset($report_release_sale, 'address') . _isset($report_release_sale, 'district') . _isset($report_release_sale, 'province');

            unset($value['id']);

            if (isset($value["overdue_date"])) {
                $value['overdue_days'] = time() - $value["overdue_date"];
            }

            $result[] = $value;
        }

    } // endfor

    return $result;
}

function processSBV($SBV, $value, $account_number)
{

    $zaccf = getZACCF($SBV['license_no']);

    if (!empty($zaccf)) {
        $phoneArr = func_createRelationshipTable($zaccf, $account_number);
        $value['other_phones'] = $phoneArr;
        $value['phone'] = $zaccf['MOBILE_NO'];

        $value['PRODGRP_ID'] = _isset($zaccf, 'PRODGRP_ID');
        $value['LIC_NO'] = _isset($zaccf, 'LIC_NO');
        $value['BIR_DT8'] = _isset($zaccf, 'cif_birth_date');

        $value['House_NO'] = _isset($zaccf, 'House_NO');
        if ($value['House_NO'] != '' && strlen($value['House_NO']) > 7) {
            $value['other_phones'][] = $value['House_NO'];
        }

        $value['OFFICE_NO'] = _isset($zaccf, 'OFFICE_NO');
        if ($value['OFFICE_NO'] != '' && strlen($value['OFFICE_NO']) > 7) {
            $value['other_phones'][] = $value['OFFICE_NO'];
        }

        $value['W_ORG'] = _isset($zaccf, 'W_ORG');
        $value['F_PDT'] = _isset($zaccf, 'F_PDT');
        $value['address'] = _isset($zaccf, 'ADDR_1') . _isset($zaccf, 'ADDR_2') . _isset($zaccf, 'ADDR_3');

        $temp = '';
        $temp .= _isset($zaccf, 'work_position2');
        $temp .= _isset($zaccf, 'work_position');
        $value['WRK_PST'] = $temp;

        if (isset($zaccf['CUS_SEX'])) {
            $value['CUS_SEX'] = ($zaccf['CUS_SEX'] == 0 || $zaccf['CUS_SEX'] == '0') ? 'NỮ' : 'NAM';
        }

    } else {
        $value['phone'] = $SBV['phone'];
        $value["LIC_NO"] = $SBV["license_no"];
        $value["address"] = $SBV["address"];
        $value['WRK_PST'] = _isset($SBV, 'job_design_code');
        $value['BIR_DT8'] = _isset($SBV, 'cif_birth_date');
        $value['check'] = _isset($SBV, 'job_design_code');
        $value['BIR_DT8'] = isset($SBV['cif_birth_date']) ? date('d-m-Y', $SBV['cif_birth_date']) : '';

        if (isset($SBV['gender'])) {
            $value['CUS_SEX'] = ($SBV['gender'] == '0' || $SBV['gender'] == 0) ? 'NỮ' : 'NAM';
        }
    }
    $customer = getOneCustomer($account_number);
    if (!empty($customer)) {
        $value['action_code'] = _isset($customer, 'action_code');
        $value['profession'] = _isset($customer, 'profession');
    }

    $value["delinquency_group"] = _isset($SBV, 'delinquency_group');
    $value["overdue_indicator"] = _isset($SBV, 'overdue_indicator');

    if (isset($value["overdue_date"])) {
        $kydue = func_getKyDue($value["overdue_date"]);
        $value["group_id"] = $value['overdue_indicator'] . '-' . $kydue;
    }

    if ($value["overdue_indicator"] == "A") {
        $value['G_type'] = func_define_G_type($value["overdue_date"]);
    }

    return $value;
}

function splitByDueDay($collection_name)
{
    global $mongo_db, $today;
    $arrName = [];

    $due_dates = $mongo_db->distinct($collection_name, "overdue_date");
    foreach ($due_dates as $key => $value) {
        $temp = $mongo_db->where("overdue_date", $value)->get($collection_name);
        $mongo_db->batch_insert('CARD_' . date('d', $value) . "_" . $today, $temp);
        $arrName[date('d', $value)] = 'CARD_' . date('d', $value) . "_" . $today;
    }
    return $arrName;
}

function splitGroupCard($collection_name, $key)
{
    global $mongo_db, $today, $xoay_vong_chia_nhom_by_id;
    for ($i = 1; $i <= 5; $i++):
        switch ($i) {
            case 1:
                $group = '01';
                $type = 'A';
                break;
            case 2:
                $group = '02';
                $type = 'B';
                break;
            case 3:
                $group = '03';
                $type = 'C';
                break;
            case 4:
                $group = '04';
                $type = 'D';
                break;
            case 5:
                $group = '05';
                $type = 'E';
                break;
            default:
                break;
        }
        $total = $mongo_db->where("overdue_indicator", $type)->count($collection_name);
        if ($i == 1) {

            if ($total > 0):

                $data = $mongo_db->where("overdue_indicator", $type)->get($collection_name);
                foreach ($data as $key_d => $val) {
                    $owner_check = check_owner_group_remember($val);
                    if ($owner_check !== false) {
                        $val['check_owner_group_remember'] = true;
                        $mongo_db->insert('CARD_' . $owner_check . '_' . $today, $val);
                    } else {
                        $id_teamleader = $xoay_vong_chia_nhom_by_id[0];
                        $val['check_owner_group_remember'] = false;
                        $mongo_db->insert('CARD_' . $id_teamleader . '_' . $today, $val);
                        $xoay_vong_chia_nhom_by_id = func_reorder_array($xoay_vong_chia_nhom_by_id);
                    }

                } // foreach

            endif;

        } else {
            if ($total > 0):
                if ($key >= 12 && $key <= 21) {
                    $kydue = '01';
                } else if ($key >= 22 && $key <= 30) {
                $kydue = '02';
            } else {
                $kydue = '03';
            }
            $data = $mongo_db->where("overdue_indicator", $type)->get($collection_name);
            if (!empty($data)) {
                $mongo_db->batch_insert('CARD_' . $type . $kydue . '_' . $today, $data);
            }

        endif;
    }

    endfor;

}

function check_owner_group_remember($data)
{
    global $mongo_db;
    global $group_mapping;
    $collection = 'LO_Diallist_detail';
    $mongo_db->switch_db('worldfone4xs');
    $result = false;

    if (isset($data["account_number"])) {
        $dial_detail = $mongo_db->
            where('account_number', $data['account_number'])->
            where('overdue_date', $data['overdue_date'])->
            where(
            array(
                '$and' => array(
                    array("owner_group_remember" => array('$exists' => true)),
                    array('owner_group_remember' => array('$ne' => false)),
                    array('owner_group_remember' => array('$ne' => true)),
                ),
            )
        )->
            order_by(array('_id' => -1))->
            getOne($collection);

        if (empty($dial_detail)) {
            $mongo_db->switch_db('LOAN_campaign_list');
            return $result;
        }

        if (isset($dial_detail["owner_group_remember"])) {
            $result = processGetTeamLeader($dial_detail["owner_group_remember"]);
            $mongo_db->switch_db('LOAN_campaign_list');
            return base64_encode($result);
        }
    }
    $mongo_db->switch_db('LOAN_campaign_list');
    return $result;
}

function processXoayVongChiaNhom($group_mapping)
{
    $result = [];
    foreach ($group_mapping as $key => $value) {
        $result[] = base64_encode(processGetTeamLeader($value['name']));
    }
    $result = array_values(array_unique($result));

    return $result;
}

function processGetTeamLeader($group_name)
{
    $temp = explode('/', $group_name);
    return $temp[2];
}