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
// FTP info
$config['ftp_host'] = '';
$config['ftp_username'] = '';
$config['ftp_password'] = '';
// FTP info

// Load from wffdata.json
$file = BASEPATH . "config/wffdata.json";
$fd = fopen ($file, 'r');
$content = filesize($file) ? fread($fd, filesize($file)) : "";
if($content) $config = array_merge($config, json_decode($content, TRUE));
fclose($fd);