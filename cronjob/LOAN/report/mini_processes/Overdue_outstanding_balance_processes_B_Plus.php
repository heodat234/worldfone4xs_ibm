<?php
require_once "Mongo_db_process.php";
$mongo_db = new Mongo_db();
$group_name = $argv[1];
$num_days_before_next_due = $argv[2];
$due_date_plus1 = $argv[3];
$due_date_time_stamp = $due_date_plus1 - 86400;
$type_collection = $argv[4];

$num_days_before_next_due += 2;
$type = 'Overdue_outstanding_balance';
$duedate_string = date('Y-m-d', $due_date_time_stamp);
$date = date('Y-m-d', $due_date_plus1);
$temp_name = str_replace($date, $duedate_string, $group_name);

for ($i = 1; $i <= $num_days_before_next_due; $i++) {

    $key_field = ($type_collection == 'SIBS') ? "current_balance" : "cur_bal" ;

    if($i == $num_days_before_next_due -1){
        $next_due_date_name = $group_name;
    }
    if($i == $num_days_before_next_due){
        $begin_date_duedate = $due_date_time_stamp + 86400 * $i;
        $total_balance = calculateFinalNumber($next_due_date_name, $begin_date_duedate, $key_field);
    }else{
        $total_balance = countTotalOverdueOutstandingBalance($group_name, $key_field);
    }

    $mongo_db->where(array('key' => $temp_name, 'days' => $i, 'type' => $type))->update('LO_Working_date_report',
        ['$set' => array(
            'key' => $temp_name,
            'Overdue_outstanding_balance' => $total_balance,
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

function countTotalOverdueOutstandingBalance($name, $key_field)
{
    global $mongo_db;

    $total = 0;
    $dial_details = $mongo_db->where('from', $name)->get('LO_Diallist_detail');
    foreach ($dial_details as $key => $dial) {
        $total += isset($dial[$key_field]) ? (int) $dial[$key_field] : 0;
    }

    return $total;
}

function calculateFinalNumber($next_due_date_name, $begin_date_duedate, $key_field){
    global $mongo_db;
    
    $before_due_date_data = $mongo_db->where('from', $next_due_date_name)->select(array('account_number'))->get('LO_Diallist_detail');
    $total =0;
    $to_time = $begin_date_duedate + 86300;
    foreach ($before_due_date_data as $key => $dial) {
        $account_number = $dial['account_number'];

        $data = $mongo_db->where('account_number', $account_number)->where('createdAt', ['$gte' => $begin_date_duedate, '$lte' => $to_time])->get('LO_Diallist_detail');
        foreach ($data as $key => $data_i) {
            $total += isset($data_i[$key_field]) ? (int) $data_i[$key_field] : 0;
        }

    }

    return $total;
}