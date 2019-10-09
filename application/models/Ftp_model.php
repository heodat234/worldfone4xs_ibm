<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Ftp_model extends CI_Model {

	private $host = "";
    private $username = "";
	private $password = "";

	function __construct()
	{
		parent::__construct();
        $this->load->config("jaccs");
		$this->host = $this->config->item('ftp_host');
		$this->username = $this->config->item('ftp_username');
		$this->password = $this->config->item('ftp_password');
	}

	function connectToFTP() {
        $connId = ftp_connect($this->host);
        if($connId) {
            if(@ftp_login($connId, $this->username, $this->password)){
                return array("status" => "1", "message" => "", "data" => $connId);
            }else{
                return array("status" => "0", "message" => "Couldn't connect as $this->username", "data" => null);
            }
        }
        else return array("status" => "0", "message" => "Couldn't connect to $this->host", "data" => null);
    }

    function listFileInFTP($connId, $directory = ".") {
        $file_list = ftp_nlist($connId, $directory);
        if(!empty($file_list)) {
            return array("status" => "1", "message" => "", "data" => $file_list);
        }
        else {
            return array("status" => "0", "message" => "Couldn't find any file in FTP directory", "data" => null);
        }
    }

    function downloadFileFromFTP($localFilePath, $remoteFilePath) {
        // try to download a file from server
        $connId = $this->connectToFTP();
        if(ftp_get($connId, $localFilePath, $remoteFilePath, FTP_BINARY)){
            $result = array("status" => "1", "message" => "File transfer successful - $localFilePath", "data" => null);
        }else{
            $result = array("status" => "0", "message" => "There was an error while downloading $localFilePath", "data" => null);
        }
        $this->closeFTP($connId);
        return $result;
    }

    function uploadFileToFTP($connId, $localFilePath, $remoteFilePath) {
        // try to upload file
        if(ftp_put($connId, $remoteFilePath, $localFilePath, FTP_ASCII)){
            return array("status" => "1", "message" => "File transfer successful - $localFilePath", "data" => null);
        }else{
            return array("status" => "0", "message" => "There was an error while uploading $localFilePath", "data" => null);
        }
    }

    function closeFTP($connId) {
        ftp_close($connId);
    }
}