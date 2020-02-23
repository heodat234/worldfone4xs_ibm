<?php
defined('BASEPATH') || exit('No direct script access allowed');

$config['imap']['encrypto'] = 'imap/ssl';
$config['imap']['validate'] = true;
$config['imap']['host']     = 'imap.googlemail.com';
$config['imap']['port']     = 993;
$config['imap']['username'] = 'tridunghuynhvan@gmail.com';
$config['imap']['password'] = 'evselzdwgbiyxqmw';

$config['imap']['folders'] = [
	'inbox'  => 'INBOX',
	'sent'   => 'Sent',
	'trash'  => 'Trash',
	'spam'   => 'Spam',
	'drafts' => 'Drafts',
];

$config['imap']['expunge_on_disconnect'] = false;

$config['imap']['cache'] = [
	'active'     => false,
	'adapter'    => 'file',
	'backup'     => 'file',
	'key_prefix' => 'imap:',
	'ttl'        => 60,
];