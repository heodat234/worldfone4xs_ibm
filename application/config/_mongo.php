<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Author: dung.huynh@southtelecom.vn
 *
 * Configuration file for private mongo
 *
 */

// Configuration for session storage mongo
$config['session_mongo_location'] 			= 'localhost';
$config['session_mongo_port'] 				= '27017';
$config['session_mongo_db'] 				= '_worldfone4xs';
$config['session_mongo_user'] 				= (ENVIRONMENT == "production") ? "_worldfone4x" : "";
$config['session_mongo_password'] 			= (ENVIRONMENT == "production") ? "St3l37779db" : "";
$config['session_mongo_collection'] 		= 'Session';
$config['session_mongo_write_concerns'] 	= (int)1;
$config['session_mongo_write_journal'] 		= true;

// Configuration for private mongo
$config['_mongo_version'] 					= 4.2;
$config['_mongo_location'] 					= $config['session_mongo_location'];
$config['_mongo_port'] 						= $config['session_mongo_port'];
$config['_mongo_db'] 						= $config['session_mongo_db'];
$config['_mongo_user'] 						= $config['session_mongo_user'];
$config['_mongo_password'] 					= $config['session_mongo_password'];
$config['_mongo_write_concerns'] 			= (int)1;
$config['_mongo_write_journal'] 			= true;