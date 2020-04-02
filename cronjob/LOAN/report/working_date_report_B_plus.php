<?php

$group_B_plus = ['B', 'C', 'D', 'E'];
$report_type[] = 'Payment_amount_received';
foreach ($group_B_plus as $key => $group) {
	$current_group = $group;
	$SIBS_G = 'SIBS_' . $current_group;
	$CARD_G = 'CARD_' . $current_group;
	$target_name_SIBS = 'SIBS/Group ' . $current_group;
	$target_name_CARD = 'CARD/Group ' . $current_group;

	insertNewHeader($param_duedate, $current_group . ' Group', $num_days_before_next_due);

	$target_SIBS = getTargetReport($target_name_SIBS, $debt_group);
	$target_CARD = getTargetReport($target_name_CARD, $debt_group);

//SIBS process
// starter_row_merge dung de color row total
	$starter_row_merge = $row;
//Lay danh sach Group
	$due_date_human = date('Y-m-d', $due_date_plus1);
	$group_name_B = $SIBS_G . $debt_group . '_' . $due_date_human;
	$group_name_B_2 = $SIBS_G . $debt_group . '_' . date('Y-m-d', $due_date_timstamp);;
//Lay so accounts duoc phan cho tung nhom trong group A
	$accounts_each_group = processCountAccounts_B_Plus($group_name_B, $due_date_plus1);
	$target_accounts_each_group = processCountAccountsTarget($accounts_each_group, $target_SIBS);

//Lay tong current_balance duoc phan cho tung nhom
	$total_current_balance = processTotalBalance_B_Plus($group_name_B, $due_date_plus1);
	$target_total_current_balance = processTotalBalanceTarget($total_current_balance, $target_SIBS);

//No. of Overdue accounts
processFireExcute_No_of_overdue_accounts_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, 'SIBS' );
// Overdue outstanding balance
processFireExcute_Overdue_outstanding_balance_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, 'SIBS' );
//Payment_amount_received
processFireExcute_Payment_amount_received_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, 'SIBS' );

	exec("ps ax | grep No_of_overdue_accounts_B_Plus_processes.php", $pids_No_of_overdue_accounts);
	while (count($pids_No_of_overdue_accounts) > 2) {
		$pids_No_of_overdue_accounts = array();
		exec("ps ax | grep No_of_overdue_accounts_B_Plus_processes.php", $pids_No_of_overdue_accounts);
		echo 'No_of_overdue_accounts_B_Plus_processes chay chua xong' . PHP_EOL;
		sleep(1);
	}
	echo 'No_of_overdue_accounts_B_Plus_processes Chay xong' . PHP_EOL;

	exec("ps ax | grep Overdue_outstanding_balance_processes_B_Plus.php", $pids_Overdue_outstanding_balance);
	while (count($pids_Overdue_outstanding_balance) > 2) {
		$pids_Overdue_outstanding_balance = array();
		exec("ps ax | grep Overdue_outstanding_balance_processes_B_Plus.php", $pids_Overdue_outstanding_balance);
		echo "Overdue_outstanding_balance_processes_B_Plus $group_name_B chay chua xong" . PHP_EOL;
		sleep(1);
	}
	echo "Overdue_outstanding_balance_processes_B_Plus $group_name_B Chay xong" . PHP_EOL;

	exec("ps ax | grep Payment_amount_received.php", $pids_Payment_amount_received);
	while (count($pids_Payment_amount_received) > 2) {
		$pids_Payment_amount_received = array();
		exec("ps ax | grep Payment_amount_received.php", $pids_Payment_amount_received);
		echo 'Payment_amount_received chay chua xong' . PHP_EOL;
		sleep(1);
	}
	echo 'Payment_amount_received Chay xong' . PHP_EOL;

//Lay No. of Overdue accounts cho tung nhom
	$No_of_overdue_accounts = processGet_No_of_overdue_accounts_B_Plus($group_name_B_2, date('Y-m-d', $due_date_timstamp));
//Lay Overdue_outstanding_balance cho tung nhom
	$Overdue_outstanding_balance = processGet_Overdue_outstanding_balance_B_Plus($group_name_B_2, date('Y-m-d', $due_date_timstamp));
