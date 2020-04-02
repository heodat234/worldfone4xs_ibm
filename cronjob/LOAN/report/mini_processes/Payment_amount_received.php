<?php
require_once "Mongo_db_process.php";
$mongo_db = new Mongo_db();
$group_name = $argv[1];
$num_days_before_next_due = $argv[2];
$due_date_plus1 = $argv[3];
$due_date_time_stamp = $due_date_plus1 - 86400;
$type_collection = $argv[4];

$num_days_before_next_due += 2;
$type = 'Payment_amount_received';
$duedate_string = date('Y-m-d', $due_date_time_stamp);
$date = date('Y-m-d', $due_date_plus1);
$temp_name = str_replace($date, $duedate_string, $group_name);

for ($i = 1; $i <= $num_days_before_next_due; $i++) {
    $payemnt_date_from = $due_date_time_stamp + 86400 * $i;

    $payment = $type_collection == 'SIBS' ? getLN306F($payemnt_date_from) : get_Report_input_payment_of_card($payemnt_date_from);
    $mongo_db->where(array('key' => $temp_name, 'days' => $i, 'type' => $type))->update('LO_Working_date_report',
        [
            '$set' => array(
                'key' => $temp_name,
                'payment_amount_received' => $payment,
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

function getLN306F($payemnt_date_from)
{
    global $mongo_db;

    $payemnt_date_from += 86400;
    $payemnt_date_to = $payemnt_date_from + 86400;
    $result = $mongo_db->aggregate_pipeline('LO_LN3206F', array(
        array(
            '$match' => array(
                'created_at' => array(
                    '$gte' => $payemnt_date_from,
                    '$lte' => $payemnt_date_to,
                ),
                'code' => '10'
            )
        ),
        array(
            '$group' => array(
                "_id" => '$code',
                'totalAmount' => array('$sum' => '$amt'),
                'count' => array('$sum' => 1),
            )
        ),
    ));
    return !empty($result) ? $result[0]['totalAmount'] : 0;
}

function get_Report_input_payment_of_card($payemnt_date_from){
    global $mongo_db;

    $payemnt_date_from += 86400;
    $payemnt_date_to = $payemnt_date_from + 86400;
    $result = $mongo_db->aggregate_pipeline('LO_Report_input_payment_of_card', array(
        array(
            '$match' => array(
                'created_at' => array(
                    '$gte' => $payemnt_date_from,
                    '$lte' => $payemnt_date_to,
                ),
                '$or' => array(
                    array('code' => '2000'),
                    array('code' => '2100'),
                    array('code' => '2700'),
                ),
            )
        ),
        array(
            '$group' => array(
                "_id" => '$account_number',
                'totalAmount' => array('$push' => array('code' => '$code', 'amount' =>'$amount')),
                'count' => array('$sum' => 1),
            )
        ),
    ));

    $totalAmount = 0;
    foreach ($result as $key => $acc) {
        foreach ($acc['totalAmount'] as $key => $value) {
            if($value['code'] == '2000' || $value['code'] == '2100'){
                $totalAmount += $value['amount'];
            }else{
                $totalAmount -= $value['amount'];
            }
        }
    }

    return $totalAmount;
}