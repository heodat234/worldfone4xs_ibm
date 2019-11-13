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
		$this->mongo_db->switch_db('_worldfone4xs');
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
        // $starttime = strtotime('today 00:00:00');
        // $endtime = strtotime('today 23:59:59');
        $starttime = strtotime('14-10-2019 00:00:00');
        $endtime = strtotime('14-10-2019 23:59:59');
		$reportData = $this->crud->where(array('created_at' => array('$gte' => $starttime, '$lte' => $endtime)))->get($this->collection);

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
        // $objPHPExcel->setActiveSheetIndex(0);
		// $objPHPExcel->getActiveSheet()->SetCellValue('A1', lang('surveyTitle'));
		// $objPHPExcel->getActiveSheet()->SetCellValue('B1', lang('question'));
		// $objPHPExcel->getActiveSheet()->SetCellValue('C1', lang('answer'));
		// //$objPHPExcel->getActiveSheet()->SetCellValue('D1', lang('customerId'));
		// $objPHPExcel->getActiveSheet()->SetCellValue('E1', lang('cretime'));
        // $rowCount = 2;
        // foreach ($data as $row) {
        //     $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $row['surveyTitle']);
        //     $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['question']);
        //     $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['answer']);
        //     //$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['customerId']->{'$id'});
        //     $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['cretime']);
        //     $rowCount++;
		// }
		
		$count = 3;
		$listMonthOfYearJ = $this->mongo_db->where(array('tags' => array('month', 'ofyear', 'name')))->getOne(set_sub_collection('Jsondata'));
		$monthOfYearInfo = array_column($listMonthOfYearJ['data'], 'text', 'value');

		$debtGroupRaw = $this->mongo_db->where(array('tags' => array('debt', 'group')))->getOne(set_sub_collection('Jsondata'));
		$debtGroup = array_column($debtGroupRaw['data'], 'text');
		asort($debtGroup);

		$debtDueMonthRaw = $this->mongo_db->where(array('tags' => array('debt', 'duedate')))->getOne(set_sub_collection('Jsondata'));
		$debtDueMonth = array_column($debtDueMonthRaw['data'], 'text');

		$debtGroupByDueDate = array();

		foreach($debtGroup as $dGroup) {
			foreach($debtDueMonth as $dueDate) {
				array_push($debtGroupByDueDate, $dGroup . $dueDate);
			}
		}

		$monthInYear = array();
		$dayData = array();
		$year = date("Y");

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

			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 4), 'TARGET OF COLLECTION');
			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 4) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 5));

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 6) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 7));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 6), 'DAILY');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 8) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 9));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 8), 'RESULT END OF DAY');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 10) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 11));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 10), 'ACCUMULATED');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 12) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 13));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 12), 'RATIO (vs target)');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 14) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 15));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 14), 'RATIO (vs start)');

			$worksheet->mergeCells('A' . ($countGroup * 17 + $positionRowOfGroupName + 16) . ':A' . ($countGroup * 17 + $positionRowOfGroupName + 17));
			$worksheet->setCellValue('A' . ($countGroup * 17 + $positionRowOfGroupName + 16), 'FINAL No');

			$countGroup++;
			$positionRowOfGroupName++;
		}
		
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