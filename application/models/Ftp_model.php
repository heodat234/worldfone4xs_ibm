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
            if(ftp_login($connId, $this->username, $this->password)){
                return $connId;
            }else{
                return null;
            }
        }
        else return null;
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
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
        // try to download a file from server
        $connId = $this->connectToFTP();
//        $buff = ftp_nlist($connId, "/");
//        print_r($buff);
//        print_r($connId);
//        ftp_put($connId, '/ZACCF.csv', '/var/www/html/worldfone4xs_ibm/upload/csv/ZACCF.csv', FTP_ASCII);
        if($connId) {
            $fget = ftp_get($connId, (string)$localFilePath, (string)$remoteFilePath, FTP_BINARY);
            if($fget){
                $result = array("status" => "1", "message" => "File transfer successful - $localFilePath", "data" => $localFilePath);
            }else{
                $result = array("status" => "0", "message" => "There was an error while downloading $localFilePath", "data" => null);
            }
            $this->closeFTP($connId);
            return $result;
        }
        else {
            return array("status" => "0", "message" => "There was an error while downloading $localFilePath", "data" => null);
        }
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