<?php

$target = getTargetReport('CARD/Group A', $due_date["debt_group"]);

//Lay danh sach Group A
$Card_A = getGroup_byName('Card/Group A');
$group_name_Card_A_data = processCard_A($Card_A);
$group_name_A = $group_name_Card_A_data['group_name'];

//Lay so accounts duoc phan cho tung nhom trong group A
$accounts_each_group = processCountAccounts_CardA($group_name_Card_A_data['lead_id'], date('Y-m-d', $due_date_plus1), $debt_group);
$target_accounts_each_group = processCountAccountsTarget($accounts_each_group, $target);

//Lay tong current_balance duoc phan cho tung nhom
$total_current_balance = processTotalBalanceCard_A($group_name_Card_A_data['lead_id'], date('Y-m-d', $due_date_plus1), $debt_group);
$target_total_current_balance = processTotalBalanceTarget($total_current_balance, $target);


//No. of Overdue accounts
processFireExcute_No_of_overdue_accounts_Card_A($group_name_Card_A_data['lead_id'], date('Y-m-d', $due_date_timstamp), $num_days_before_next_due, $due_date_timstamp, $debt_group);
// Overdue outstanding balance
processFireExcute_Overdue_outstanding_balance_Card_A($group_name_Card_A_data['lead_id'], date('Y-m-d', $due_date_timstamp), $num_days_before_next_due, $due_date_timstamp, $debt_group);

exec("ps ax | grep No_of_overdue_accounts_processes.php", $pids_No_of_overdue_accounts_card);
while(count($pids_No_of_overdue_accounts_card) > 2){
    $pids_No_of_overdue_accounts_card = array();
    exec("ps ax | grep No_of_overdue_accounts_processes.php", $pids_No_of_overdue_accounts_card);
    echo 'No_of_overdue_accounts_processes chay chua xong' . PHP_EOL;
    sleep(1);
}
echo 'No_of_overdue_accounts_processes Chay xong' . PHP_EOL;

exec("ps ax | grep Overdue_outstanding_balance_processes.php", $pids_Overdue_outstanding_balance_card);
while(count($pids_Overdue_outstanding_balance_card) > 2){
    $pids_Overdue_outstanding_balance_card = array();
    exec("ps ax | grep Overdue_outstanding_balance_processes.php", $pids_Overdue_outstanding_balance_card);
    echo 'Overdue_outstanding_balance_processes chay chua xong' . PHP_EOL;
    sleep(1);
}
echo 'Overdue_outstanding_balance_processes Chay xong' . PHP_EOL;

// Lay No. of Overdue accounts cho tung nhom
$No_of_overdue_accounts = processGet_No_of_overdue_accounts_Card_A($group_name_Card_A_data['lead_id'], date('Y-m-d', $due_date_timstamp));
// Lay Overdue_outstanding_balance cho tung nhom
$Overdue_outstanding_balance = processGet_Overdue_outstanding_balance_Card_A($group_name_Card_A_data['lead_id'], date('Y-m-d', $due_date_timstamp));

createFrameCard_A_Template($param_duedate, $group_name_A, $accounts_each_group, $total_current_balance, $target_accounts_each_group, $target_total_current_balance, $num_days_before_next_due);

$sodong_cansum = $total_nhomA = count($group_name_A);
total_start_target($sodong_cansum);

// Xu ly
//No_of_overdue_accounts
pushNo_of_overdue_accounts($No_of_overdue_accounts);
totalNo_of_overdue_accounts($num_days_before_next_due, $sodong_cansum);

pushNo_of_Paid_accounts_end_of_day($total_nhomA, $num_days_before_next_due);
totalNo_of_Paid_accounts_end_of_day($num_days_before_next_due, $sodong_cansum);

pushNo_of_Paid_accounts_Accumulated($total_nhomA, $num_days_before_next_due);
totalNo_of_Paid_accounts_Accumulated($num_days_before_next_due, $sodong_cansum);

pushCollected_ratio($total_nhomA, $num_days_before_next_due);
totalCollected_ratio($num_days_before_next_due, $sodong_cansum);
// Overdue_outstanding_balance
pushOverdue_outstanding_balance($Overdue_outstanding_balance);
totalOverdue_outstanding_balance($num_days_before_next_due, $sodong_cansum);

pushOverdue_outstanding_balance_Collected_amount($total_nhomA, $num_days_before_next_due);
totalOverdue_outstanding_balance_Collected_amount($num_days_before_next_due, $sodong_cansum);

pushOverdue_outstanding_balance_Collected_amount_Accumulated($total_nhomA, $num_days_before_next_due);
totalOverdue_outstanding_balance_Collected_amount_Accumulated($num_days_before_next_due, $sodong_cansum);

pushOverdue_outstanding_balance_Collected_ratio($total_nhomA, $num_days_before_next_due);
totalOverdue_outstanding_balance_Collected_ratio($num_days_before_next_due, $sodong_cansum);


function processFireExcute_No_of_overdue_accounts_Card_A($lead_id, $due_date, $num_days_before_next_due, $due_date_timstamp, $debt_group)
{
    foreach ($lead_id as $key => $value) {
        $id = CARD .'_' . $value . '_' . $due_date;
        $id = base64_encode($id);
        $due_date_timstamp = (string) $due_date_timstamp;
        $type_collection = 'CARD';
        $url = "nohup php " . __DIR__ . "/mini_processes/No_of_overdue_accounts_processes.php $id $num_days_before_next_due $due_date_timstamp $debt_group $type_collection > /dev/null 2>&1 &";
        echo $url . PHP_EOL;
        exec($url);
    }
}

function processFireExcute_Overdue_outstanding_balance_Card_A($lead_id, $due_date, $num_days_before_next_due, $due_date_timstamp, $debt_group)
{
    foreach ($lead_id as $key => $value) {
        $id = CARD . '_' . $value . '_' . $due_date;
        $id = base64_encode($id);
        $type_collection = 'CARD';
        $due_date_timstamp = (string) $due_date_timstamp;
        $url = "nohup php " . __DIR__ . "/mini_processes/Overdue_outstanding_balance_processes.php $id $num_days_before_next_due $due_date_timstamp $debt_group $type_collection > /dev/null 2>&1 &";
        echo $url . PHP_EOL;
        exec($url);
    }
}