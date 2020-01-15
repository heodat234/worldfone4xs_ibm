<?php
class Pbxacsevents extends CI_Controller 
{

	function __construct() {
		parent::__construct();
		$this->load->library('mongo_db');
		$this->load->library('mongo_private');
	}

	public function webhook_acsresult() {
    	header('Content-type: application/json');
    	
		try {
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new Exception('Bad Method Request', 400);
			}

	    	$config = $this->mongo_private->getOne('ConfigType');          			

			if (!isset($_POST['secret'])) {
				throw new Exception("Lack of secret", 401);
			}

			$config_type = $this->mongo_private->where(["secret_key"=>$_POST["secret"]])->getOne("ConfigType");

			if (!$config_type) {
				throw new Exception("Unauthorized", 401);
			}
			
			unset($_POST['secret']);

			if(!empty($_POST['acs_call_duration'])) {
				$_POST['acs_call_duration'] = (int) $_POST['acs_call_duration'];
			}

			$ori_collection = "acs_cdr";

			$collection = !empty($config_type["type"]) ? $config_type["type"] . "_" . $ori_collection : $ori_collection;

			$this->mongo_db->where('ua_uuid', $_POST['ua_uuid'])->update($collection, ['$set' => $_POST]);

			echo json_encode(array("status" => 1, "message" => 'Success'));
		} catch (Exception $e) {			
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
    }
}