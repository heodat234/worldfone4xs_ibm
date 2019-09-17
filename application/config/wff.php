<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Author: dung.huynh@southtelecom.vn
 *
 * Configuration file for worldfone4xs`
 *
 */
// Default
// default_preference cua moi user
$config["default_preference"] = array(
	"language"		=> "eng",
	"ringtone"		=> "",
	"avatar"		=> "",
	"sound_effect"	=> FALSE
);
// Config cua chung he thong
$config['wff_version'] 				= "1.0";

$config['wff_env'] 					= "DEV";

$config['wff_unique_login'] 		= TRUE;

$config['wff_time_cache'] 			= 60;

$config['wff_auth_redirect'] 		= TRUE;

$config['record_activity']			= TRUE;

$config['use_worker']				= TRUE;

$config['phone_type'] 				= "";

$config['brand_title'] 				= "";

$config['brand_logo'] 				= "";

$config['loader_layer'] 			= TRUE;

$config['ip_sip_server'] 			= "";

// Load from wffdata.json
$file = BASEPATH . "config/wffdata.json";
$fd = fopen ($file, 'r');
$content = filesize($file) ? fread($fd, filesize($file)) : "";
if($content) $config = array_merge($config, json_decode($content, TRUE));
fclose($fd);