<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

defined('PROUI_PATH') OR define('PROUI_PATH', base_url('public/proui/'));

defined('KENDOUI_PATH') OR define('KENDOUI_PATH', base_url('public/kendo/'));

defined('STEL_PATH') OR define('STEL_PATH', base_url('public/stel/'));

defined('RINGTONE_PATH') OR define('RINGTONE_PATH', 'public/ringtone/');

defined('PICTURE_PATH') OR define('PICTURE_PATH', 'public/picture/');

defined('UPLOAD_PATH') OR define('UPLOAD_PATH', 'upload/');

defined('CHAT_PATH') OR define('CHAT_PATH', base_url(''));

function pre($list, $exit = true)
{
    echo "<pre>";
    print_r($list);
    if($exit)
    {
    	echo "</pre>";
        die();
    }
}

function check_external($path) {
    return preg_match("/^https?:\/\//", trim($path)) > 0 ? TRUE : FALSE;
}

function fix_current_url()
{
    $CI =& get_instance();
    return $CI->config->base_url($CI->uri->uri_string());
}

function _http_response_message($code) {
    $text = "";
    switch ($code) {
        case 0  : $text = "Can't connect Data server"; break;
        case 100: $text = 'Continue'; break;
        case 101: $text = 'Switching Protocols'; break;
        case 200: $text = 'OK'; break;
        case 201: $text = 'Created'; break;
        case 202: $text = 'Accepted'; break;
        case 203: $text = 'Non-Authoritative Information'; break;
        case 204: $text = 'No Content'; break;
        case 205: $text = 'Reset Content'; break;
        case 206: $text = 'Partial Content'; break;
        case 300: $text = 'Multiple Choices'; break;
        case 301: $text = 'Moved Permanently'; break;
        case 302: $text = 'Moved Temporarily'; break;
        case 303: $text = 'See Other'; break;
        case 304: $text = 'Not Modified'; break;
        case 305: $text = 'Use Proxy'; break;
        case 400: $text = 'Bad Request'; break;
        case 401: $text = 'Unauthorized'; break;
        case 402: $text = 'Payment Required'; break;
        case 403: $text = 'Forbidden'; break;
        case 404: $text = 'Not Found'; break;
        case 405: $text = 'Method Not Allowed'; break;
        case 406: $text = 'Not Acceptable'; break;
        case 407: $text = 'Proxy Authentication Required'; break;
        case 408: $text = 'Request Time-out'; break;
        case 409: $text = 'Conflict'; break;
        case 410: $text = 'Gone'; break;
        case 411: $text = 'Length Required'; break;
        case 412: $text = 'Precondition Failed'; break;
        case 413: $text = 'Request Entity Too Large'; break;
        case 414: $text = 'Request-URI Too Large'; break;
        case 415: $text = 'Unsupported Media Type'; break;
        case 500: $text = 'Internal Server Error'; break;
        case 501: $text = 'Not Implemented'; break;
        case 502: $text = 'Bad Gateway'; break;
        case 503: $text = 'Service Unavailable'; break;
        case 504: $text = 'Gateway Time-out'; break;
        case 505: $text = 'HTTP Version not supported'; break;
        default: $text = 'Unknown http status code: ' . htmlentities($code); break;
    }
    return $text;
}

function find_all_files($dir) 
{ 
    $root = array_diff(scandir($dir), array('..', '.'));
    foreach($root as $value) 
    { 
        if(is_file("{$dir}/{$value}")) {
            $result[]="{$dir}/{$value}";
            continue;
        } 
        foreach(find_all_files("{$dir}/{$value}") as $value) 
        { 
            $result[]=$value; 
        } 
    } 
    return $result; 
}

function interval_duration($starttime, $endtime, $interval = 60) {
    $intervalArr = array();
    $startsplit = $starttime;
    while($startsplit < $endtime) {
        $endsplit = $startsplit + $interval * 60;
        $intervalArr[] = array("start" => $startsplit, "end" => $endsplit);
        $startsplit = $endsplit;
    }
    return $intervalArr;
}

// Function to get the client ip address
function get_client_ip_server() {
    $ipaddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(!empty($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(!empty($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(!empty($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(!empty($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
 
    return $ipaddress;
}

function set_sub_collection($collection = "") {
    $CI =& get_instance();
    $CI->load->library("session");
    $type = $CI->session->userdata("type");
    return $type ? "{$type}_{$collection}" : $collection;
}

function vn_to_str ($str, $slug_mode = false){
	$unicode = array(
		'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
		'd' => 'đ',
		'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
		'i' => 'í|ì|ỉ|ĩ|ị',
		'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
		'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
		'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
		'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
		'D' => 'Đ',
		'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
		'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
		'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
		'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
		'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
	);
	 
	foreach($unicode as $nonUnicode=>$uni){
		$str = preg_replace("/($uni)/i", $nonUnicode, $str);
	}
	if($slug_mode) $str = str_replace(' ', '_', $str);
	return $str;
}

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function timestampToFormattedString($timestamp, $formattedString = 'Y-m-d\TH:i:s\Z') {
    return date($formattedString, $timestamp);
}

function stringDateToFormattedString($stringDate, $formattedString = 'Y-m-d\TH:i:s\Z') {
    $date = date_create($stringDate);
    return date_format($date, $formattedString);
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}