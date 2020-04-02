<?php
require_once 'init.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$col = 'A';
$row = 1;
$starter_row_merge = 3;
$report_type = [
    'No. of Overdue accounts',
    'No. of Paid accounts end of day',
    'No. of Paid accounts Accumulated',
    'Collected ratio (account)',
    'Overdue outstanding balance',
    'Collected amount (end of day)',
    'Collected amount Accumulated',
    'Collected ratio (amount)',
];

function createFrameTemplate($param_duedate, $group_name_A, $accounts_each_group, $total_current_balance, $target_accounts_each_group, $target_total_current_balance, $num_days_before_next_due)
{
    global $sheet;
    global $col;
    global $row;
    global $report_type;

    $row = 1;

//header
    $col = 'A';
    $sheet->setCellValue('A' . $row, 'A GROUP');
    $sheet->setCellValue('F' . $row, 'Start');
    $sheet->mergeCells('F1:G1');
    $sheet->setCellValue('H' . $row, 'Target');
    $sheet->mergeCells('H1:I1');
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
//end header

//body
    //thong tin due_date
    $col = 'A';
    foreach ($param_duedate as $key => $value) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($col . $row)->getAlignment()->setVertical('center');
        $col++;
    }
    //Them nhom vao cot Group

    $group_row = $row;
    for ($i = 0; $i < count($report_type); $i++) {
        foreach ($group_name_A as $key => $value) {
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
        $columnIndex = Coordinate::columnIndexFromString($col);
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
    //Them target accounts tung nhom
    $col++;
    $new_row = $row;
    foreach ($target_total_current_balance as $key => $value) {
        $sheet->setCellValue($col . $new_row, $value);
        $new_row++;
    }

    //No. of Overdue accounts
    $col++;
    $row_merge_from = $row;
    $row_merged_to = $new_row;

    foreach ($report_type as $key => $value) {
        $sheet->setCellValue($col . $row_merge_from, $value);
        $sheet->getStyle($col . $row_merge_from)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($col . $row_merge_from)->getAlignment()->setVertical('center');
        $sheet->mergeCells($col . $row_merge_from . ':' . $col . $row_merged_to);
        $row_merge_from += count($group_name_A) + 1;
        $row_merged_to += count($group_name_A) + 1;
    }

//end body

}

function createFrameCard_A_Template($param_duedate, $group_name_A, $accounts_each_group, $total_current_balance, $target_accounts_each_group, $target_total_current_balance, $num_days_before_next_due)
{
    global $sheet;
    global $col;
    global $row;
    global $report_type;

//body
    //them thong tin due_date
    $col = 'A';
    $param_duedate['product'] = 'CARD';
    foreach ($param_duedate as $key => $value) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($col . $row)->getAlignment()->setVertical('center');
        $col++;
    }
    //Them nhom vao cot Group
    $group_row = $row;
    for ($i = 0; $i < count($report_type); $i++) {
        foreach ($group_name_A as $key => $value) {
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

    //No. of Overdue accounts
    $col++;
    $row_merge_from = $row;
    $row_merged_to = $new_row;
    foreach ($report_type as $key => $value) {
        $sheet->setCellValue($col . $row_merge_from, $value);
        $sheet->getStyle($col . $row_merge_from)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($col . $row_merge_from)->getAlignment()->setVertical('center');
        $sheet->mergeCells($col . $row_merge_from . ':' . $col . $row_merged_to);
        $row_merge_from += count($group_name_A) + 1;
        $row_merged_to += count($group_name_A) + 1;
    }

//end body

}

function total_start_target($sodong_cansum)
{
    global $sheet;
    global $row;

    $this_col = ['F', 'G', 'H', 'I'];
    foreach ($this_col as $coll) {
        $this_row = $row;
        $to_row = $this_row + $sodong_cansum - 1;
        $SUMRANGE = $coll . $this_row . ':' . $coll . $to_row;
        $total_row = $to_row + 1;
        $sheet->setCellValue($coll . $total_row, "=SUM($SUMRANGE)");
        $this_row++;
    }
}

function pushNo_of_overdue_accounts($No_of_overdue_accounts)
{
    global $sheet;
    global $col;
    global $row;

    $col++;
    foreach ($No_of_overdue_accounts as $key => $no_of_overdue_accounts) {
        $temp_col = $col;
        foreach ($no_of_overdue_accounts as $key => $value) {
            $sheet->setCellValue($temp_col . $row, $value);
            $temp_col++;
        }
        $row++;
    }
}

function totalNo_of_overdue_accounts($num_days_before_next_due, $sodong_cansum)
{
    global $sheet;
    global $row;

    $num_days_before_next_due += 2;
    $this_col = 'K';
    $this_row = $row - $sodong_cansum;

    for ($i = 1; $i <= $num_days_before_next_due; $i++) {
        $to_row = $this_row + $sodong_cansum - 1;
        $SUMRANGE = $this_col . $this_row . ':' . $this_col . $to_row;
        $total_row = $row;
        $sheet->setCellValue($this_col . $total_row, "=SUM($SUMRANGE)");
        $this_col++;
    }
    $row++;

}

function pushNo_of_Paid_accounts_end_of_day($total_nhomA, $num_days_before_next_due)
{
    global $row;
    global $sheet;

    for ($i = 0; $i < $total_nhomA; $i++) {
        $this_col = 'K';
        $temp_row = $row - ($total_nhomA + 1);
        for ($day = 0; $day < $num_days_before_next_due + 2; $day++) {
            $val1 = $sheet->getCell($this_col . $temp_row)->getValue();
            $next_col = $this_col;
            $next_col++;
            $val2 = $sheet->getCell($next_col . $temp_row)->getValue();
            $sheet->setCellValue($this_col . $row, $val1 - $val2);
            $this_col++;
        }
        $row++;
    }

}

function totalNo_of_Paid_accounts_end_of_day($num_days_before_next_due, $sodong_cansum)
{
    global $sheet;
    global $row;
    $num_days_before_next_due += 2;
    $this_col = 'K';
    $this_row = $row - $sodong_cansum;
    $to_row = $row - 1;
    for ($i = 1; $i <= $num_days_before_next_due; $i++) {
        $SUMRANGE = $this_col . $this_row . ':' . $this_col . $to_row;
        $sheet->setCellValue($this_col . $row, "=SUM($SUMRANGE)");
        $this_col++;
    }
    $row++;
}

function pushNo_of_Paid_accounts_Accumulated($total_nhomA, $num_days_before_next_due)
{
    global $row;
    global $sheet;
    $temp_row = $row - $total_nhomA * 2 - 2;

    for ($i = 0; $i < $total_nhomA; $i++) {
        $this_col = 'K';
        $hold_data = $sheet->getCell($this_col . $temp_row)->getValue();
        for ($day = 0; $day < $num_days_before_next_due + 2; $day++) {
            $temp_col = $this_col;
            $temp_col++;
            $next_date_value = $sheet->getCell($temp_col . $temp_row)->getValue();
            $sheet->setCellValue($this_col . $row, $hold_data - $next_date_value);
            $this_col++;
        }
        $temp_row++;
        $row++;
    }

}

function totalNo_of_Paid_accounts_Accumulated($num_days_before_next_due, $sodong_cansum)
{
    global $sheet;
    global $row;
    $num_days_before_next_due += 2;
    $this_col = 'K';
    $this_row = $row - $sodong_cansum;
    $to_row = $row - 1;
    for ($i = 1; $i <= $num_days_before_next_due; $i++) {
        $SUMRANGE = $this_col . $this_row . ':' . $this_col . $to_row;
        $sheet->setCellValue($this_col . $row, "=SUM($SUMRANGE)");
        $this_col++;
    }
    $row++;
}

function pushCollected_ratio($total_nhomA, $num_days_before_next_due)
{
    global $row;
    global $sheet;
    $temp_row = $row - $total_nhomA * 3 - 3;
    for ($i = 0; $i < $total_nhomA; $i++) {
        $this_col = 'K';
        $hold_data = $sheet->getCell($this_col . $temp_row)->getValue();

        for ($day = 0; $day < $num_days_before_next_due + 2; $day++) {
            $No_of_Paid_accounts_Accumulated_row = $row - $total_nhomA - 1;
            $No_of_Paid_accounts_Accumulated_value = $sheet->getCell($this_col . $No_of_Paid_accounts_Accumulated_row)->getValue();
            $dive = ($hold_data != 0) ? round($No_of_Paid_accounts_Accumulated_value / $hold_data, 4) : 0;
            $columnIndex = Coordinate::columnIndexFromString($this_col);
            $sheet->getStyleByColumnAndRow($columnIndex, $row)
                ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->setCellValue($this_col . $row, $dive);

            $this_col++;
        }
        $temp_row++;
        $row++;
    }

}

function totalCollected_ratio($num_days_before_next_due, $sodong_cansum)
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
            ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

        $sheet->setCellValue($this_col . $row, "=SUM($SUMRANGE)");
        $this_col++;
    }
    $row++;
}

