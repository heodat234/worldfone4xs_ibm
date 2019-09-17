<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Chat_mode {
    function init($mode)
    {
    	require_once("assignmentrules/{$mode}.php");
    	return new $mode();
    }
}