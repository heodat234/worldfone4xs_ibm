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

    function read()
    {
        try {
            $request = json_decode($this->input->get("q"), TRUE);

            $arr = array();
            $path = FCPATH.'upload/users/import/';

            $items = array_diff(scandir($path), array('..', '.'));
            foreach ($items as $name) {
                    $row['file_path'] = $path . $name;
                    $file_info = new SplFileInfo($row['file_path']);
                    $row['file_name'] = $file_info->getFilename();
                    $ext = $file_info->getExtension();
                    array_push($arr, $row);
            }
            $response = array('data'=> $arr, 'total' => count($arr));
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }

    public function upload($collection1)
    {
      $collection = set_sub_collection($collection1);
      // var_dump($collection);exit;
        // $key = ini_get("session.upload_progress.prefix") . "advancedProgress";
        // if (isset($_SESSION[$key]))
        // {
        //     var_dump('expression');exit;
        // }
    	$json = array();
        $config['upload_path']          = './upload/users/import';
        $config['allowed_types']        = 'csv||doc|docx|xls|xlsx|application/vnd.ms-excel|zip|7zip|rar|application/x-rar-compressed|application/rar|application/x-rar|application/octet-stream|application/force-download|pdf|application/pdf';
        $config['max_size']             = 204800;
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
            $filePath = '';
        }
        else{
        	   $complete = time();
            $data = array('upload_data' => $this->upload->data());
            $duoifile = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $filePath = $data['upload_data']['full_path'];
            $config['file_name'] = $data['upload_data']['file_name'];
            if(in_array($duoifile,array("jpg","jpe","jpeg","gif","png")) ) {
                $type = 'image';
            }else{
                $type = 'file';
            }
     		  $status = 1;
	        $error = [];
        }
         $dataImport = array(
            'collection'              => $collection1,
            'begin_import'            => $start,
            'complete_import'         => 0,
            'file_name'               => $config['file_name'],
            'file_path'               => $filePath,
            'source'                  => 'Manual',
            'status'                  => 2
        );
        $idImport = $this->import_model->importFile($dataImport);

        try {
            if ($status == 1) {
               $output = shell_exec('python3.6 /var/www/html/python/excel_1.py ' . $idImport . " ". $collection ." 2>&1");
               $response = trim($output);
               $response = substr($response, -2, 1);
                // $response = $this->import_model->importData($filePath,$duoifile,$collection,$idImport);
                if ($response == 1) {
                    $message = 'Upload successfully';
                }else{
                    $status = 0;
                    $message = 'Upload error';
                }
            }else{
                $dataImport = array(
                    'complete_import'         => time(),
                    'status'                  => $status,
                );
                $this->import_model->updateImportHistory($idImport,$dataImport);
            }
        }
        catch (Exception $e) {
            $status = 0;
            $message = 'Upload error';
        }
        echo json_encode(array("status" => $status, "message" => $message));
    }

    function importFTP($collection)
    {
        try {
            $file_path = $this->input->post('file_path');
            $file_name = $this->input->post('file_name');
            $duoifile = pathinfo($file_name, PATHINFO_EXTENSION);

            $dataImport = array(
                'collection'              => $collection,
                'begin_import'            => time(),
                'complete_import'         => 0,
                'file_name'               => $file_name,
                'source'                  => 'FTP',
                'status'                  => 2
            );
            $idImport = $this->import_model->importFile($dataImport);
            $response = $this->import_model->importData($file_path,$duoifile,$collection,$idImport);
            if ($response == 1) {
                $dataImport = array(
                    'complete_import'         => time(),
                    'status'                  => 1
                );
                $this->import_model->updateImportHistory($idImport,$dataImport);
                $status = 1;
                $message = 'Upload successfully';
            }else{
                $status = 0;
                $dataImport = array(
                    'complete_import'         => time(),
                    'status'                  => $status,
                    'error'                   => $response
                );
                $this->import_model->updateImportHistory($idImport,$dataImport);
                $status = 0;
                $error = $response;
                $message = 'Upload successfully';
            }

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