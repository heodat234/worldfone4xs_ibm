<?php
require_once "Mongo_db_process.php";
$mongo_db = new Mongo_db();
$group_name = $argv[1];
$num_days_before_next_due = $argv[2];
$due_date_plus1 = $argv[3];
$due_date_time_stamp = $due_date_plus1 - 86400;
$type_collection = $argv[4];

$num_days_before_next_due += 2;
$type = 'No_of_overdue_accounts';
$duedate_string = date('Y-m-d', $due_date_time_stamp);
$date = date('Y-m-d', $due_date_plus1);
$temp_name = str_replace($date, $duedate_string, $group_name);

for ($i = 1; $i <= $num_days_before_next_due; $i++) {

    if($i == $num_days_before_next_due -1){
        $next_due_date_name = $group_name;
    }
    if($i == $num_days_before_next_due){
        $begin_date_duedate = $due_date_time_stamp + 86400 * $i;
        $accounts = calculateFinalNumber($next_due_date_name, $begin_date_duedate);
    }else{
        $accounts = getNumberAccounts_by_From_B_plus($group_name);
    }

    $mongo_db->where(array('key' => $temp_name, 'days' => $i, 'type' => $type))->update('LO_Working_date_report',
        ['$set' => array(
            'key' => $temp_name,
            'no_of_overdue_accounts' => $accounts,
            'type' => $type, 'days' => $i,
            'from' => $group_name,
            'createdAt' => time(),
        ),
    ],
    array('upsert' => true));
    
    $date2 = date('Y-m-d', $due_date_plus1 + 86400 * $i);
    $group_name = str_replace($date, $date2, $group_name);
    $date = $date2;
}

function getNumberAccounts_by_From_B_plus($name)
{
    global $mongo_db;
    return $mongo_db->where('from', $name)->count('LO_Diallist_detail');
}

function calculateFinalNumber($next_due_date_name, $begin_date_duedate){
    $before_due_date_data = $mongo_db->where('from', $next_due_date_name)->select(array('account_number'))->get('LO_Diallist_detail');
    $total =0;
    $to_time = $begin_date_duedate + 86300;
    foreach ($before_due_date_data as $key => $dial) {
        $account_number = $dial['account_number'];

        $count = $mongo_db->where('account_number', $account_number)->where('createdAt', ['$gte' => $begin_date_duedate, '$lte' => $to_time])->count('LO_Diallist_detail');
        if($count != 0) $total++;
    }

    return $total;
}