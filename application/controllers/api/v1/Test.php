<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

Class Test extends CI_Controller {


	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->library("chatinternal");
		$server = IoServer::factory(
	        $this->chatinternal,
	        8000
	    );

	    $server->run();
	}

	function update($id)
	{
		
	}
}