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

    public function upload($collection1)
    {
        ini_set('max_execution_time', '300');
        $collection = set_sub_collection($collection1);
      
    	$json = array();
        $path = FCPATH . UPLOAD_PATH .  "users/import/";
        if (!@file_exists($path)) { 
            @mkdir($path, 0755);
        }

        $start = time();
        $complete = 0;
        $file = $_FILES['file'];
        $file_parts = @pathinfo($file['name']);
        $notallowed_types = 'php|sh|bash';
        $filesize = @filesize($file['tmp_name']);
        $file_extension = $file_parts['extension'];
        if(strpos($notallowed_types, strtolower($file_parts['extension'])) !== FALSE) throw new Exception("Wrong file type.");
        // if($filesize > 10000000) throw new Exception("File too large. Over 10MB.");
        $new_file_name = str_replace([" ","/"], ["",""], $file['name']);
        $file_path = $path . $new_file_name;

        // Check exists
        if(@file_exists($file_path)) {
            @unlink($file_path); //remove the file
        }

        if (@is_uploaded_file($file['tmp_name'])) {
            @move_uploaded_file($file['tmp_name'] , $file_path);
        }
        $dataImport = array(
            'collection'              => $collection1,
            'begin_import'            => $start,
            'complete_import'         => 0,
            'file_name'               => $new_file_name,
            'file_path'               => $file_path,
            'source'                  => 'Manual',
            'status'                  => 2
        );
        $idImport = $this->import_model->importFile($dataImport);

        try {
            $extension = $this->session->userdata("extension");
            if ($collection1 == 'Datalibrary') {
                exec('/usr/local/bin/python3.6 '.FCPATH.'cronjob/python/Telesales/importDataLibrary.py ' . $idImport . " ". $collection ." ". $extension ." > /dev/null &");
            }else if ($collection1 == 'Telesalelist'){
                exec('/usr/local/bin/python3.6 '.FCPATH.'cronjob/python/Telesales/importTelesale.py ' . $idImport . " ". $collection ." ". $extension ." > /dev/null &");
            }else if ($collection1 == 'Lawsuit'){
                if ($file_extension = 'xlsx') {
                    exec('/usr/local/bin/python3.6 '.FCPATH.'cronjob/python/Loan/importLawsuit.py ' . $idImport . " ". $collection ." ". $extension ." > /dev/null &");
                }
            }
            
            $status = -1;
            $message = 'Upload pending';
        }
        catch (Exception $e) {
            $status = 0;
            $message = 'Upload error';
        }
        echo json_encode(array("status" => $status, "message" => $message));
    }

    function importFTP($collection1)
    {
        try {
            $collection = set_sub_collection($collection1);
            $file_path = $this->input->post('file_path');
            $file_name = $this->input->post('file_name');
            $duoifile = pathinfo($file_name, PATHINFO_EXTENSION);

            $dataImport = array(
                'collection'              => $collection1,
                'begin_import'            => time(),
                'complete_import'         => 0,
                'file_name'               => $file_name,
                'file_path'               => $file_path,
                'source'                  => 'FTP',
                'status'                  => 2
            );
            $idImport = $this->import_model->importFile($dataImport);
            $extension = $this->session->userdata("extension");
            if ($collection1 == 'Datalibrary') {
                 exec('/usr/local/bin/python3.6 '.FCPATH.'cronjob/python/Telesales/importDataLibrary.py ' . $idImport . " ". $collection ." ". $extension ." > /dev/null &");
            }else{
                exec('/usr/local/bin/python3.6 '.FCPATH.'cronjob/python/Telesales/importTelesale.py ' . $idImport . " ". $collection ." ". $extension ." > /dev/null &");
            }
            $status = -1;
            $message = 'Upload pending';

            echo json_encode(array("status" => $status,"message" => $message));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    function cancelUpload()
    {
        $idImport = $this->input->post('id');
       $dataImport = array(
            'complete_import'         => time(),
            'status'                  => 0
        );
        $this->import_model->updateImportHistory($idImport,$dataImport);
         echo json_encode(array("status" =>1,"message" => 'Cancel upload successfully'));
    }
}