<?php
define('MANV', 'SIBS_JIVF00');
define('CARD', 'CARD');

require_once 'model/working_date_report_model.php'; //function start with 'get'
require_once 'excel_template_creator/working_date_report_template.php'; //funtion start with 'push'
require_once 'functions/working_date_report_func.php'; //function start with 'process'

$due_date = getReportDueDate();
$due_date_timstamp = $due_date['due_date'];
$num_days_before_next_due = processCountNumDaysBeforeDue($due_date_timstamp, $due_date['debt_group']);
$num_days_before_next_due--;

$param_duedate = [];
$param_duedate['month'] = $due_date["for_month"];
$param_duedate['due'] = date('d', $due_date["due_date"]);
$param_duedate['product'] = 'SIBS';
$param_duedate['due_date'] = date('d-m-Y', $due_date["due_date"]);

$due_date_plus1 = $due_date["due_date"] + 86400;
$debt_group = $due_date["debt_group"];

require_once 'working_date_report_SIBS_A.php';
mergeRow();
$starter_row_merge = $row;
require_once 'working_date_report_Card_A.php';
coloredTotalRow($total_nhomA, $num_days_before_next_due);
mergeRow();
setStyleColumns();
require_once 'working_date_report_B_plus.php';

writeExcel(date('d-m-Y', $due_date_timstamp));