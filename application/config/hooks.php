<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

// Compress output
$hook['display_override'][] = array(
	'class' 	=> '',
	'function' 	=> 'compress',
	'filename' 	=> 'Compress.php',
	'filepath' 	=> 'hooks'
);

// Activity log

$hook['post_system'][] = array(
	'class' 	=> 'Activity_log',
	'function' 	=> 'write',
	'filename' 	=> 'Activity_log.php',
	'filepath' 	=> 'hooks'
);