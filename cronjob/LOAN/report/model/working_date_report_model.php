<?php
require_once "Mongo_db_report.php";
$mongo_db = new Mongo_db();

function getReportDueDate()
{
    global $mongo_db;

    $midnight = strtotime('today midnight');
    $due_date = $mongo_db->where("due_date", ['$lte' => $midnight])->offset(1)->order_by(['due_date' => -1])->getOne('LO_Report_due_date');
    // $due_date = $mongo_db->where("for_month", "2")->order_by(['due_date' => 1])->getOne('LO_Report_due_date');
    return $due_date;
}

function getTargetReport($group_name, $kydue)
{
    global $mongo_db;
    $group_alphabet = substr($group_name, -1);
    $group_name = $group_name . '/' . $group_alphabet . $kydue;
    $target_data = $mongo_db->where('name', $group_name)->getOne('LO_Target_of_report');
    return $target_data['target'] ? $target_data['target'] : 0;
}

function getGroup_byName($regex_name)
{
    global $mongo_db;

    $group_A = $mongo_db->select(['name', 'members', 'lead'])->like('name', $regex_name)->get('LO_Group');
    return $group_A;
}

function getNumberAccounts_by_From($name, $debt_group)
{
    global $mongo_db;

    return $mongo_db->where('from', $name)->where('group_id', "A$debt_group")->count('LO_Diallist_detail');
}

function getTotalCurrentBalance($name, $debt_group)
{
    global $mongo_db;

    $result = $mongo_db->aggregate_pipeline('LO_Diallist_detail', array(
        array(
            '$match' => array('from' => $name, 'group_id' => "A$debt_group"),
        ),
        array(
            '$group' => array(
                '_id' => '$from',
                'total_current_balance' => array('$sum' => '$current_balance'),
            ),
        ),
    ));

    return $result;
}

function getNumberAccounts_by_From_Card_A($name, $debt_group)
{
    global $mongo_db;

    return $mongo_db->where('from', $name)->where('group_id', "A-$debt_group")->count('LO_Diallist_detail');
}

function getTotalCurrentBalance_Card_A($name, $debt_group)
{
    global $mongo_db;

    $result = $mongo_db->aggregate_pipeline('LO_Diallist_detail', array(
        array(
            '$match' => array('from' => $name, 'group_id' => "A-$debt_group"),
        ),
        array(
            '$group' => array(
                '_id' => '$from',
                'total_current_balance' => array('$sum' => '$cur_bal'),
            ),
        ),
    ));
    return $result;
}

function getNextDueTimestamp($due_date_timstamp, $debt_group)
{
    global $mongo_db;

    $result = $mongo_db->where('due_date', ['$gt' => $due_date_timstamp])->where('debt_group', $debt_group)->order_by(array('_id' => 1))->getOne('LO_Report_due_date');
    return $result['due_date'];
}

function getNo_of_overdue_accounts($key)
{
    global $mongo_db;

    $result = $mongo_db->where(array('key' => $key, 'type' => 'No_of_overdue_accounts'))->order_by(['days' => 1])->get('LO_Working_date_report');
    $result2 = [];
    foreach ($result as $key => $value) {
        $result2[] = $value['no_of_overdue_accounts'];
    }

    return $result2;
}

function getOverdue_outstanding_balance($key)
{
    global $mongo_db;

    $result = $mongo_db->where(array('key' => $key, 'type' => 'Overdue_outstanding_balance'))->order_by(['days' => 1])->get('LO_Working_date_report');
    $result2 = [];
    foreach ($result as $key => $value) {
        $result2[] = $value['Overdue_outstanding_balance'];
    }
    return $result2;
}

function getNumberAccounts_by_From_B_plus($name, $due_date_timstamp)
{
    // tam thoi bo loc due_date
    global $mongo_db;
    return $mongo_db->where('from', $name)->count('LO_Diallist_detail');
}

function getTotalCurrentBalance_B_Plus($name, $due_date_timstamp, $typeDebt)
{
    global $mongo_db;

    $keyField = ($typeDebt == 'SIBS') ? '$current_balance' : '$cur_bal';
    $result = $mongo_db->aggregate_pipeline('LO_Diallist_detail', array(
        array(
            '$match' => array('from' => $name),
        ),
        array(
            '$group' => array(
                '_id' => '$from',
                'total_current_balance' => array('$sum' => $keyField),
            ),
        ),
    ));
    return $result;
}

function getPayment_amount_received($key)
{
    global $mongo_db;

    $result = $mongo_db->where(array('key' => $key, 'type' => 'Payment_amount_received'))->order_by(['days' => 1])->get('LO_Working_date_report');
    $result2 = [];
    foreach ($result as $key => $value) {
        $result2[] = $value['payment_amount_received'];
    }
    return $result2;
}