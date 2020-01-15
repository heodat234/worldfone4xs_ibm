<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Proxy extends WFF_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    $fileName = $_POST['fileName'];
		    $contentType = $_POST['contentType'];
		    $base64 = $_POST['base64'];

		    $data = base64_decode($base64);

		    header('Content-Type:' . $contentType);
		    header('Content-Length:' . strlen($data));
		    header('Content-Disposition: attachment; filename=' . str_replace([" "], ["_"], $fileName));

		    echo $data;
		}
	}
}