function pushOverdue_outstanding_balance($Overdue_outstanding_balance)
{
    global $sheet;
    global $col;
    global $row;

    foreach ($Overdue_outstanding_balance as $key => $overdue_outstanding_balance) {
        $temp_col = $col;
        foreach ($overdue_outstanding_balance as $key => $value) {
            $sheet->setCellValue($temp_col . $row, $value);
            $temp_col++;
        }
        $row++;
    }
}

function totalOverdue_outstanding_balance($num_days_before_next_due, $sodong_cansum)
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

function pushOverdue_outstanding_balance_Collected_amount($total_nhomA, $num_days_before_next_due)
{
    global $row;
    global $sheet;
    for ($i = 0; $i < $total_nhomA; $i++) {
        $this_col = 'K';
        $temp_row = $row - ($total_nhomA + 1);
        for ($day = 0; $day < $num_days_before_next_due + 2; $day++) {
            $val1 = $sheet->getCell($this_col . $temp_row)->getValue();
            $next_col = $this_col;
            $next_col++;
            $val2 = $sheet->getCell($next_col . $temp_row)->getValue();
            $sheet->setCellValue($this_col . $row, $val1 - $val2);
            $this_col++;
        }
        $row++;
    }

}

function totalOverdue_outstanding_balance_Collected_amount($num_days_before_next_due, $sodong_cansum)
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

