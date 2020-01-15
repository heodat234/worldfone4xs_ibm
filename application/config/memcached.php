<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Memcached settings
| -------------------------------------------------------------------------
| Your Memcached servers can be specified below.
|
|	See: https://codeigniter.com/user_guide/libraries/caching.html#memcached
|
*/
if(ENVIRONMENT == "production") {
	$config = array(
		'default' => array(
			'hostname' => '127.0.0.1',
			'port'     => '11211',
			'weight'   => '1',
		),
		'first' => array(
			'hostname' => '192.168.101.11',
			'port'     => '11211',
			'weight'   => '1',
		),
		'second' => array(
			'hostname' => '192.168.101.12',
			'port'     => '11211',
			'weight'   => '1',
		)
	);
} else {
	$config = array(
		'default' => array(
			'hostname' => '127.0.0.1',
			'port'     => '11211',
			'weight'   => '1',
		)
	);
}