<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends WFF_Controller {

	function __construct()
    { 
    	parent::__construct();
    	$this->load->model('import_model');
        $this->only_main_content = (bool) $this->input->get("omc");
        
    }

    public function index() {
    	$this->_build_template($this->only_main_content);
        $this->output->test($this->data);
        $this->output->data["css"][] = STEL_PATH . "css/table.css";
        $this->output->data["js"][] = KENDOUI_PATH . "js/jszip.min.js";
        $this->output->data["js"][] = STEL_PATH . "js/tools.js";
        // $this->output->data["js"][] = STEL_PATH . "js/manage/customer.js";
        $this->load->view('import/import_view', $_GET);
    }

    public function upload($collection)
    {
    	$json = array();
        $config['upload_path']          = './upload/users/import';
        $config['allowed_types']        = 'csv||doc|docx|xls|xlsx|application/vnd.ms-excel|zip|7zip|rar|application/x-rar-compressed|application/rar|application/x-rar|application/octet-stream|application/force-download|pdf|application/pdf';
        $config['max_size']             = 25000;
        $config['file_name'] = $_FILES['file']['name'];

        $start = time();
        $complete = 0;
        if (file_exists(FCPATH.'/upload/users/import') == "") {
            mkdir( FCPATH.'/upload/users/import', 0777, true );
        }

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('file')){
            $error = array('error' => $this->upload->display_errors());
            $json['error'] = $error['error'];
            $status = 0;
            $message = 'Upload error';
        }
        else{
        	$complete = time();
            $data = array('upload_data' => $this->upload->data());
            $duoifile = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $filePath =$data['upload_data']['full_path'];
           
            if(in_array($duoifile,array("jpg","jpe","jpeg","gif","png")) ) {
                $type = 'image';
            }else{
                $type = 'file';
            }            
     		try {
     			// $collection = 'Telesalelist';
            	$this->import_model->importData($filePath,$duoifile,$collection);
            	$status = 1;
            	$message = 'Upload successfully';
            } 
            catch (Exception $e) {
            	$status = 0;
            	$message = 'Upload error';
			}	
	        
        }
        $dataImport = array(
     				'begin_import' 	          => $start,
     				'complete_import' 		  => $complete,
     				'file_name' 	          => $config['file_name'],
     				'source'		          => 'Manual',
     				'status'		          => $status
     			);
        $this->import_model->importFile($dataImport);
        echo json_encode(array("status" => $status, "message" => $message));
    }
}