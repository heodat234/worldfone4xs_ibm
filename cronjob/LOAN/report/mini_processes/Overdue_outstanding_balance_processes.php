<?php
require_once "Mongo_db_process.php";
$mongo_db = new Mongo_db();
$group_name = base64_decode($argv[1]);
$num_days_before_next_due = $argv[2];
$due_date_time_stamp = $argv[3];
$debt_group = $argv[4];
$type_collection = $argv[5];

$num_days_before_next_due += 2;
$type = 'Overdue_outstanding_balance';
$duedate_string = date('Y-m-d', $due_date_time_stamp);
for ($i = 1; $i <= $num_days_before_next_due; $i++) {

    $date = date('Y-m-d', $due_date_time_stamp + 86400 * $i);
    $temp_name = str_replace($duedate_string, $date, $group_name);
    $group_id = ($type_collection == 'SIBS') ? "A$debt_group" : "A-$debt_group";
    $key_field = ($type_collection == 'SIBS') ? "current_balance" : "cur_bal" ;

    if($i == $num_days_before_next_due -1){
        $next_due_date_name = $temp_name;
    }
    if($i == $num_days_before_next_due){
        $begin_date_duedate = $due_date_time_stamp + 86400 * $i;
        $total_balance = calculateFinalNumber($next_due_date_name, $group_id, $begin_date_duedate, $key_field);
    }else{
        $total_balance = countTotalOverdueOutstandingBalance($temp_name, $key_field, $group_id);
    }


    $total_balance = countTotalOverdueOutstandingBalance($temp_name, $key_field, $group_id);

    $mongo_db->where(array('key' => $group_name, 'days' => $i, 'type' => $type))->update('LO_Working_date_report',
        ['$set' => array(
            'key' => $group_name,
            'Overdue_outstanding_balance' => $total_balance,
            'type' => $type, 'days' => $i,
            'from' => $temp_name,
            'createdAt' => time(),
        ),
        ],
        array('upsert' => true));
    echo $i . PHP_EOL;

    echo $date . PHP_EOL;
}

function countTotalOverdueOutstandingBalance($name, $key_field, $group_id)
{
    global $mongo_db;

    $total = 0;
    $dial_details = $mongo_db->where('from', $name)->where('group_id', $group_id)->get('LO_Diallist_detail');
    foreach ($dial_details as $key => $dial) {
        $total += isset($dial[$key_field]) ? (int) $dial[$key_field] : 0;
    }

    return $total;
}

function calculateFinalNumber($next_due_date_name, $group_id, $begin_date_duedate, $key_field){
    global $mongo_db;
    
    $before_due_date_data = $mongo_db->where('from', $next_due_date_name)->where('group_id', $group_id)->select(array('account_number'))->get('LO_Diallist_detail');
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