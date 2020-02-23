<?php
class App extends CI_Controller 
{
	private $caseCol;

	function __construct() {
		parent::__construct();
		$this->load->library('mongo_db');		
		$this->load->library('mongo_private');

		$this->caseCol = 'TS_Appointment';
	}

	public function auth() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_POST['email']) || !isset($_POST['password'])) {				
				throw new Exception('Unauthorized', 401);
			}			
			
			$user = $this->mongo_db
				->select(['email', 'password', 'token', 'name'])
				->where([
					'email' => strtolower($_POST['email']),
				])
				->getOne('app_users');

			if (!$user) {
				throw new Exception("Email not registered", 401);				
			}

			if (!password_verify($_POST['password'], $user['password'])) {
				throw new Exception("Invalid Password", 401);				
			}

			$token = '';
			if (!isset($user['token']) || $user['token'] == '') {
				$token = $_POST['email'] . bin2hex(openssl_random_pseudo_bytes(64));
				$this->mongo_db->where(['email' => $_POST['email']])->set(['token' => $token])->update('app_users');	
			} else {
				$token = $user['token'];
			}

			echo json_encode(array('status' => 1, 'message' => 'Success', 'token' => $token, 'username' => isset($user['name']) ? $user['name'] : ''));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function changePassword() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['newPassword']) || !isset($_POST['confirmNewPassword'])) {
				throw new Exception('Unauthorized', 401);
			}

			if ($_POST['newPassword'] != $_POST['confirmNewPassword']) {
				throw new Exception('New Passwords are not the same', 401);	
			}
			
			$user = $this->mongo_db
				->select(['email', 'password'])
				->where([
					'email' => strtolower($_POST['email']),
				])
				->getOne('app_users');

			if (!$user) {
				throw new Exception("Email not registered", 401);				
			}

			if (!password_verify($_POST['password'], $user['password'])) {
				throw new Exception("Invalid Password", 401);				
			}

			$newPassword = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);

			$this->mongo_db->where('email', $user['email'])->set(['password' => $newPassword])->update('app_users');

			echo json_encode(array('status' => 1, 'message' => 'Success'));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function logout() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_POST['token']) || !isset($_POST['deviceToken'])) {				
				throw new Exception('Unauthorized', 401);
			}			
			
			$user = $this->mongo_db
				->select(['deviceToken'])
				->where([
					'token' => $_POST['token'],
				])
				->getOne('app_users');

			$deviceToken = $user['deviceToken'];

			foreach ($deviceToken as $key => $value) {
				if ($value == $_POST['deviceToken']) {
					unset($deviceToken[$key]);
				}
			}						

			$this->mongo_db->where('token', $_POST['token'])->update('app_users', ['$set' => ['deviceToken' => $deviceToken]]);

			echo json_encode(array('status' => 1, 'message' => 'Success'));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function postCase() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new Exception('Bad Method Request', 403);
			}	

			if (!isset($_POST['token'])) {				
				throw new Exception('Unauthorized', 401);
			}
			
			$user = $this->mongo_db				
				->where([
					'token' => $_POST['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}			

			$data = json_decode($_POST['case'], true);

			$data['received_at'] = time();
			$data['images'] = isset($_POST['images']) ? $_POST['images'] : [];
			$data['created_by'] = $user['email'];
			$data['uuid'] = $_POST['uuid'];

			$this->mongo_db->insert($this->caseCol, $data);			

			echo json_encode(array('status' => 1, 'message' => 'Success'));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function getCases() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			$user = $this->mongo_db
				->select(['token', 'email'])
				->where([
					'token' => $_GET['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}			

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$result = isset($_GET['result']) ? $_GET['result'] : 10;

			$data = $this->mongo_db->select(['uuid', 'created_at', 'status', 'created_by', 'cus_name'])->where('created_by', $user['email'])->limit($result)->offset(($page - 1)*$result)->order_by(['created_at' => 'desc'])->get($this->caseCol);

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => $data));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function searchCases() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			if (!isset($_GET['qs']) || $_GET['qs'] == '') {
				throw new Exception('Bad Request', 400);
			}

			$user = $this->mongo_db
				->select(['token', 'email'])
				->where([
					'token' => $_GET['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}			

			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$result = isset($_GET['result']) ? $_GET['result'] : 10;

			$data = $this->mongo_db->select(['uuid', 'created_at', 'status', 'created_by', 'cus_name'])
				->where('created_by', $user['email'])				
				->where([
					'$or' => [
						['cus_name' => ['$regex' => $_GET['qs']]],
						['cmnd' => ['$regex' => $_GET['qs']]],
						['cus_phone' => ['$regex' => $_GET['qs']]],
					]
				])
				
				->limit($result)
				->offset(($page - 1)*$result)
				->order_by(['created_at' => 'desc'])
				->get($this->caseCol);

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => $data));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function getCaseDetail() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			$user = $this->mongo_db
				->select(['token', 'email'])
				->where([
					'token' => $_GET['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}		

			if (!isset($_GET['uuid'])) {	
				throw new Exception('Bad Request', 400);
			}

			$data = $this->mongo_db->where(['uuid' => $_GET['uuid'], 'created_by' => $user['email']])->getOne($this->caseCol);

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => $data));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function pushDeviceToken() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new Exception('Bad Method Request', 403);
			}	

			if (!isset($_POST['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			if (!isset($_POST['deviceToken'])) {				
				throw new Exception('Unauthorized', 400);
			}

			$user = $this->mongo_db->select('deviceToken')->where('token', $_POST['token'])->getOne('app_users');
			$deviceToken = (array)$user['deviceToken'];			
			$deviceToken[] = $_POST['deviceToken'];				
			
			$this->mongo_db->where('token', $_POST['token'])->set(['deviceToken' => (array)array_unique($deviceToken)])->update('app_users');			

			echo json_encode(array('status' => 1, 'message' => 'Success'));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function getDdlDataSource() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			$user = $this->mongo_db
				->select(['token', 'email'])
				->where([
					'token' => $_GET['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}		

			$data = [
				'dealer' => $this->getDealerDDL()
			];

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => $data));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	private function getDealerDDL() {
		$pipeline = array(
			array(
				'$match' => array(
					'location' => array('$ne' => null)
				)
			),
			array(
				'$group' => array(
					'_id' => '$location',					
					'dealer' => array('$push'=> array('dealer_code' => '$dealer_code', 'dealer_name' => '$dealer_name'))
				)
			)
		);

		$data = $this->mongo_db->aggregate_pipeline('TS_Dealer', $pipeline);

		return $data;
	}

	public function getLastestDdlUpdateTime() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			$user = $this->mongo_db
				->select(['token', 'email'])
				->where([
					'token' => $_GET['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}		

			$config = $this->mongo_db->select(['lastestDdlUpdateTime'])->getOne('app_config');
			$lastestDdlUpdateTime = isset($config['lastestDdlUpdateTime']) ? $config['lastestDdlUpdateTime'] : time();

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => (int)$lastestDdlUpdateTime));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function getDdlDataSourceForWF() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token']) || $_GET['token'] != 'linhdeptrai') {				
				throw new Exception('Unauthorized', 401);
			}

			$serviceItems = array();
			$service_lv1 = $this->mongo_db->where(array("lv" => 1))->select(["name", "lv"])->get('2_Service_level');
			foreach ($service_lv1 as $doc1) {
				$service_lv2 = $this->mongo_db->where(array("lv" => 2, "parent_id" => new MongoDB\BSON\ObjectId($doc1["id"])))
				->select(["name", "lv"])->get('2_Service_level');
				foreach ($service_lv2 as $doc2) {
					$service_lv3 = $this->mongo_db->where(array("lv" => 3, "parent_id" => new MongoDB\BSON\ObjectId($doc2["id"])))
					->select(["name", "lv"])->get('2_Service_level');
					foreach ($service_lv3 as $doc3) {
						$serviceItems[] = array(
							"value" 		=> $doc1["name"] . " / " . $doc2["name"] . " / " . $doc3["name"],
						); 
					}
				}
			}

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => $serviceItems));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function getStats() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			$user = $this->mongo_db
				->select(['token', 'email'])
				->where([
					'token' => $_GET['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}		
			
			$beginDayOfMonthTS = strtotime(date('Y-m-1'));
			$beginDayOfYearTS = strtotime(date('Y-1-1'));

			$monthsInYearArr = [];
			for ($i = 1; $i <= date('m'); $i++) {
				$monthsInYearArr[$i]['month'] = (int)$i;
				$monthsInYearArr[$i]['from'] = strtotime(date('Y-'.$i.'-1'));
				$monthsInYearArr[$i]['to'] =  strtotime(date('Y-'.(string)($i+1).'-1')) - 1;
			}

			$rawData1 = $this->mongo_db->select('_id', 'created_at')
				//->where('source', 'App Ticket')
				->where('created_by', $user['email'])
				->where_gte('created_at', $beginDayOfYearTS)
				->get($this->caseCol);

			$data1 = [];			
			foreach ($monthsInYearArr as $month) {
				$count = 0;

				foreach ($rawData1 as $rawData) {				
					if ($rawData['created_at'] >= $month['from'] && $rawData['created_at'] <= $month['to']) {
						$count++;
					}
				}

				$data1[] = [
					'month' => $month['month'],
					'count' => $count
				];
			}

			$pipeline2 = array(
				array(
					'$match' => array(
						'$and' => array(
							//array('source' => 'App Ticket'),
							array('created_by' => $user['email']),
							array('created_at' => array('$gte' => $beginDayOfMonthTS)),
						)
					)
				),
				array(
					'$group' => array(
						'_id' => '$status',					
						'count' => array('$sum'=> 1)
					)
				)
			);

			$data2 = $this->mongo_db->aggregate_pipeline($this->caseCol, $pipeline2);

			$rawData3 = $this->mongo_db->select('_id', 'created_at')
				//->where('source', 'App Ticket')
				->where('created_by', $user['email'])
				->where_gte('created_at', $beginDayOfMonthTS)
				->get($this->caseCol);

			$daysInMonthArr = [];
			for ($i = 1; $i <= date('d'); $i++) {
				$daysInMonthArr[$i]['day'] = $i;
				$daysInMonthArr[$i]['from'] = strtotime('midnight', strtotime(date('Y-m-'.$i)));
				$daysInMonthArr[$i]['to'] =  strtotime('tomorrow', $daysInMonthArr[$i]['from'])  - 1;
			}

			$data3 = [];
			foreach ($daysInMonthArr as $day) {
				$count = 0;

				foreach ($rawData3 as $rawData) {				
					if ($rawData['created_at'] >= $day['from'] && $rawData['created_at'] <= $day['to']) {
						$count++;
					}
				}

				$data3[] = [
					'day' => $day['day'],
					'count' => $count
				];
			}

			$data = [
				'fetchedAt' => time(),
				'data1' => $data1,
				'data2' => $data2,
				'data3' => $data3,
			];

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => $data));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function rateCheckin() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new Exception('Bad Method Request', 403);
			}	

			if (!isset($_POST['secret_token'])) {				
				throw new Exception('Unauthorized', 401);
			}			

			$seed = 'linh dep trai vcl';

			$secret_token = md5(intval(time()/180).$seed);

			if ($secret_token !== $_POST['secret_token']) {
				throw new Exception('Forbidden', 403);
			}

			$data = [
				'rating' => $_POST['rating'],
				'rated_at' => time(),
				'counter' =>  $_POST['counter'],				
				'complain' => $_POST['complain'],
				'complainDetail' => $_POST['complainDetail'],
			];
			
			$this->mongo_db->insert('checkin_rating', $data);			

			echo json_encode(array('status' => 1, 'message' => 'Success'));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}

	public function getCounterDDLDataSource() {
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET') {
				throw new Exception('Bad Method Request', 403);
			}

			if (!isset($_GET['token'])) {				
				throw new Exception('Unauthorized', 401);
			}

			$user = $this->mongo_db
				->select(['token', 'email'])
				->where([
					'token' => $_GET['token'],				
				])
				->getOne('app_users');			

			if (!$user) {
				throw new Exception("Unauthorized", 401);				
			}		

			$_data = $this->mongo_db->get('app_counters');

			$data = [];
			foreach ($_data as $d) {
				$data[] = $d['name'];
			}

			echo json_encode(array('status' => 1, 'message' => 'Success', 'data' => $data));
		} catch (Exception $e) {
			http_response_code($e->getCode());
			echo json_encode(array('status' => 0, 'message' => $e->getMessage()));			
		}
	}
}

?>