//Lay Payment_amount_received cho tung nhom
	$Payment_amount_received = processGet_Payment_amount_received($group_name_B_2, date('Y-m-d', $due_date_timstamp));

	createFrame_B_Template($param_duedate, array($group_name_B_2), $accounts_each_group, $total_current_balance, $target_accounts_each_group, $target_total_current_balance, $num_days_before_next_due, 'SIBS');

// Tinh total cac cot can show
	$sodong_cansum = $total_nhomB = 1;
	total_start_target($sodong_cansum);

// Xu ly
	pushNo_of_overdue_accounts($No_of_overdue_accounts);
	totalNo_of_overdue_accounts($num_days_before_next_due, $sodong_cansum);

	pushNo_of_Paid_accounts_end_of_day($total_nhomB, $num_days_before_next_due);
	totalNo_of_Paid_accounts_end_of_day($num_days_before_next_due, $sodong_cansum);

	pushNo_of_Paid_accounts_Accumulated($total_nhomB, $num_days_before_next_due);
	totalNo_of_Paid_accounts_Accumulated($num_days_before_next_due, $sodong_cansum);

	pushCollected_ratio($total_nhomB, $num_days_before_next_due);
	totalCollected_ratio($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance($Overdue_outstanding_balance);
	totalOverdue_outstanding_balance($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance_Collected_amount($total_nhomB, $num_days_before_next_due);
	totalOverdue_outstanding_balance_Collected_amount($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance_Collected_amount_Accumulated($total_nhomB, $num_days_before_next_due);
	totalOverdue_outstanding_balance_Collected_amount_Accumulated($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance_Collected_ratio($total_nhomB, $num_days_before_next_due);
	totalOverdue_outstanding_balance_Collected_ratio($num_days_before_next_due, $sodong_cansum);

	pushPayment_amount_received($Payment_amount_received);
	totalPayment_amount_received($num_days_before_next_due, $sodong_cansum);

	coloredTotalRow($total_nhomB, $num_days_before_next_due);

//CARD process
// starter_row_merge dung de color row total
	$starter_row_merge = $row;
//Lay danh sach Group
	$due_date_human = date('Y-m-d', $due_date_plus1);
	$group_name_B = $CARD_G . $debt_group . '_' . $due_date_human;
	$group_name_B_2 = $CARD_G . $debt_group . '_' . date('Y-m-d', $due_date_timstamp);;
//Lay so accounts duoc phan cho tung nhom trong group A
	$accounts_each_group = processCountAccounts_B_Plus($group_name_B, $due_date_plus1);
	$target_accounts_each_group = processCountAccountsTarget($accounts_each_group, $target_CARD);

//Lay tong current_balance duoc phan cho tung nhom
	$total_current_balance = processTotalBalance_B_Plus($group_name_B, $due_date_plus1, 'CARD');
	$target_total_current_balance = processTotalBalanceTarget($total_current_balance, $target_CARD);

//No. of Overdue accounts
processFireExcute_No_of_overdue_accounts_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, 'CARD' );
// Overdue outstanding balance
processFireExcute_Overdue_outstanding_balance_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, 'CARD' );
//Payment_amount_received
processFireExcute_Payment_amount_received_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, 'CARD' );

	exec("ps ax | grep No_of_overdue_accounts_B_Plus_processes.php", $pids_No_of_overdue_accounts);
	while (count($pids_No_of_overdue_accounts) > 2) {
		$pids_No_of_overdue_accounts = array();
		exec("ps ax | grep No_of_overdue_accounts_B_Plus_processes.php", $pids_No_of_overdue_accounts);
		echo 'No_of_overdue_accounts_B_Plus_processes chay chua xong' . PHP_EOL;
		sleep(1);
	}
	echo 'No_of_overdue_accounts_B_Plus_processes Chay xong' . PHP_EOL;

	exec("ps ax | grep Overdue_outstanding_balance_processes_B_Plus.php", $pids_Overdue_outstanding_balance);
	while (count($pids_Overdue_outstanding_balance) > 2) {
		$pids_Overdue_outstanding_balance = array();
		exec("ps ax | grep Overdue_outstanding_balance_processes_B_Plus.php", $pids_Overdue_outstanding_balance);
		echo "Overdue_outstanding_balance_processes_B_Plus $group_name_B chay chua xong" . PHP_EOL;
		sleep(1);
	}
	echo 'Overdue_outstanding_balance_processes_B_Plus Chay xong' . PHP_EOL;

	exec("ps ax | grep Payment_amount_received.php", $pids_Payment_amount_received);
	while (count($pids_Payment_amount_received) > 2) {
		$pids_Payment_amount_received = array();
		exec("ps ax | grep Payment_amount_received.php", $pids_Payment_amount_received);
		echo 'Payment_amount_received chay chua xong' . PHP_EOL;
		sleep(1);
	}
	echo 'Payment_amount_received Chay xong' . PHP_EOL;

//Lay No. of Overdue accounts cho tung nhom
	$No_of_overdue_accounts = processGet_No_of_overdue_accounts_B_Plus($group_name_B_2, date('Y-m-d', $due_date_timstamp));
//Lay Overdue_outstanding_balance cho tung nhom
	$Overdue_outstanding_balance = processGet_Overdue_outstanding_balance_B_Plus($group_name_B_2, date('Y-m-d', $due_date_timstamp));
	//Lay Payment_amount_received cho tung nhom
	$Payment_amount_received = processGet_Payment_amount_received($group_name_B_2, date('Y-m-d', $due_date_timstamp));

	createFrame_B_Template($param_duedate, array($group_name_B_2), $accounts_each_group, $total_current_balance, $target_accounts_each_group, $target_total_current_balance, $num_days_before_next_due, 'CARD');

// Tinh total cac cot can show
	$sodong_cansum = $total_nhomB = 1;
	total_start_target($sodong_cansum);

// Xu ly
	pushNo_of_overdue_accounts($No_of_overdue_accounts);
	totalNo_of_overdue_accounts($num_days_before_next_due, $sodong_cansum);

	pushNo_of_Paid_accounts_end_of_day($total_nhomB, $num_days_before_next_due);
	totalNo_of_Paid_accounts_end_of_day($num_days_before_next_due, $sodong_cansum);

	pushNo_of_Paid_accounts_Accumulated($total_nhomB, $num_days_before_next_due);
	totalNo_of_Paid_accounts_Accumulated($num_days_before_next_due, $sodong_cansum);

	pushCollected_ratio($total_nhomB, $num_days_before_next_due);
	totalCollected_ratio($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance($Overdue_outstanding_balance);
	totalOverdue_outstanding_balance($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance_Collected_amount($total_nhomB, $num_days_before_next_due);
	totalOverdue_outstanding_balance_Collected_amount($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance_Collected_amount_Accumulated($total_nhomB, $num_days_before_next_due);
	totalOverdue_outstanding_balance_Collected_amount_Accumulated($num_days_before_next_due, $sodong_cansum);

	pushOverdue_outstanding_balance_Collected_ratio($total_nhomB, $num_days_before_next_due);
	totalOverdue_outstanding_balance_Collected_ratio($num_days_before_next_due, $sodong_cansum);

	pushPayment_amount_received($Payment_amount_received);
	totalPayment_amount_received($num_days_before_next_due, $sodong_cansum);

	coloredTotalRow($total_nhomB, $num_days_before_next_due);
}





function processFireExcute_No_of_overdue_accounts_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, $type_col)
{
	$due_date_plus1 = (string) $due_date_plus1;
	$url = "nohup php " . __DIR__ . "/mini_processes/No_of_overdue_accounts_B_Plus_processes.php $group_name_B $num_days_before_next_due $due_date_plus1 $type_col  > /dev/null 2>&1 &";
	echo $url . PHP_EOL;
	exec($url);
}

function processFireExcute_Overdue_outstanding_balance_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, $type_col)
{
	$due_date_plus1 = (string) $due_date_plus1;
	$url = "nohup php " . __DIR__ . "/mini_processes/Overdue_outstanding_balance_processes_B_Plus.php $group_name_B $num_days_before_next_due $due_date_plus1 $type_col  > /dev/null 2>&1 &";
	exec($url);
	echo $url . PHP_EOL;
}

function processFireExcute_Payment_amount_received_B_Plus($group_name_B, $num_days_before_next_due, $due_date_plus1, $type_col)
{
	$due_date_plus1 = (string) $due_date_plus1;
	$url = "nohup php " . __DIR__ . "/mini_processes/Payment_amount_received.php $group_name_B $num_days_before_next_due $due_date_plus1 $type_col  > /dev/null 2>&1 &";
	exec($url);
	echo $url . PHP_EOL;
}