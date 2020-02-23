<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$config['googleplus']['application_name'] = 'South Telecom CRM';
$config['googleplus']['client_id']        = '593404669349-lscs8iuns0pc2kipdbed8m6i5h00sf6o.apps.googleusercontent.com';
$config['googleplus']['client_secret']    = 'VXe-KFH8YVl8ZiwtnofQQY85';
$config['googleplus']['redirect_uri']     = base_url('action/glogin');
$config['googleplus']['api_key']          = '';
$config['googleplus']['scopes']           = array('profile','email');
/*$config['googleplus']['scopes']           = array('profile','email','openid','https://mail.google.com/');*/

