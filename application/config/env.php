<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Author: dung.huynh@southtelecom.vn
 *
 * Configuration file for enviroment
 *
 */

/* ENV version 1 variable */
$config['v1'] = array(
	'baseUrl'			   => base_url(),
	'templateApi'		   => base_url('template/view/v1/'),
	'vApi'				   => base_url('api/v1/'),
    'reportApi'            => base_url('api/report/'),
    'restApi'			   => base_url('api/restful/')
);