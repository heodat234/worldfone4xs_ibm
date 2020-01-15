<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Manage_create_calling_list extends WFF_Controller {

	private $sub = "";

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
		$this->sub = set_sub_collection("");
		error_reporting(E_ALL);
	}

	function read_list_file_import(){
		$today 		= (string)date('Ymd');
		$fileImport = ['LNJC05F', 'LIST_OF_ACCOUNT_IN_COLLECTION_', 'ZACCF', 'sbv_'];
		$beautifulConvert = ['sbv_' => 'File SBV', 'LIST_OF_ACCOUNT_IN_COLLECTION_' => ' File LIST_OF_ACCOUNT_IN_COLLECTION', 'LNJC05F' => 'File LNJC05F', 'ZACCF' => 'File ZACCF'];
		$dir 		= ENVIRONMENT == 'development' ? '/data/upload_file/' . $today .'/' : '/mnt/nas/upload_file/' . $today .'/';
		$temp 	= ['LNJC05F' => false, 'LIST_OF_ACCOUNT_IN_COLLECTION_' => false, 'ZACCF' => false, 'sbv_' => false];
		if(file_exists($dir) == true) {			
			$files = scandir($dir);
			foreach ($fileImport as $key => $import) {
				foreach ($files as $key => $file) {
					$check = strpos($file, $import) !== false ? true : false;
					if($check){
						$temp[$import] = true;
					}
				}
			}
		}

		$result = [];
		foreach ($temp as $key => $value) {
			$result[] = array(
				'fileName' => $beautifulConvert[$key],
				'status'   => $value,
			);
		}

		echo json_encode($result);
	}

	function read_list_data_imported(){
		$this->load->library("mongo_db");
		$data['LNCJ05'] = $this->mongo_db->count('LO_LNJC05');
		$data['ListOfAccount'] = $this->mongo_db->count('LO_List_of_account_in_collection');
		$data['Zaccf'] = date('d-m-Y H:i:s',$this->mongo_db->order_by(array('_id' => -1))->getOne('LO_ZACCF')['createdAt']);
		$data['SBV'] = date('d-m-Y H:i:s',$this->mongo_db->order_by(array('_id' => -1))->getOne('LO_SBV')['created_at']);

		$result = [];
		foreach ($data as $key => $value) {
			$result[] = array(
				'collection' => $key,
				'totalData'   => $value,
			);
		}
		echo json_encode($result);
	}

	function read_basket_campaign(){
		$this->load->library("mongo_db");
		$this->mongo_db->switch_db('LOAN_campaign_list');
		$SIBS = 0;
		$CARD = 0;
		$listCol = $this->mongo_db->listCollections();
		$this->mongo_db->switch_db('worldfone4xs');
		foreach ($listCol as $key => $col) {
			$checkSIBS = strpos((string)$col, 'SIBS_JIVF') !== false ? true : false;
			if($checkSIBS){
				$SIBS++;
			}
			$checkCARD = strpos((string)$col, 'CARD') !== false ? true : false;
			if($checkCARD){
				$CARD++;
			}
		}
		if($SIBS < 5){
			$checkSIBS_running = $this->mongo_db->where(array('event' => 're_run_SIBS'))->getOne('LO_ManageCreateCallingLists_Status');
			if(count($checkSIBS_running) > 0){
				$result[] = array(
					'basket' =>'SIBS',
					'totaldata'  => 'Running'
				);
			}else{
				$result[] = array(
					'basket' =>'SIBS',
					'totaldata' => $SIBS
				);
			}
		}else{
			$result[] = array(
				'basket' =>'SIBS',
				'totaldata'	=> $SIBS
			);
			$this->mongo_db->where(array('event' => 're_run_SIBS'))->delete_all('LO_ManageCreateCallingLists_Status');
		}
		if($CARD == 0){
			$checkCARD_running = $this->mongo_db->where(array('event' => 're_run_CARD'))->getOne('LO_ManageCreateCallingLists_Status');
			if(count($checkCARD_running) > 0){
				$result[] = array(
					'basket' =>'CARD',
					'totaldata' 	=> 'Running'
				);
			}else{
				$result[] = array(
					'basket' =>'CARD',
					'totaldata' 	=> $CARD
				);
			}
		}else{
			$result[] = array(
				'basket' =>'CARD',
				'totaldata' 	=> $CARD
			);
			$this->mongo_db->where(array('event' => 're_run_CARD'))->delete_all('LO_ManageCreateCallingLists_Status');
		}

		echo json_encode($result);
	}

	function reImport(){
		$this->load->library("mongo_db");
		$fileImport = $_GET['param'];
		switch ($fileImport) {
			case 'LNCJ05':
				$dir = FCPATH . 'cronjob/python/Loan/re_run_sh/re_run_import_LNJC05F.sh';
				$this->insertCronScanning($dir);
				// exec($dir);
			break;

			case 'ListOfAccount':
				$dir = FCPATH . 'cronjob/python/Loan/re_run_sh/re_run_import_ListOfAccount.sh';
				$this->insertCronScanning($dir);
				// exec($dir);
			break;

			case 'Zaccf':
				$dir = FCPATH . 'cronjob/LOAN/import-daily.sh';
				$this->insertCronScanning($dir);
				// exec($dir);
			break;

			case 'SBV':
				$dir = FCPATH . 'cronjob/python/Loan/re_run_sh/re_run_import_SBV.sh';
				$this->insertCronScanning($dir);
				// exec($dir);
			break;
			
			default:
				break;
		}
	}

	function reRunBasketCampaign(){
		$this->load->library("mongo_db");
		$basket = $_GET['param'];
		$this->mongo_db->switch_db('LOAN_campaign_list');
		$listCol = $this->mongo_db->listCollections();
		foreach ($listCol as $key => $col) {
			$check = strpos((string)$col, $basket) !== false ? true : false;
			if($check){
				$this->mongo_db->drop_collection($col);
			}
		}
		$this->mongo_db->switch_db('worldfone4xs');
		print_r($_SERVER['SERVER_ADDR']);
		switch ($basket) {
			case 'SIBS':
				$dir = FCPATH . 'cronjob/LOAN/re_run_sh/re_run_makeCallingList_SIBS.sh';
				echo $dir;
				$this->insertCronScanning($dir);
				// exec($dir);
				$this->mongo_db->insert('LO_ManageCreateCallingLists_Status', array('event' => 're_run_SIBS'));
			break;

			case 'CARD':
				$dir = FCPATH . 'cronjob/LOAN/re_run_sh/re_run_makeCallingList_CARD.sh';
				echo $dir;
				$this->insertCronScanning($dir);
				// exec($dir);
				$this->mongo_db->insert('LO_ManageCreateCallingLists_Status', array('event' => 're_run_CARD'));
			break;
			
			default:
				# code...
				break;
		}
	}

	function read_diallist(){
		$this->load->library('mongo_db');
		$midnight = strtotime('today midnight');

		$total_detail_SIBS = $this->mongo_db->where(
			array(
				"createdAt" => array('$gt' => $midnight),
				"from"		=> array('$regex' => 'SIBS')
			)
		)->count('LO_Diallist_detail');

		$total_detail_CARD = $this->mongo_db->where(
			array(
				"createdAt" => array('$gt' => $midnight),
				"from"		=> array('$regex' => 'CARD')
			)
		)->count('LO_Diallist_detail');

		$result[] = array(
			'diallist' => "SIBS",
			'total_detail' => $total_detail_SIBS
		);
		$result[] = array(
			'diallist' => "CARD",
			'total_detail' => $total_detail_CARD
		);
		echo json_encode($result);
	}

	function reRunDiallist(){
		$this->load->library("mongo_db");
		$midnight_timestamp = strtotime('today midnight');
		$diallist_team = $_GET['param'];

		$this->mongo_db->where(
			array(
				'createdAt' => array('$gt' => $midnight_timestamp),
				'team'	=> $diallist_team,
			)
		)->delete_all('LO_Diallist');

		switch ($diallist_team) {
			case 'SIBS':
			$dir = FCPATH . 'cronjob/LOAN/re_run_sh/re_run_autoCreateDiallist_SIBS.sh';
			echo $dir;
			$this->insertCronScanning($dir);
			// exec($dir);
			break;

			case 'CARD':
			$dir = FCPATH . 'cronjob/LOAN/re_run_sh/re_run_autoCreateDiallist_CARD.sh';
			echo $dir;
			$this->insertCronScanning($dir);
			// exec($dir);
			break;
			
			default:
				# code...
			break;
		}
	}

	function oneUserOnly(){
		$this->load->library("mongo_db");
		$extension = $this->session->userdata("extension");
		$user_accessed = $this->mongo_db->where(array('event' => 'access', 'createdAt' => array('$gt' => time() - 8)))->getOne('LO_ManageCreateCallingLists_Status');

		if(empty($user_accessed)){
			$this->mongo_db->where('event','access')->delete_all('LO_ManageCreateCallingLists_Status');
			$this->mongo_db->insert('LO_ManageCreateCallingLists_Status', array('event' => 'access', 'extension' => $extension, 'createdAt' =>  time()));
			echo json_encode(array('status' => 1, 'extension' => $extension));
		}else{
			if($user_accessed['extension'] == $extension){
				$this->mongo_db->where(array('event' => 'access', 'extension' => $extension))->update_all('LO_ManageCreateCallingLists_Status', array('$set' => array('createdAt' => time())));
				echo json_encode(array('status' => 1, 'extension' => $extension));
			}else{
				echo json_encode(array('status' => 0, 'extension' => $user_accessed['extension'] ));
			}
		}
	}

	function insertCronScanning($dir){
		$this->mongo_db->insert('LO_Cron_scanning', array('dir' => $dir, 'runned' => false, 'time' => date('d-m-Y H:i:s', time())));
	}


}