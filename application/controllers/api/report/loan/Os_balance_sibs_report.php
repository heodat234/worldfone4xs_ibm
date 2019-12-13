<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

Class Os_balance_sibs_report extends WFF_Controller {

    private $collection = "Os_balance_group_sibs";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
		$this->load->library("mongo_db");
		$this->load->library("excel");
		$this->load->library("mongo_private");
        $this->collection = set_sub_collection($this->collection);
        
    }

    function index()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function saveReport()
    {
      shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/worldfone4xs_ibm/cronjob/python/Loan/saveMasterData.py  > /dev/null &');
    }

    function exportExcel()
    {
		$monthInYear = array();
		$dayData = array();
		$year = date("Y");
        // $starttime = strtotime('today 00:00:00');
        // $endtime = strtotime('today 23:59:59');
        $starttime = strtotime($year . '-01-01' . ' 00:00:00');
		$endtime = strtotime('today 23:59:59');
		
		// $reportData = array();
		// $reportData = $this->crud->where(array('created_at' => array('$gte' => $starttime, '$lte' => $endtime)))->get($this->collection);
		// foreach($reportDataRaw as $dataRow) {
		// 	$reportData[(string)$dataRow['created_at']] = $dataRow;
		// }
		
		// print_r($reportData);

		$spreadsheet = new Spreadsheet();
    	$spreadsheet->getProperties()
	    ->setCreator("South Telecom")
	    ->setLastModifiedBy("Tri Dung")
	    ->setTitle("Report")
	    ->setSubject("Report")
	    ->setDescription("Office 2007 XLSX, generated using PHP classes.")
	    ->setKeywords("office 2007 openxml php")
	    ->setCategory("Report");

	    $worksheet = $spreadsheet->getActiveSheet();
		
		$count = 3;
		$listMonthOfYearJ = $this->mongo_private->where(array('tags' => array('month', 'ofyear', 'name')))->getOne(set_sub_collection('Jsondata'));
		$monthOfYearInfo = array_column($listMonthOfYearJ['data'], 'text', 'value');

		$debtGroupRaw = $this->mongo_private->where(array('tags' => array('debt', 'group')))->getOne(set_sub_collection('Jsondata'));
		$debtGroup = array_column($debtGroupRaw['data'], 'text');
		asort($debtGroup);

		$debtDueMonthRaw = $this->mongo_private->where(array('tags' => array('debt', 'duedate')))->getOne(set_sub_collection('Jsondata'));
		$debtDueMonth = array_column($debtDueMonthRaw['data'], 'text');

		$debtGroupByDueDate = array();

		foreach($debtGroup as $dGroup) {
			foreach($debtDueMonth as $dueDate) {
				array_push($debtGroupByDueDate, $dGroup . $dueDate);
			}
		}

		$listHeader = array();
		foreach($monthOfYearInfo as $key => $value) {
			// print_r(cal_days_in_month(CAL_GREGORIAN, (int)$key, $year));
			$totalDayInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$key, $year);
			for ($i = 1; $i <= $totalDayInMonth; $i++) {
				$columnName = $this->excel->stringFromColumnIndex($count);
				$columnNameLastMonth = $this->excel->stringFromColumnIndex($count + ($totalDayInMonth - 1));
				if($i == 1) {
					$worksheet->setCellValue($columnName . '1', $value);
					$worksheet->mergeCells($columnName . '1:' . $columnNameLastMonth . '1');
				}
				$worksheet->setCellValue($columnName . '2', $i);
				$dateTimeStamp = strtotime($year . '-' . $key . '-' . $i . ' 00:00:00');
				$listHeader[(string)$dateTimeStamp] = $columnName;
				$count++;
			}
		}

		$countGroup = 0;
		$positionRowOfGroupName = 3;
		foreach($debtGroupByDueDate as $group) {
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName), $group);

			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 2), 'START');
			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 2) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 3));

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 2), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 3), 'No.');

			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 4), 'TARGET OF COLLECTION');
			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 4) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 5));

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 4), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 5), 'No.');
			
			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 6) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 7));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 6), 'DAILY');

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 6), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 7), 'No.');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 8) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 9));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 8), 'RESULT END OF DAY');

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 8), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 9), 'No.');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 10) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 11));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 10), 'ACCUMULATED');

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 10), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 11), 'No.');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 12) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 13));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 12), 'RATIO (vs target)');

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 12), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 13), 'No.');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 14) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 15));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 14), 'RATIO (vs start)');

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 14), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 15), 'No.');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 16) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 17));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 16), 'FINAL No');

			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 16), 'OS BL');
			$worksheet->setCellValue('B' . ($countGroup * 17 + $positionRowOfGroupName + 17), 'No.');

			foreach($listHeader as $headerKey => $headerValue) {
				$dataReport = $this->crud->where(array('created_at' => $headerKey, 'debt_group_name' => $group))->getOne($this->collection);
				
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 2), (!empty($dataReport['start_os_bl'])) ? number_format($dataReport['start_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 3), (!empty($dataReport['start_no'])) ? number_format($dataReport['start_no'], 0, '', ',') : '');
				// print_r($reportData);
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 4), (!empty($dataReport['target_os_bl'])) ? number_format($dataReport['target_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 5), (!empty($dataReport['target_no'])) ? number_format($dataReport['target_no'], 0, '', ',') : '');
			
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 6), (!empty($dataReport['daily_os_bl'])) ? number_format($dataReport['daily_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 7), (!empty($dataReport['daily_no'])) ? number_format($dataReport['daily_no'], 0, '', ',') : '');
			
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 8), (!empty($dataReport['end_date_os_bl'])) ? number_format($dataReport['end_date_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 9), (!empty($dataReport['end_date_no'])) ? number_format($dataReport['end_date_no'], 0, '', ',') : '');
			
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 10), (!empty($dataReport['accumulated_os_bl'])) ? number_format($dataReport['accumulated_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 11), (!empty($dataReport['accumulated_no'])) ? number_format($dataReport['accumulated_no'], 0, '', ',') : '');
			
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 12), (!empty($dataReport['ratio_target_os_bl'])) ? number_format($dataReport['ratio_target_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 13), (!empty($dataReport['ratio_target_no'])) ? number_format($dataReport['ratio_target_no'], 0, '', ',') : '');
			
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 14), (!empty($dataReport['ratio_start_os_bl'])) ? number_format($dataReport['ratio_start_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 15), (!empty($dataReport['ratio_start_no'])) ? number_format($dataReport['ratio_start_no'], 0, '', ',') : '');
			
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 16), (!empty($dataReport['final_os_bl'])) ? number_format($dataReport['final_os_bl'], 0, '', ',') : '');
				$worksheet->setCellValue($headerValue . ($countGroup * 17 + $positionRowOfGroupName + 17), (!empty($dataReport['final_no'])) ? number_format($dataReport['final_no'], 0, '', ',') : '');
			}

			$countGroup++;
			$positionRowOfGroupName++;
		}

		// $style = array(
		// 	'alignment' => array(
		// 		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		// 	)
		// );
	
		// $worksheet->getDefaultStyle()->applyFromArray($style);
		
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "loan/export/" . 'export.xlsx';
		$writer->save($file_path);
		echo $file_path;
    }
    function downloadExcel()
    {
        $file_path = UPLOAD_PATH . "excel/MasterData.xlsx";
        echo json_encode(array("status" => 1, "data" => $file_path));
    }

    function excel()
	{
		try {
			$folder = "excel";
			$collection = set_sub_collection("File");
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . UPLOAD_PATH . "loan/export";
			if (!@file_exists($path)) { 
				@mkdir(UPLOAD_PATH . $folder, 0755);
			}
			$file = $_FILES['file'];
			$file_parts = @pathinfo($file['name']);
			$notallowed_types = 'php|sh|bash';
			$filesize = @filesize($file['tmp_name']);
			if(strpos($notallowed_types, strtolower($file_parts['extension'])) !== FALSE) throw new Exception("Wrong file type.");
			// Check size  > 30MB
			if($filesize > 30000000) throw new Exception("File too large. Over 30MB.");
			$new_file_name = str_replace([" ","/"], ["",""], $file['name']);
			$file_path = $path . $new_file_name;

			// Check exists
			if(@file_exists($file_path)) {
			    @unlink($file_path); //remove the file
			}

			if (@is_uploaded_file($file['tmp_name'])) {
				@move_uploaded_file($file['tmp_name'] , $file_path);
			}
			$data = array(
				'name'		=> $file['name'],
				'type'		=> $folder,
				'uploadname'=> $file['name'],
				'filename' 	=> $new_file_name,
				'filepath'	=> UPLOAD_PATH . $folder . "/" . $new_file_name,
				'size' 		=> $filesize,
				'extension' => $file_parts['extension'],
				'createdBy'	=> $this->session->userdata("extension"),
				'createdAt' => time(),
				'time'		=> (new DateTime())->format('Y-m-d H:i:s')	
			);
			$this->load->library("mongo_db");
			$this->mongo_db->insert($collection, $data);
			echo json_encode(array("status" => 1, "message" => "Upload successfully", 
				"filepath" => $data["filepath"], "filename" => $file['name'], "size" => $filesize
			));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}
}