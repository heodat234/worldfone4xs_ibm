<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Import_manual extends WFF_Controller {

	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection("");
		$this->load->library("mongo_db");
		error_reporting(E_ALL);
	}

	function read_list_file_import(){
        $result 	= [];
		$today 		= (string)date('Ymd');
		$yesterday	= (string)(date('Ymd') - 1);
		$starttime 	= strtotime('today 00:00:00');
		$endtime	= strtotime('today 23:59:59');
		$dir 		= ENVIRONMENT == 'development' ? '/data/upload_file/' . $today .'/' : '/mnt/nas/upload_file/' . $today .'/';
		if(file_exists($dir) == true) {			
			$files = scandir($dir);
			foreach ($files as $key => $file) {
				if(!in_array($file, array('.', '..', 'LIST_OF_ACCOUNT_IN_COLLECTION_' . $yesterday, 'LNJC05F', 'ZACCF.txt', 'sbv_' . $yesterday . '.dat', 'gl_' . $yesterday . '.dat', 'appointmentstatus.csv', 'INGLTDF.txt', 'Update.dat', 'LN3206F'))) {
					$importLog = $this->mongo_db->where(array('file_name' => $file, 'begin_import' => array('$gte' => $starttime, '$lte' => $endtime)))->order_by(array('begin_import' => -1))->limit(1)->get($this->sub . 'Import');
					// print_r($importLog);
					array_push($result, array(
						'fileName'  		=> $file,
						'importStatus'    	=> (isset($importLog[0]['status'])) ? $importLog[0]['status'] : -1,
						'total'				=> (!empty($importLog[0]['total'])) ? $importLog[0]['total'] : '',
						'begin_import'		=> (!empty($importLog[0]['begin_import'])) ? $importLog[0]['begin_import'] : '',
						'complete_import'	=> (!empty($importLog[0]['complete_import'])) ? $importLog[0]['complete_import'] : ''
					));
				}
                
            } 
		}
		echo json_encode(array('data' => $result, 'total' => count($result)));
	}

	function reImport(){
		$today 		= (string)date('Ymd');
		$yesterday	= (string)(date('Ymd') - 1);
		$fileImport = $_GET['param'];
		$fileName = '';
		switch ($fileImport) { 
			case 'DANH SACH THU HOI XE.xlsx':
				$fileName = 'importThuHoiXe';
			break;
			case 'DANH_SACH_KHOA_THE.xlsx':
				$fileName = 'importLockCard';
			break;
			case 'Investigation file.xlsx':
				$fileName = 'importInvestigationFile';
			break;
			case 'K20190812.0244.R18.xls':
			case 'K20190812.0244.R18.xlsx':	
				$fileName = 'importK20190812.0244.R18.20191030';
			break;
			case 'Ket qua di site.xlsx':
				$fileName = 'importSiteResult';
			break;
			case 'Lawsuit report.xlsx':
				$fileName = 'importLawsuit';
			break;
			case 'List of customers assigned to partners.xlsx':
				$fileName = 'importListOfCusAssignedToPartner';
			break;
			case 'Trial_Balance_Report_Telling_Each_Account_Information_' . $yesterday . '.txt':
				$fileName = 'importTrialBalanceReport';
			break;
			case 'WO - monthly.xlsx':
				$fileName = 'importWoMonthly';
			break;
			case 'WO all product.xlsx':
				$fileName = 'importWoAllProd';
			break;
			case 'WO payment.xlsx':
				$fileName = 'importWoPayment';
			break;
			default:
				break;
		}
		// exec(FCPATH . 'cronjob/python/Loan/import_manual.sh ' . $fileName, $output, $return);
		$command = escapeshellcmd(FCPATH . 'cronjob/python/Loan/importManual.sh ' . $fileName);
		$output = shell_exec($command);
		print_r($output);
		print_r(FCPATH . 'cronjob/python/Loan/importManual.sh ' . $fileName);
		echo json_encode(array('status' => 2, 'data' => array()));
	}

	function insertCronScanning($dir){
		$this->mongo_db->insert('LO_Cron_scanning', array('dir' => $dir, 'runned' => false, 'time' => date('d-m-Y H:i:s', time())));
	}
}