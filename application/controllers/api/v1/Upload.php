<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Upload extends WFF_Controller {

	function __construct()
	{
		parent::__construct();
		header('Content-type: application/json');
	}

	function avatar($folder = "")
	{
		try {
			$collection = set_sub_collection("Picture");
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . PICTURE_PATH . $folder . "/";
			//pre($path);
			if (!@file_exists($path)) { 
				@mkdir(PICTURE_PATH . $folder, 0755);
			}
			$file = $_FILES['file'];
			$file_parts = @pathinfo($file['name']);
			$allowed_types = 'jpeg|jpg|png|gif|ico';
			$filesize = @filesize($file['tmp_name']);
			if(strpos($allowed_types, strtolower($file_parts['extension'])) === FALSE) throw new Exception("Wrong file type. Only accept jpeg, png or gif");
			if($filesize > 1000000) throw new Exception("File too large");
			$new_file_name = str_replace([" ","/"], ["",""], $file['name']);
			$file_path = $path . $new_file_name;
			if (@is_uploaded_file($file['tmp_name'])) {
				@move_uploaded_file($file['tmp_name'] , $file_path);
			}
			$data = array(
				'name'		=> $file['name'],
				'type'		=> $folder,
				'uploadname'=> $file['name'],
				'filename' 	=> $new_file_name,
				'filepath'	=> PICTURE_PATH . $folder . "/" . $new_file_name,
				'size' 		=> $filesize,
				'extension' => $file_parts['extension'],
				'createdBy'	=> $this->session->userdata("extension"),
				'createdAt' => time(),
				'time'		=> (new DateTime())->format('Y-m-d H:i:s')	
			);
			$this->load->library("mongo_db");
			$this->mongo_db->insert($collection, $data);
			echo json_encode(array("status" => 1, "message" => "Upload successfully", "filepath" => $data["filepath"]));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function images($folder = "")
	{
		try {
			$collection = set_sub_collection("Picture");
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . PICTURE_PATH . $folder . "/";
			//pre($path);
			if (!@file_exists($path)) { 
				@mkdir(PICTURE_PATH . $folder, 0755);
			}
			$filepaths = [];
			$files = $_FILES['files'];
			pre($files);
			foreach ($files as $file) {
				$file_parts = @pathinfo($file['name']);
				$allowed_types = 'jpeg|jpg|png|gif|ico';
				$filesize = @filesize($file['tmp_name']);
				if(strpos($allowed_types, strtolower($file_parts['extension'])) === FALSE) throw new Exception("Wrong file type. Only accept jpeg, png or gif");
				if($filesize > 1000000) throw new Exception("File too large");
				$new_file_name = str_replace([" ","/"], ["",""], $file['name']);
				$file_path = $path . $new_file_name;
				if (@is_uploaded_file($file['tmp_name'])) {
					@move_uploaded_file($file['tmp_name'] , $file_path);
				}
				$data = array(
					'name'		=> $file['name'],
					'type'		=> $folder,
					'uploadname'=> $file['name'],
					'filename' 	=> $new_file_name,
					'filepath'	=> PICTURE_PATH . $folder . "/" . $new_file_name,
					'size' 		=> $filesize,
					'extension' => $file_parts['extension'],
					'createdBy'	=> $this->session->userdata("extension"),
					'createdAt' => time(),
					'time'		=> (new DateTime())->format('Y-m-d H:i:s')	
				);
				$this->load->library("mongo_db");
				$this->mongo_db->insert($collection, $data);
				$filepaths[] = $data["filepath"];
			}
			echo json_encode(array("status" => 1, "message" => "Upload successfully", "filepaths" => $filepaths));
		} catch (Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function file($folder = "")
	{
		try {
			$collection = set_sub_collection("Attachment");
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . UPLOAD_PATH . $folder . "/";
			if (!@file_exists($path)) { 
				@mkdir(UPLOAD_PATH . $folder, 0755);
			}
			$file = $_FILES['file'];
			$file_parts = @pathinfo($file['name']);
			$notallowed_types = 'php|sh|bash';
			$filesize = @filesize($file['tmp_name']);
			if(strpos($notallowed_types, strtolower($file_parts['extension'])) !== FALSE) throw new Exception("Wrong file type.");
			if($filesize > 10000000) throw new Exception("File too large. Over 10MB.");
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

	function capture() {
		try {
			$request = $_POST;
			$collection = set_sub_collection("Picture");

			if(!isset($request["dataImg"], $request["filename"])) throw new Exception("Lack of input");

			$this->load->library("session");
			$this->load->library("mongo_db");
			$request["createdAt"] = time();
			$request["createdBy"] = $this->session->userdata("extension");
			
			// Create file
			$folder = "capture";
			$path = "./" . PICTURE_PATH . $folder . "/";
			if (!@file_exists($path)) { 
				@mkdir(PICTURE_PATH . $folder, 0755);
			}
			$dataImg = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request["dataImg"]));
			if ($dataImg === false) {
		        throw new Exception('base64_decode failed');
		    }
			@file_put_contents($path . $request["filename"], $dataImg);
			// Insert mongo
			
			$filepath = PICTURE_PATH . $folder . "/" . $request["filename"];
			$data = array(
				'name'		=> $request["filename"],
				'type'		=> $folder,
				'uploadname'=> $request["filename"],
				'filename' 	=> $request["filename"],
				'filepath'	=> $filepath,
				'size' 		=> @filesize($filepath),
				'extension' => "png",
				'createdBy'	=> $this->session->userdata("extension"),
				'createdAt' => time(),
				'time'		=> (new DateTime())->format('Y-m-d H:i:s')	
			);
			$this->mongo_db->insert($collection, $data);
			echo json_encode(array("status" => 1, "message" => "Upload successfully", "filepath" => $filepath));
		} catch(Exception $e) {
			echo json_encode(array("status" => 0, "message" => $e->getMessage()));
		}
	}

	function attachment()
	{
		try {
			$folder = "attachment/out";
			$collection = set_sub_collection("Attachment");
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . UPLOAD_PATH . $folder . "/";
			if (!@file_exists($path)) { 
				@mkdir(UPLOAD_PATH . $folder, 0755);
			}
			$file = $_FILES['file'];
			$file_parts = @pathinfo($file['name']);
			$notallowed_types = 'php|sh|bash';
			$filesize = @filesize($file['tmp_name']);
			if(strpos($notallowed_types, strtolower($file_parts['extension'])) !== FALSE) throw new Exception("Wrong file type.");
			if($filesize > 10000000) throw new Exception("File too large. Over 10MB.");
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

	function library()
	{
		try {
			$folder = "library";
			$collection = set_sub_collection("File");
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . UPLOAD_PATH . $folder . "/";
			if (!@file_exists($path)) { 
				@mkdir(UPLOAD_PATH . $folder, 0755);
			}
			$file = $_FILES['file'];
			$file_parts = @pathinfo($file['name']);
			$notallowed_types = 'php|sh|bash';
			$filesize = @filesize($file['tmp_name']);
			if(strpos($notallowed_types, strtolower($file_parts['extension'])) !== FALSE) throw new Exception("Wrong file type.");
			if($filesize > 10000000) throw new Exception("File too large. Over 10MB.");
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

	function excel()
	{
		try {
			$folder = "excel";
			$collection = set_sub_collection("File");
			if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
			$path = "./" . UPLOAD_PATH . $folder . "/";
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

    function csv()
    {
        try {
            print_r("TEST");
            $folder = "csv";
            $collection = set_sub_collection("File");
            if ($this->input->server('REQUEST_METHOD') !== 'POST') throw new Exception("Wrong method");
            $path = "./" . UPLOAD_PATH . $folder . "/";
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