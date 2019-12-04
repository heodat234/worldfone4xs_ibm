<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Import extends WFF_Controller {

	function __construct()
    {
    	parent::__construct();
    	$this->load->model('import_model');
        header('Content-type: application/json');
        $this->only_main_content = (bool) $this->input->get("omc");
        // $this->collection = set_sub_collection($this->collection);
    }

    // function read()
    // {
    //     try {
    //         $request = json_decode($this->input->get("q"), TRUE);

    //         $arr = array();
    //         $path = FCPATH.'upload/users/import/';

    //         $items = array_diff(scandir($path), array('..', '.'));
    //         foreach ($items as $name) {
    //                 $row['file_path'] = $path . $name;
    //                 $file_info = new SplFileInfo($row['file_path']);
    //                 $row['file_name'] = $file_info->getFilename();
    //                 $ext = $file_info->getExtension();
    //                 array_push($arr, $row);
    //         }
    //         $response = array('data'=> $arr, 'total' => count($arr));
    //         echo json_encode($response);
    //     } catch (Exception $e) {
    //         echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    //     }
    // }
    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $file_path = '/data/upload_file/';
            if (in_array(ENVIRONMENT, array('UAT', 'development'))) {
                $today = strtotime("2019-11-20 00:00:00");
            }
            else {
                $today = strtotime('today 00:00:00');
            }
            $todayFile = date('Ymd', $today);
            $file_path = $file_path . $todayFile . '/';
            $file_name = 'callinglist.csv';
            $existFile = file_exists($file_path . $file_name);
            if($existFile) {
                echo json_encode(array('data' => array(array('file_path' => $file_path . $file_name, 'file_name' => $file_name)), 'total' => 1));
            }
            else {
                echo json_encode(array('data' => array(array('file_path' => $file_path . $file_name, 'file_name' => '')), 'total' => 0));
            }
        }
        catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    
}