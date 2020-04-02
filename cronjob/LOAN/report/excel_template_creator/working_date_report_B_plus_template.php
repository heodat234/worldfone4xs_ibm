<?php
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

function insertNewHeader($param_duedate, $SIBS_G, $num_days_before_next_due){
    global $sheet;
    global $row;

    $row++;
    $row++;
    $col = 'A';
    $sheet->setCellValue('A' . $row, $SIBS_G);
    $sheet->setCellValue('F' . $row, 'Start');
    $sheet->mergeCells("F$row:G$row");
    $sheet->setCellValue('H' . $row, 'Target');
    $sheet->mergeCells("H$row:I$row");
    $row++;
    //cong them duedate va duedate+1 cho ky due toi de tinh final
    $num_days_before_next_due += 2;
    $header = ['Month', 'Due', 'Product', 'Due date', 'GROUP', 'Accounts', 'Amount', 'Accounts', 'Amount', 'Day'];
    for ($i = 1; $i <= $num_days_before_next_due; $i++) {
        $header[] = (string) $i;
    }

    foreach ($header as $value) {
        $sheet->getStyle($col . $row)->getFont()->setBold(true);
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
        $col++;
    }
    $row++;
}

function createFrame_B_Template($param_duedate, $group_name_B_plus, $accounts_each_group, $total_current_balance, $target_accounts_each_group, $target_total_current_balance, $num_days_before_next_due, $typeDebt)
{
    global $sheet;
    global $col;
    global $row;
    global $report_type;

    //them thong tin due_date
    $col = 'A';
    $param_duedate['product'] = $typeDebt;
    foreach ($param_duedate as $key => $value) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($col . $row)->getAlignment()->setVertical('center');
        $col++;
    }
    //Them nhom vao cot Group
    $group_row = $row;
    for ($i = 0; $i < count($report_type); $i++) {
        foreach ($group_name_B_plus as $key => $value) {
            $sheet->setCellValue($col . $group_row, $value);
            $group_row++;
        }
        $sheet->setCellValue($col . $group_row, 'Total');
        $group_row++;
    }

    // Them so accounts duoc phan chia theo tung nhom
    $col++;
    $new_row = $row;
    foreach ($accounts_each_group as $key => $value) {
        $sheet->setCellValue($col . $new_row, $value);
        $new_row++;
    }
    //Them tong current balance tung nhom
    $col++;
    $new_row = $row;
    foreach ($total_current_balance as $key => $value) {
        $sheet->setCellValue($col . $new_row, $value);
        $new_row++;
    }
    //Them target accounts tung nhom
    $col++;
    $new_row = $row;
    foreach ($target_accounts_each_group as $key => $value) {
        $sheet->setCellValue($col . $new_row, $value);
        $new_row++;
    }
    //Them target current_balance tung nhom
    $col++;
    $new_row = $row;
    foreach ($target_total_current_balance as $key => $value) {
        $sheet->setCellValue($col . $new_row, $value);
        $new_row++;
    }

    $col++;
    $row_merge_from = $row;
    $row_merged_to = $new_row;

    foreach ($report_type as $key => $value) {
        $sheet->setCellValue($col . $row_merge_from, $value);
        $sheet->getStyle($col . $row_merge_from)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($col . $row_merge_from)->getAlignment()->setVertical('center');
        $sheet->mergeCells($col . $row_merge_from . ':' . $col . $row_merged_to);
        $row_merge_from += count($group_name_B_plus) + 1;
        $row_merged_to += count($group_name_B_plus) + 1;
    }

}

function pushPayment_amount_received($Payment_amount_received)
{
    global $sheet;
    global $col;
    global $row;

    foreach ($Payment_amount_received as $key => $payment_amount_received) {
        $temp_col = $col;
        foreach ($payment_amount_received as $key => $value) {
            $sheet->setCellValue($temp_col . $row, $value);
            $temp_col++;
        }
        $row++;
    }
}

function totalPayment_amount_received($num_days_before_next_due, $sodong_cansum)
{
    global $sheet;
    global $row;
    $num_days_before_next_due += 2;
    $this_col = 'K';
    $this_row = $row - $sodong_cansum;
    $to_row = $row - 1;
    for ($i = 1; $i <= $num_days_before_next_due; $i++) {
        $SUMRANGE = $this_col . $this_row . ':' . $this_col . $to_row;
        $columnIndex = Coordinate::columnIndexFromString($this_col);
        $sheet->getStyleByColumnAndRow($columnIndex, $row)
            ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

        $sheet->setCellValue($this_col . $row, "=SUM($SUMRANGE)");
        $this_col++;
    }
    $row++;
}

?>