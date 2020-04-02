<?php

function processSIBS_A($SIBS_A)
{
    $result = [];

    $result['group_name'] = [];
    $result['lead_id'] = [];
    $search = ["/G1", "/G2", "/G3"];

    foreach ($SIBS_A as $key => $value) {
        $result['group_name'][] = str_replace($search, '', $value['name']);
        $result['lead_id'][] = $value['lead'];
    }
    $result['group_name'] = array_values(array_unique($result['group_name']));
    $result['lead_id'] = array_values(array_unique($result['lead_id']));
    return $result;
}

function processCountAccounts($lead_id, $due_date, $debt_group)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $string = MANV . $value . '_' . $due_date;
        $result[] = getNumberAccounts_by_From($string, $debt_group);
    }
    return $result;
}

function processCountAccountsTarget($accounts_each_group, $target)
{
    $result = [];
    foreach ($accounts_each_group as $key => $value) {
        $result[] = $value * $target / 100;
    }
    return $result;
}

function processTotalBalance($lead_id, $due_date, $debt_group)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $string = MANV . $value . '_' . $due_date;
        $data = getTotalCurrentBalance($string, $debt_group);
        $result[] = isset($data[0]) ? $data[0]['total_current_balance'] : 0;
    }
    return $result;
}

function processTotalBalanceTarget($total_current_balance, $target)
{
    $result = [];
    foreach ($total_current_balance as $key => $value) {
        $result[] = $value * $target / 100;
    }
    return $result;
}

function processCountNumDaysBeforeDue($due_date_timstamp, $debt_group)
{
    $next_due = getNextDueTimestamp($due_date_timstamp, $debt_group);
    $days = $next_due - $due_date_timstamp;

    return $days / 86400;
}

function processGet_No_of_overdue_accounts($lead_id, $due_date)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $key = MANV . $value . '_' . $due_date;
        $result[$key] = getNo_of_overdue_accounts($key);
    }

    return $result;
}

function processGet_Overdue_outstanding_balance($lead_id, $due_date)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $key = MANV . $value . '_' . $due_date;
        $result[$key] = getOverdue_outstanding_balance($key);
    }

    return $result;
}

function processCard_A($Card_A)
{
    $result = [];

    $result['group_name'] = [];
    $result['lead_id'] = [];
    $search = ["/G1", "/G2", "/G3"];

    foreach ($Card_A as $key => $value) {
        $group_name = $result['group_name'][] = str_replace($search, '', $value['name']);
        $temp = explode('/', $group_name);
        $result['lead_id'][] = base64_encode($temp[count($temp) -1]);
    }
    $result['group_name'] = array_values(array_unique($result['group_name']));
    $result['lead_id'] = array_values(array_unique($result['lead_id']));
    return $result;
}

function processCountAccounts_CardA($lead_id, $due_date, $debt_group)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $string = CARD . '_' . $value . '_' . $due_date;
        $result[] = getNumberAccounts_by_From_Card_A($string, $debt_group);
    }
    return $result;
}

function processTotalBalanceCard_A($lead_id, $due_date, $debt_group)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $string = CARD .'_' . $value . '_' . $due_date;
        $data = getTotalCurrentBalance_Card_A($string, $debt_group);
        $result[] = isset($data[0]) ? $data[0]['total_current_balance'] : 0;
    }
    return $result;
}

function processGet_No_of_overdue_accounts_Card_A($lead_id, $due_date)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $key = CARD . '_' . $value . '_' . $due_date;
        $result[$key] = getNo_of_overdue_accounts($key);
    }

    return $result;
}

function processGet_Overdue_outstanding_balance_Card_A($lead_id, $due_date)
{
    $result = [];
    foreach ($lead_id as $key => $value) {
        $key = CARD .'_' . $value . '_' . $due_date;
        $result[$key] = getOverdue_outstanding_balance($key);
    }

    return $result;
}

function processCountAccounts_B_Plus($group_name, $due_date_timstamp)
{
    $result = [];
    $result[] = getNumberAccounts_by_From_B_plus($group_name, $due_date_timstamp);
    return $result;
}

function processTotalBalance_B_Plus($group_name, $due_date_timstamp, $typeDebt = 'SIBS')
{
    $result = [];
    $temp = getTotalCurrentBalance_B_Plus($group_name, $due_date_timstamp, $typeDebt);
    $result[] = isset($temp[0]) ? $temp[0]['total_current_balance'] : 0;
    return $result;
}

function processGet_No_of_overdue_accounts_B_Plus($key, $due_date)
{
    $result = [];
    $result[$key] = getNo_of_overdue_accounts($key);

    return $result;
}

function processGet_Overdue_outstanding_balance_B_Plus($key, $due_date)
{
    $result = [];

    $result[$key] = getOverdue_outstanding_balance($key);

    return $result;
}

function processGet_Payment_amount_received($key, $due_date)
{
    $result = [];

    $result[$key] = getPayment_amount_received($key);
    return $result;
}

