<?php

$target = getTargetReport('SIBS/Group A', $due_date["debt_group"]);

//Lay danh sach Group A
$SIBS_A = getGroup_byName('SIBS/Group A');
$group_name_A_data = processSIBS_A($SIBS_A);
$group_name_A = $group_name_A_data['group_name'];

//Lay so accounts duoc phan cho tung nhom trong group A
$accounts_each_group = processCountAccounts($group_name_A_data['lead_id'], date('Y-m-d', $due_date_plus1), $debt_group);
$target_accounts_each_group = processCountAccountsTarget($accounts_each_group, $target);

//Lay tong current_balance duoc phan cho tung nhom
$total_current_balance = processTotalBalance($group_name_A_data['lead_id'], date('Y-m-d', $due_date_plus1), $debt_group);
$target_total_current_balance = processTotalBalanceTarget($total_current_balance, $target);

//No. of Overdue accounts
processFireExcute_No_of_overdue_accounts($group_name_A_data['lead_id'], date('Y-m-d', $due_date_timstamp), $num_days_before_next_due, $due_date_timstamp, $debt_group, 'SIBS' );
// Overdue outstanding balance
processFireExcute_Overdue_outstanding_balance($group_name_A_data['lead_id'], date('Y-m-d', $due_date_timstamp), $num_days_before_next_due, $due_date_timstamp, $debt_group, 'SIBS' );

exec("ps ax | grep No_of_overdue_accounts_processes.php", $pids_No_of_overdue_accounts);
while (count($pids_No_of_overdue_accounts) > 2) {
    $pids_No_of_overdue_accounts = array();
    exec("ps ax | grep No_of_overdue_accounts_processes.php", $pids_No_of_overdue_accounts);
    echo 'No_of_overdue_accounts_processes chay chua xong' . PHP_EOL;
    sleep(1);
}
echo 'No_of_overdue_accounts_processes Chay xong' . PHP_EOL;

exec("ps ax | grep Overdue_outstanding_balance_processes.php", $pids_Overdue_outstanding_balance);
while (count($pids_Overdue_outstanding_balance) > 2) {
    $pids_Overdue_outstanding_balance = array();
    exec("ps ax | grep Overdue_outstanding_balance_processes.php", $pids_Overdue_outstanding_balance);
    echo 'Overdue_outstanding_balance_processes chay chua xong' . PHP_EOL;
    sleep(1);
}
echo 'Overdue_outstanding_balance_processes Chay xong' . PHP_EOL;

//Lay No. of Overdue accounts cho tung nhom
$No_of_overdue_accounts = processGet_No_of_overdue_accounts($group_name_A_data['lead_id'], date('Y-m-d', $due_date_timstamp));
//Lay Overdue_outstanding_balance cho tung nhom
$Overdue_outstanding_balance = processGet_Overdue_outstanding_balance($group_name_A_data['lead_id'], date('Y-m-d', $due_date_timstamp));

createFrameTemplate($param_duedate, $group_name_A, $accounts_each_group, $total_current_balance, $target_accounts_each_group, $target_total_current_balance, $num_days_before_next_due);

// Tinh total cac cot
$sodong_cansum = $total_nhomA = count($group_name_A);
total_start_target($sodong_cansum);

// Xu ly
pushNo_of_overdue_accounts($No_of_overdue_accounts);
totalNo_of_overdue_accounts($num_days_before_next_due, $sodong_cansum);

pushNo_of_Paid_accounts_end_of_day($total_nhomA, $num_days_before_next_due);
totalNo_of_Paid_accounts_end_of_day($num_days_before_next_due, $sodong_cansum);

pushNo_of_Paid_accounts_Accumulated($total_nhomA, $num_days_before_next_due);
totalNo_of_Paid_accounts_Accumulated($num_days_before_next_due, $sodong_cansum);

pushCollected_ratio($total_nhomA, $num_days_before_next_due);
totalCollected_ratio($num_days_before_next_due, $sodong_cansum);

pushOverdue_outstanding_balance($Overdue_outstanding_balance);
totalOverdue_outstanding_balance($num_days_before_next_due, $sodong_cansum);

pushOverdue_outstanding_balance_Collected_amount($total_nhomA, $num_days_before_next_due);
totalOverdue_outstanding_balance_Collected_amount($num_days_before_next_due, $sodong_cansum);

pushOverdue_outstanding_balance_Collected_amount_Accumulated($total_nhomA, $num_days_before_next_due);
totalOverdue_outstanding_balance_Collected_amount_Accumulated($num_days_before_next_due, $sodong_cansum);

pushOverdue_outstanding_balance_Collected_ratio($total_nhomA, $num_days_before_next_due);
totalOverdue_outstanding_balance_Collected_ratio($num_days_before_next_due, $sodong_cansum);

coloredTotalRow($total_nhomA, $num_days_before_next_due);


function processFireExcute_No_of_overdue_accounts($lead_id, $due_date, $num_days_before_next_due, $due_date_timstamp, $debt_group, $type_col)
{
    foreach ($lead_id as $key => $value) {
        $id = MANV . $value . '_' . $due_date;
        $id = base64_encode($id);
        $due_date_timstamp = (string) $due_date_timstamp;
        $url = "nohup php " . __DIR__ . "/mini_processes/No_of_overdue_accounts_processes.php $id $num_days_before_next_due $due_date_timstamp $debt_group $type_col  > /dev/null 2>&1 &";
        echo $url . PHP_EOL;
        exec($url);
    }
}

function processFireExcute_Overdue_outstanding_balance($lead_id, $due_date, $num_days_before_next_due, $due_date_timstamp, $debt_group, $type_col)
{
    foreach ($lead_id as $key => $value) {
        $id = MANV . $value . '_' . $due_date;
        $id = base64_encode($id);
        $due_date_timstamp = (string) $due_date_timstamp;
        $url = "nohup php " . __DIR__ . "/mini_processes/Overdue_outstanding_balance_processes.php $id $num_days_before_next_due $due_date_timstamp $debt_group $type_col  > /dev/null 2>&1 &";
        echo $url . PHP_EOL;
        exec($url);
    }
}