function pushOverdue_outstanding_balance_Collected_amount_Accumulated($total_nhomA, $num_days_before_next_due)
{
    global $row;
    global $sheet;
    $temp_row = $row - 2 * $total_nhomA - 2; // lay row tai vi tri Overdue_outstanding_balance

    for ($i = 0; $i < $total_nhomA; $i++) {
        $this_col = 'K';
        $hold_data = $sheet->getCell($this_col . $temp_row)->getValue();
        for ($day = 0; $day < $num_days_before_next_due + 2; $day++) {
            $temp_col = $this_col;
            $temp_col++;
            $next_date_value = $sheet->getCell($temp_col . $temp_row)->getValue();
            $sheet->setCellValue($this_col . $row, $hold_data - $next_date_value);
            $this_col++;
        }
        $temp_row++;
        $row++;
    }

}

function totalOverdue_outstanding_balance_Collected_amount_Accumulated($num_days_before_next_due, $sodong_cansum)
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

function pushOverdue_outstanding_balance_Collected_ratio($total_nhomA, $num_days_before_next_due)
{
    global $row;
    global $sheet;
    $temp_row = $row - 3 * $total_nhomA - 3;

    for ($i = 0; $i < $total_nhomA; $i++) {
        $this_col = 'K';
        $hold_data = $sheet->getCell($this_col . $temp_row)->getValue();
        for ($day = 0; $day < $num_days_before_next_due + 2; $day++) {
            $Overdue_outstanding_balance_Accumulated_row = $row - $total_nhomA - 1;
            $Overdue_outstanding_balance_Accumulated_value = $sheet->getCell($this_col . $Overdue_outstanding_balance_Accumulated_row)->getValue();
            $dive = ($hold_data != 0) ? round($Overdue_outstanding_balance_Accumulated_value / $hold_data, 4) : 0;

            $columnIndex = Coordinate::columnIndexFromString($this_col);
            $sheet->getStyleByColumnAndRow($columnIndex, $row)
                ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->setCellValue($this_col . $row, $dive);

            $this_col++;
        }
        $temp_row++;
        $row++;
    }

}

function totalOverdue_outstanding_balance_Collected_ratio($num_days_before_next_due, $sodong_cansum)
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
            ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

        $sheet->setCellValue($this_col . $row, "=SUM($SUMRANGE)");
        $this_col++;
    }
    $row++;
}

function setStyleColumns()
{
    global $sheet;
    global $row;

    $temp_row = $row - 1;
    // $sheet->getStyle("F1:F$temp_row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000');
    // $sheet->getStyle("G1:G$temp_row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000');
    // $sheet->getStyle("H1:H$temp_row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000');
    // $sheet->getStyle("I1:I$temp_row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000');

    $sheet->getStyle("F1:F$temp_row")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("F1:F$temp_row")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("G1:G$temp_row")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("G1:G$temp_row")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("H1:H$temp_row")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("H1:H$temp_row")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("I1:I$temp_row")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("I1:I$temp_row")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

    $sheet->getColumnDimension('E')->setAutoSize(true);
    $sheet->getColumnDimension('J')->setAutoSize(true);
}

function coloredTotalRow($total_nhomA, $num_days_before_next_due)
{
    global $row;
    global $sheet;
    global $starter_row_merge;

    $from_col = 'E';
    $to_col = 'K';
    for ($j = 0; $j < $num_days_before_next_due + 1; $j++) {
        $to_col++;
    }

    $this_row = $starter_row_merge + $total_nhomA;
    while ($this_row < $row + 1) {
        $sheet->getStyle($from_col . $this_row . ":" . $to_col . $this_row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8CBAD');
        $this_row += ($total_nhomA + 1);
    }
}

function mergeRow()
{
    global $sheet;
    global $row;
    global $starter_row_merge;

    $to_row = $row - 1;
    $cols = ['A', 'B', 'C', 'D'];
    foreach ($cols as $key => $col) {
        $sheet->mergeCells($col . $starter_row_merge . ':' . $col . $to_row);
    }
}

function writeExcel($due_date)
{
    global $spreadsheet;
    global $export_path;

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    $writer->save($export_path . $due_date . ".xlsx");
}

require_once 'working_date_report_B_plus_template.php';