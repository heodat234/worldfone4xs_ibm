<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Readfile_model extends CI_Model {

    private $collection = "ConfigType";
    // Where config
    private $where = array();
    private $config_array = array();
    private $external_path = "externalcrm/";

    function __construct() {
        parent::__construct();
        $this->load->library('mongo_private');
        $this->load->library("session");
        $type = $this->session->userdata("type");
        if($type) $this->where = array("type" => $type);
        $this->config_array = $this->mongo_private->where($this->where)->getOne($this->collection);
        if(empty($this->config_array["pbx_url"]) || empty($this->config_array["secret_key"])) 
            throw new Exception("Lack of config");
    }

    function play_recording($calluuid)
    {
        $params = array("calluuid" => $calluuid);
        $file_url = $this->create_url("playback.php", $params);
        $file_size = $this->curl_get_file_size( $file_url );
        header('Content-Type: audio/mpeg'); 
        header('Accept-Ranges: bytes');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-length: $file_size");
        header('X-Pad: avoid browser bug');
        header('Cache-Control: no-cache');
        readfile($file_url);
    }

    function download_recording($calluuid)
    {
        $params = array("calluuid" => $calluuid);
        $file_url = $this->create_url("playback.php", $params);
        header('Content-Type: audio/mpeg'); 
        header('Cache-Control: no-cache');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"" . $calluuid . ".mp3\""); 
        readfile($file_url);
    }

    function play_voicemail($id)
    { 
        $params = array("voicemailid" => $id);
        $file_url = $this->create_url("playbackVoicemmail.php", $params);
        header('Content-Type: audio/x-wav'); 
        header('Cache-Control: no-cache');
        header('Accept-Ranges: bytes');
        header("Content-Transfer-Encoding: chunked"); 
        readfile($file_url);
    }

    function download_voicemail($id) {
        $params = array("voicemailid" => $id);
        $file_url =  $this->create_url("playbackVoicemmail.php", $params);
        header('Content-Type: audio/x-wav'); 
        header('Cache-Control: no-cache');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"" . $id . ".wav\""); 
        readfile($file_url);
    }

    private function create_url($file, $params)
    {
        $add_param = array("secrect" => $this->config_array["secret_key"], "secret" => $this->config_array["secret_key"]);
        $params = array_merge($params, $add_param);
        $query = http_build_query($params);
        return $this->config_array["pbx_url"] . $this->external_path . $file . "?" . $query;
    }

    private function curl_get_file_size( $url ) {
        // Assume failure.
        $result = -1;

        $curl = curl_init( $url );

        // Issue a HEAD request and follow any redirects.
        curl_setopt( $curl, CURLOPT_NOBODY, true );
        curl_setopt( $curl, CURLOPT_HEADER, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );

        $data = curl_exec( $curl );
        curl_close( $curl );

        if( $data ) {
          $content_length = "unknown";
          $status = "unknown";

          if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
            $status = (int)$matches[1];
          }

          if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
            $content_length = (int)$matches[1];
          }

          // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
          if( $status == 200 || ($status > 300 && $status <= 308) ) {
            $result = $content_length;
          }
        }

        return $result;
    }
}